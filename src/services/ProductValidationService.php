<?php

require_once __DIR__ . '/../functions/database.php';
require_once __DIR__ . '/../exceptions/AIProviderException.php';
require_once __DIR__ . '/HashService.php';
require_once __DIR__ . '/ImageSignatureService.php';
require_once __DIR__ . '/AIProviderRouter.php';
require_once __DIR__ . '/TextEmbeddingService.php';

class ProductValidationService
{
    /**
     * @param array<int,array>|array<string,mixed> $productData
     * @param array<string,mixed>|null $legacyProductData
     * @return array<string,mixed>
     */
    public static function validate(int $productId, int $producerId, array $productData, ?array $legacyProductData = null): array
    {
        if ($legacyProductData !== null) {
            $legacyProductData['images'] = $productData;
            $productData = $legacyProductData;
        }

        $productData['producer_id'] = $producerId;
        $images = $productData['images'] ?? [];
        if (!is_array($images)) {
            throw new InvalidArgumentException('productData.images debe ser un array.');
        }

        $result = self::baseResult();
        $hashMatches = [];
        $ragRules = [];
        $exampleDecisions = [];

        try {
            $imageHashes = self::hashImages($images);
            $hashMatches = self::findHashMatches($imageHashes, $productId, $producerId);

            if ($hashMatches !== []) {
                $bestMatch = self::bestMatch($hashMatches);
                $detectionMethod = $bestMatch['detection_method'] ?? 'hash_exacto';
                $score = (float) ($bestMatch['score'] ?? 1.0);

                if ($detectionMethod === 'hash_exacto') {
                    $result['decision'] = 'rejected';
                    $plagioPayload = self::plagiarismPayload($bestMatch, $score);
                    $plagioPayload['status'] = 'confirmed';
                    $result['plagio_visual'] = $plagioPayload;
                    $result['motivo_general'] = 'Plagio confirmado: imagen idéntica a producto de otro vendedor.';
                } else {
                    $result['decision'] = 'revision_humana';
                    $result['plagio_visual'] = self::plagiarismPayload($bestMatch, $score);
                    $result['motivo_general'] = 'Posible plagio detectado por similitud perceptual. Revisión manual requerida.';
                }
                self::saveResult($productId, $producerId, $result);
                return $result;
            }

            // Siempre generar embeddings (imagen + texto) antes del threshold
            [$imageEmbeddings, $visualSimilarities] = self::processImageEmbeddings($imageHashes, $producerId, $productId, $result);

            try {
                TextEmbeddingService::embedAndSaveProductData($productId, $productData);
            } catch (Throwable $e) {
                // Non-fatal: el texto embedding se reintentará en la siguiente validación
            }

            $configStmt = Database::getInstance()->ejecutar('ai.fun_val_get_config', [':key' => 'rag.min_examples']);
            $configValue = $configStmt->fetchColumn();
            if ($configValue === false || $configValue === null) {
                throw new RuntimeException('Configuración rag.min_examples no encontrada en ai.config');
            }
            $minExamples = (int) $configValue;
            $stmt = Database::getInstance()->ejecutar('ai.fun_val_check_examples_count');
            $totalExamples = (int) $stmt->fetchColumn();
            if ($totalExamples < $minExamples) {
                $result['decision'] = 'pending_review';
                $result['motivo_general'] = "Validación pendiente: el sistema está en fase de aprendizaje (ejemplos: {$totalExamples}/{$minExamples}).";
                self::saveResult($productId, $producerId, $result);
                return $result;
            }

            if ($visualSimilarities !== []) {
                $bestVisual = self::bestMatch($visualSimilarities);
                $score = (float) ($bestVisual['similarity'] ?? $bestVisual['score'] ?? 0.0);
                if ($score > 0.0) {
                    $result['plagio_visual'] = self::plagiarismPayload($bestVisual, $score);
                }
            }

            [$coherence, $ragRules, $exampleDecisions, $ragContextText] = self::buildRagContext($productId, $productData, $imageEmbeddings, $result);
            $result['coherencia_texto_imagen'] = $coherence;

            $needsDecision = self::shouldCallDecisionModel(
                $result['plagio_visual'], $result['coherencia_texto_imagen'], $result['artesanalidad'],
                $ragRules, $exampleDecisions
            );
            if ($needsDecision) {
                $evidence = self::buildEvidence($productData, $hashMatches, $visualSimilarities,
                    $result['coherencia_texto_imagen'], $ragRules, $result['artesanalidad'],
                    $exampleDecisions);
                self::applyDecisionModel($result, $evidence);
            } else {
                $result['motivo_general'] = 'Producto aprobado automáticamente: sin plagio visual y con coherencia texto-imagen alta o no requerida.';
            }

            self::saveResult($productId, $producerId, $result);
            return $result;
        } catch (Throwable $exception) {
            if ($exception instanceof AIProviderException) {
                return self::pendingResult($productId, $producerId, $result, 'Validación pendiente: ambos proveedores de IA fallaron');
            }

            $result['decision'] = 'pending_validacion_ia';
            $result['fallback_used'] = true;
            $result['motivo_general'] = 'Error de infraestructura durante validación IA: ' . $exception->getMessage();

            try {
                self::saveResult($productId, $producerId, $result);
            } catch (Throwable $saveException) {
                error_log('[ProductValidationService] No se pudo guardar validación pendiente: ' . $saveException->getMessage());
            }

            return $result;
        }
    }

    private static function baseResult(): array
    {
        return [
            'decision' => 'approved',
            'plagio_visual' => [
                'status' => 'none',
                'detection_method' => null,
                'score' => 0.0,
                'matched_product_id' => null,
                'matched_producer_id' => null,
                'matched_image_id' => null,
                'matched_image_url' => null,
            ],
            'coherencia_texto_imagen' => ['status' => 'no_evaluada', 'score' => 0.0, 'reason' => 'Aún no evaluada'],
            'artesanalidad' => ['status' => 'no_evaluada', 'score' => 0.0, 'reason' => 'Aún no evaluada'],
            'rag_evidence' => 0,
            'provider_used' => null,
            'fallback_used' => false,
            'models' => ['embedding_model' => null, 'decision_model' => null],
            'motivo_general' => '',
        ];
    }

    private static function hashImages(array $images): array
    {
        $imageHashes = [];
        foreach ($images as $image) {
            if (!is_array($image)) {
                continue;
            }
            $path = (string) ($image['path'] ?? $image['image_path'] ?? '');
            if ($path === '') {
                continue;
            }
            $hashes = HashService::hashFile($path);
            $imageHashes[] = array_merge($image, $hashes, [
                'id_imagen' => (int) ($image['id_imagen'] ?? $image['image_id'] ?? 0),
                'path' => $path,
            ]);
        }

        return $imageHashes;
    }

    private static function findHashMatches(array $imageHashes, int $productId, int $producerId): array
    {
        $matches = [];
        foreach ($imageHashes as $hashData) {
            $unified = ImageSignatureService::findHashMatchesUnified(
                (string) ($hashData['file_hash'] ?? ''),
                $hashData['phash'] ?? null,
                $hashData['dhash'] ?? null,
                (int) ($hashData['id_producto'] ?? $productId),
                (int) ($hashData['id_imagen'] ?? $hashData['image_id'] ?? 0),
                20
            );

            foreach ($unified as $match) {
                self::appendExternalMatch(
                    $matches,
                    $match,
                    $producerId,
                    $match['detection_method'] ?? 'hash_exacto',
                    (float) ($match['score'] ?? 0.0)
                );
            }
        }

        return $matches;
    }

    private static function processImageEmbeddings(array $imageHashes, int $producerId, int $productId, array &$result): array
    {
        $imageEmbeddings = [];
        $visualSimilarities = [];

        foreach ($imageHashes as $hashData) {
            self::saveImageSignature($productId, $hashData);
            try {
                // Usar URL pública para el embedding (el proveedor IA necesita acceso HTTP)
                $imageUrl = self::imageUrl($hashData);
                if (!preg_match('/^https?:\/\//i', $imageUrl)) {
                    // Si no hay URL pública, construir desde path local
                    $localPath = (string) ($hashData['path'] ?? $hashData['image_path'] ?? '');
                    if ($localPath !== '' && !str_starts_with($localPath, '/')) {
                        $imageUrl = 'http://135.119.114.214/viva/' . ltrim($localPath, '/');
                    }
                }
                $embedding = AIProviderRouter::generateImageEmbedding($imageUrl);
                $imageEmbeddings[] = $embedding['embedding'];
                $imageId = (int) ($hashData['id_imagen'] ?? $hashData['image_id'] ?? 0);
                if ($imageId > 0) {
                    ImageSignatureService::saveVisualEmbedding(
                        $productId,
                        $imageId,
                        $embedding['embedding'],
                        (string) ($embedding['model'] ?? '')
                    );
                }
                $similar = ImageSignatureService::findSimilarByVectorExcludingProducer($embedding['embedding'], $producerId, 0.90, 10);
                foreach ($similar as $match) {
                    $match['detection_method'] = 'embedding_visual';
                    $visualSimilarities[] = $match;
                }
                $result['models']['embedding_model'] = $embedding['model'] ?? null;
                $result['provider_used'] = $embedding['provider'] ?? $result['provider_used'];
                $result['fallback_used'] = self::providerIsFallback((string) ($embedding['provider'] ?? ''));
            } catch (AIProviderException $exception) {
                $result['provider_used'] = null;
                $result['fallback_used'] = true;
            }
        }

        return [$imageEmbeddings, $visualSimilarities];
    }

    private static function buildRagContext(int $productId, array $productData, array $imageEmbeddings, array &$result): array
    {
        $ragRules = [];
        $exampleDecisions = [];
        $textEmbedding = null;

        try {
            $textData = TextEmbeddingService::embedAndSaveProductData($productId, $productData);
            $textEmbedding = $textData['embedding'] ?? null;
            if (!empty($textData['model'])) {
                $result['models']['embedding_model'] = $textData['model'];
            }
        } catch (AIProviderException $exception) {
            if ($imageEmbeddings === []) {
                throw $exception;
            }
            $result['fallback_used'] = true;
        }

        // Usar solo el título para coherencia texto-imagen (más preciso que el texto combinado)
        $title = trim((string) ($productData['title'] ?? ''));
        if ($title !== '') {
            try {
                $titleCoherence = TextEmbeddingService::computeTextImageCoherence($title, $imageEmbeddings);
            } catch (Throwable $e) {
                $titleCoherence = ['status' => 'no_evaluada', 'score' => 0.0, 'reason' => 'Error al evaluar coherencia con título.'];
            }
            $coherence = $titleCoherence;
        } else {
            $coherence = TextEmbeddingService::computeTextImageCoherenceEmbedding($textEmbedding, $imageEmbeddings);
        }

        try {
            $ragRules = TextEmbeddingService::getRagRulesByTypes(['artisan_policy', 'plagiarism_policy']);
        } catch (Throwable $exception) {
            $ragRules = array_merge(
                TextEmbeddingService::getRagRulesByType('artisan_policy'),
                TextEmbeddingService::getRagRulesByType('plagiarism_policy')
            );
        }
        $result['rag_evidence'] = count($ragRules);

        // Buscar productos similares por embedding (solo título) y traer sus decisiones previas
        $ragContextText = trim((string) ($productData['title'] ?? ''));
        if ($ragContextText !== '' && $textEmbedding !== null) {
            try {
                $similarProducts = TextEmbeddingService::searchSimilarTextExcludingProducer(
                    $ragContextText,
                    (int) ($productData['producer_id'] ?? 0),
                    5
                );
                foreach ($similarProducts as $similar) {
                    $similarId = $similar['product_id'] ?? null;
                    if ($similarId === null) {
                        continue;
                    }
                    $decision = self::getLatestDecision((int) $similarId);
                    if ($decision !== null) {
                        $exampleDecisions[] = $decision;
                    }
                }
            } catch (Throwable $exception) {
                $result['fallback_used'] = true;
            }
        }

        return [$coherence, $ragRules, $exampleDecisions, $ragContextText];
    }

    private static function getLatestDecision(int $productId): ?array
    {
        try {
            $stmt = Database::getInstance()->ejecutar('ai.fun_val_latest_validation_result', [
                ':product_id' => $productId,
            ]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (Throwable) {
            return null;
        }
    }

    private static function appendExternalMatch(array &$matches, array $match, int $producerId, string $method, float $score): void
    {
        $matchedProducer = self::producerId($match);
        if ($matchedProducer !== null && $matchedProducer === $producerId) {
            return;
        }
        $match['detection_method'] = $method;
        $match['score'] = $score;
        $matches[] = $match;
    }

    private static function saveImageSignature(int $productId, array &$hashData): void
    {
        try {
            $imageId = (int) ($hashData['id_imagen'] ?? $hashData['image_id'] ?? 0);
            if ($imageId <= 0) {
                $imageId = self::resolveImageId($productId, (string) ($hashData['path'] ?? $hashData['url_imagen'] ?? ''));
                $hashData['id_imagen'] = $imageId;
            }
            if ($imageId <= 0) {
                error_log('[ProductValidationService] No se pudieron guardar hashes: falta id_imagen.');
                return;
            }
            ImageSignatureService::saveImageHashes(
                $productId,
                $imageId,
                (string) $hashData['file_hash'],
                (string) $hashData['phash'],
                (string) $hashData['dhash']
            );
        } catch (Throwable $exception) {
            error_log('[ProductValidationService] No se pudieron guardar hashes de imagen: ' . $exception->getMessage());
        }
    }

    private static function resolveImageId(int $productId, string $path): int
    {
        if ($path === '') {
            return 0;
        }

        $stmt = Database::getInstance()->ejecutar('obtenerImagenPorProductoUrl', [
            ':id_producto' => $productId,
            ':url_imagen' => $path,
        ]);

        return (int) ($stmt->fetchColumn() ?: 0);
    }

    private static function imageUrl(array $image): string
    {
        return (string) ($image['url'] ?? $image['image_url'] ?? $image['path'] ?? $image['image_path'] ?? '');
    }

    private static function bestMatch(array $matches): array
    {
        usort($matches, static fn (array $a, array $b): int => (float) ($b['similarity'] ?? $b['score'] ?? 0.0) <=> (float) ($a['similarity'] ?? $a['score'] ?? 0.0));
        return $matches[0] ?? [];
    }

    private static function plagiarismPayload(array $match, float $score): array
    {
        return [
            'status' => 'posible',
            'detection_method' => $match['detection_method'] ?? 'embedding_visual',
            'score' => $score,
            'matched_product_id' => $match['product_id'] ?? $match['id_producto'] ?? null,
            'matched_producer_id' => self::producerId($match),
            'matched_image_id' => $match['image_id'] ?? $match['id_imagen'] ?? null,
            'matched_image_url' => $match['matched_image_url'] ?? $match['image_url'] ?? $match['url_imagen'] ?? $match['image_path'] ?? null,
        ];
    }

    private static function producerId(array $row): ?int
    {
        if (isset($row['producer_id'])) {
            return (int) $row['producer_id'];
        }
        if (isset($row['id_productor'])) {
            return (int) $row['id_productor'];
        }
        return null;
    }

    private static function shouldCallDecisionModel(array $plagiarism, array $coherence, array $artisan, array $ragRules = [], array $exampleDecisions = []): bool
    {
        // Siempre llamar al modelo si hay contexto RAG disponible
        if ($ragRules !== [] || $exampleDecisions !== []) {
            return true;
        }

        // Sin contexto RAG: llamar solo si hay señales que evaluar
        return (float) ($plagiarism['score'] ?? 0.0) > 0.0
            || in_array((string) ($coherence['status'] ?? ''), ['media', 'baja'], true)
            || (string) ($artisan['status'] ?? '') === 'dudosa'
            || (
                (string) ($coherence['status'] ?? '') === 'no_evaluada'
                && (string) ($artisan['status'] ?? '') === 'no_evaluada'
            );
    }

    private static function buildEvidence(array $productData, array $hashResults, array $visualResults, array $coherence, array $ragRules, array $artisanNotes, array $exampleDecisions = []): array
    {
        return [
            'product' => [
                'title' => $productData['title'] ?? '',
                'description' => $productData['description'] ?? '',
                'category' => $productData['category'] ?? '',
                'materials' => $productData['materials'] ?? '',
            ],
            'hash_results' => array_slice($hashResults, 0, 5),
            'visual_similarity' => array_slice($visualResults, 0, 5),
            'text_image_coherence' => $coherence,
            'rag_rules' => array_slice($ragRules, 0, 10),
            'similar_product_decisions' => array_slice($exampleDecisions, 0, 5),
            'artisan_assessment' => $artisanNotes,
        ];
    }

    private static function applyDecisionModel(array &$result, array $evidence): void
    {
        $systemPrompt = "Eres un validador de productos artesanales para un e-commerce que acepta tres perfiles:\n" .
            "1. Artesanía indígena tradicional (mochilas, hamacas, cerámica, talla, etc.)\n" .
            "2. Artesanía popular no indígena (marroquinería, alimentos artesanales, etc.)\n" .
            "3. Piezas vintage, antigüedades y objetos de mercadillo con valor cultural (como una olla de bronce precolombina)\n\n" .
            "Tenés acceso a REGLAS de artesanalidad y EJEMPLOS de productos previamente aprobados y rechazados como contexto.\n" .
            "Usá esa información para decidir consistentemente.\n\n" .
            "Criterios:\n" .
            "- approved: El producto es artesanal (hecho a mano, técnica tradicional), o es una pieza vintage/precolombina con valor cultural.\n" .
            "- rejected: El producto es producción industrial, ropa de fábrica, plástico en serie, o claramente no artesanal.\n" .
            "- revision_humana: Hay dudas, evidencia conflictiva, o el producto es industrial pero podría tener valor cultural.\n\n" .
            "Responde ÚNICAMENTE con un objeto JSON (sin texto adicional, sin bloques markdown), máximo 3 oraciones en motivo_general:\n" .
            "{\n" .
            "  \"decision\": \"approved|rejected|revision_humana\",\n" .
            "  \"artesanalidad\": {\"status\": \"artesanal|dudosa|no_artesanal\", \"score\": 0.0-1.0, \"reason\": \"Breve: qué ves en la imagen vs el texto.\"},\n" .
            "  \"motivo_general\": \"Máximo 3 oraciones: qué dice el texto, qué muestra la imagen, qué regla RAG aplicaste y por qué.\"\n" .
            "}";
        $userPrompt = json_encode($evidence, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n\nDecide: approved, rejected, o revision_humana. Responde SOLO con JSON.";

        try {
            $decision = AIProviderRouter::callDecisionModel($systemPrompt, $userPrompt);
            $parsed = self::parseDecisionContent((string) $decision['content'], $decision['parsed'] ?? null);
            $allowed = ['approved', 'rejected', 'revision_humana', 'pending_validacion_ia', 'pending_review'];
            $result['decision'] = in_array(($parsed['decision'] ?? ''), $allowed, true) ? $parsed['decision'] : 'revision_humana';
            $result['artesanalidad'] = is_array($parsed['artesanalidad'] ?? null) ? $parsed['artesanalidad'] : $result['artesanalidad'];
            $result['motivo_general'] = (string) ($parsed['motivo_general'] ?? $parsed['reason'] ?? 'Decisión generada por IA.');
            $result['models']['decision_model'] = $decision['model'] ?? null;
            $result['provider_used'] = $decision['provider'] ?? $result['provider_used'];
            $result['fallback_used'] = self::providerIsFallback((string) ($decision['provider'] ?? ''));
        } catch (AIProviderException $exception) {
            $result['decision'] = 'pending_validacion_ia';
            $result['fallback_used'] = true;
            $result['motivo_general'] = 'Validación pendiente: ambos proveedores de IA fallaron';
        }
    }

    private static function parseDecisionContent(string $content, mixed $preferred = null): array
    {
        if (is_array($preferred) && $preferred !== []) {
            return $preferred;
        }

        $decoded = json_decode(trim($content), true);
        if (is_array($decoded)) {
            return $decoded;
        }
        if (preg_match('/```(?:json)?\s*([\s\S]*?)```/i', $content, $matches) === 1) {
            $decoded = json_decode(trim($matches[1]), true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }
        if (preg_match('/\{[\s\S]*\}/', $content, $matches) === 1) {
            $decoded = json_decode($matches[0], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return ['decision' => 'revision_humana', 'motivo_general' => 'La IA no devolvió JSON válido.'];
    }

    private static function saveResult(int $productId, int $producerId, array $result): int
    {
        $plagiarism = $result['plagio_visual'] ?? [];
        $coherence = $result['coherencia_texto_imagen'] ?? [];
        $artisan = $result['artesanalidad'] ?? [];
        $stmt = Database::getInstance()->ejecutar('ai.fun_c_validation_result', [
            ':product_id' => $productId,
            ':producer_id' => $producerId,
            ':decision' => $result['decision'],
            ':plagiarism_status' => $plagiarism['status'] ?? 'none',
            ':plagiarism_score' => $plagiarism['score'] ?? 0.0,
            ':plagiarism_method' => $plagiarism['detection_method'] ?? 'N/A',
            ':matched_product_id' => (string) ($plagiarism['matched_product_id'] ?? 'N/A'),
            ':matched_producer_id' => (string) ($plagiarism['matched_producer_id'] ?? 'N/A'),
            ':matched_image_id' => (string) ($plagiarism['matched_image_id'] ?? 'N/A'),
            ':matched_image_url' => (string) ($plagiarism['matched_image_url'] ?? 'N/A'),
            ':text_image_status' => $coherence['status'] ?? 'no_evaluada',
            ':text_image_score' => $coherence['score'] ?? 0.0,
            ':artisan_status' => $artisan['status'] ?? 'no_evaluada',
            ':artisan_score' => $artisan['score'] ?? 0.0,
            ':provider_used' => $result['provider_used'] ?? '',
            ':decision_model' => $result['models']['decision_model'] ?? '',
            ':fallback_used' => (bool) ($result['fallback_used'] ?? false),
            ':reason' => $result['motivo_general'] ?? '',
        ]);

        return (int) $stmt->fetchColumn();
    }

    private static function pendingResult(int $productId, int $producerId, array $result, string $reason): array
    {
        $result['decision'] = 'pending_validacion_ia';
        $result['fallback_used'] = true;
        $result['motivo_general'] = $reason;
        self::saveResult($productId, $producerId, $result);
        return $result;
    }

    private static function providerIsFallback(string $provider): bool
    {
        $primary = strtolower((string) ($_ENV['AI_PRIMARY_PROVIDER'] ?? 'openrouter'));
        return $provider !== '' && strtolower($provider) !== $primary;
    }
}
