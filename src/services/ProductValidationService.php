<?php

require_once __DIR__ . '/../functions/database.php';
require_once __DIR__ . '/../exceptions/AIProviderException.php';
require_once __DIR__ . '/HashService.php';
require_once __DIR__ . '/ImageSignatureService.php';
require_once __DIR__ . '/AIProviderRouter.php';
require_once __DIR__ . '/TextEmbeddingService.php';

class ProductValidationService
{
    // ═══════════════════════════════════════════════════════════════
    //  CONSTANTES
    // ═══════════════════════════════════════════════════════════════

    private const VISUAL_DESC_MODEL = 'nvidia/nemotron-3-nano-omni-30b-a3b-reasoning';
    private const VISUAL_DESC_PROMPT = "Eres un experto en artesanías colombianas y clasificación visual de productos para e-commerce.

Analiza la imagen del producto y genera una descripción semántica útil para búsqueda por embeddings.

Instrucciones:
1. Identifica el objeto principal.
2. Si el producto parece una artesanía conocida, menciona su nombre común y nombre cultural.
3. No te quedes solo en colores o formas; describe tipo de producto, material probable, técnica artesanal, patrón, uso y categoría.
4. Si el objeto se parece a un sombrero vueltiao, mochila wayuu, ruana, canasto, cerámica, talla en madera u otra artesanía tradicional, indícalo explícitamente con nivel de confianza.
5. Si no estás seguro, usa frases como \"posible\", \"parece\", \"similar a\", pero no inventes.
6. Responde en español.
7. Devuelve una descripción optimizada para embeddings, no una descripción para humanos.

Formato JSON:
{\"categoria_visual\": \"...\", \"producto_probable\": \"...\", \"descripcion_semantica\": \"...\", \"etiquetas\": [], \"confianza\": \"baja|media|alta\"}

SOLO responde con el JSON, sin texto adicional, sin markdown.";

    // ═══════════════════════════════════════════════════════════════
    //  MAIN: Punto de entrada de la validación
    //  Flujo: hashes → descripción visual → embeddings → RAG → LLM
    // ═══════════════════════════════════════════════════════════════

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

            self::saveImageHashes($imageHashes, $productId);

            [$visualDescription, $visualDescriptionEmbedding] = self::generateAndSaveVisualDescription($productId, $imageHashes);

            $textData = null;
            try {
                $textData = TextEmbeddingService::embedAndSaveProductData($productId, $productData);
                if (!empty($textData['model'])) {
                    $result['models']['embedding_model'] = $textData['model'];
                }
            } catch (Throwable $e) {
                if ($e instanceof AIProviderException) {
                    $result['fallback_used'] = true;
                }
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

            $textEmbedding = is_array($textData) && is_array($textData['embedding'] ?? null) ? $textData['embedding'] : null;
            [$coherence, $ragRules, $exampleDecisions, $ragContextText, $visualDescMatches] = self::buildRagContext(
                $productId,
                $productData,
                $result,
                $textEmbedding,
                $visualDescriptionEmbedding
            );
            $result['coherencia_texto_imagen'] = $coherence;

            // ── Pre-LLM Rules: decisiones sin llamar al modelo ──
            $preDecision = self::applyPreLlmRules($result);
            if ($preDecision !== null) {
                self::saveResult($productId, $producerId, $result);
                return $result;
            }

            // ── LLM Decision ──
            $evidence = self::buildEvidence($productData, $hashMatches,
                $result['coherencia_texto_imagen'], $ragRules, $result['artesanalidad'],
                $exampleDecisions, $visualDescription, $visualDescMatches);
            self::applyDecisionModel($result, $evidence);

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

    // ═══════════════════════════════════════════════════════════════
    //  RESULTADO BASE: estructura por defecto del resultado
    // ═══════════════════════════════════════════════════════════════

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

    // ═══════════════════════════════════════════════════════════════
    //  HASHING: detección de plagio por hash exacto y perceptual
    // ═══════════════════════════════════════════════════════════════

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

    private static function saveImageHashes(array $imageHashes, int $productId): void
    {
        foreach ($imageHashes as $hashData) {
            self::saveImageSignature($productId, $hashData);
        }
    }

    // ═══════════════════════════════════════════════════════════════
    //  DESCRIPCIÓN VISUAL: llama a NVIDIA Nemo 30B para describir
    //  la imagen y genera embedding vectorial de esa descripción
    // ═══════════════════════════════════════════════════════════════

    /**
     * @param array<int,array> $imageHashes
     * @return array{0:?string,1:?array<int,float>}
     */
    private static function generateAndSaveVisualDescription(int $productId, array $imageHashes): array
    {
        if ($imageHashes === []) {
            return [null, null];
        }

        try {
            $firstImage = reset($imageHashes);
            if (!is_array($firstImage)) {
                return [null, null];
            }

            $imageUrl = (string) ($firstImage['url'] ?? $firstImage['image_url'] ?? $firstImage['path'] ?? $firstImage['image_path'] ?? '');
            if (!preg_match('/^data:image/i', $imageUrl) && !preg_match('/^https?:\/\//i', $imageUrl)) {
                $localPath = (string) ($firstImage['path'] ?? '');
                if ($localPath !== '') {
                    $imageUrl = 'http://135.119.114.214/viva/' . ltrim($localPath, '/');
                }
            }

            $descResult = AIProviderRouter::callChat([
                ['role' => 'user', 'content' => [
                    ['type' => 'image_url', 'image_url' => ['url' => $imageUrl]],
                    ['type' => 'text', 'text' => self::VISUAL_DESC_PROMPT],
                ]],
            ], self::VISUAL_DESC_MODEL, ['nvidia'], [
                'chat_template_kwargs' => ['enable_thinking' => false],
            ]);

            $descText = (string) ($descResult['content'] ?? '');
            if ($descText === '' || preg_match('/\{.*\}/s', $descText, $matches) !== 1) {
                return [null, null];
            }

            $descJson = json_decode($matches[0], true);
            if (!is_array($descJson) || empty($descJson['descripcion_semantica'])) {
                return [null, null];
            }

            $semanticDesc = trim((string) $descJson['descripcion_semantica']);
            if ($semanticDesc === '') {
                return [null, null];
            }

            $embedResult = AIProviderRouter::generateTextEmbedding($semanticDesc);
            $visualDescriptionEmbedding = $embedResult['embedding'] ?? null;
            if (!is_array($visualDescriptionEmbedding) || $visualDescriptionEmbedding === []) {
                return [null, null];
            }
            $visualDescriptionEmbedding = self::normalizeEmbedding($visualDescriptionEmbedding);

            $imageId = (int) ($firstImage['id_imagen'] ?? $firstImage['image_id'] ?? 0);
            if ($imageId <= 0) {
                $imageId = self::resolveImageId($productId, (string) ($firstImage['path'] ?? $firstImage['url_imagen'] ?? ''));
            }
            if ($imageId <= 0) {
                error_log('[ProductValidation] No se pudo guardar descripción visual: falta id_imagen.');
                return [$semanticDesc, $visualDescriptionEmbedding];
            }

            self::saveVisualDescription($productId, $imageId, $semanticDesc, $visualDescriptionEmbedding, self::VISUAL_DESC_MODEL);
            return [$semanticDesc, $visualDescriptionEmbedding];
        } catch (Throwable $e) {
            error_log('[ProductValidation] Error generando descripción visual: ' . $e->getMessage());
            return [null, null];
        }
    }

    /**
     * @param array<int,float> $embedding
     */
    private static function saveVisualDescription(int $productId, int $imageId, string $description, array $embedding, string $model): void
    {
        ImageSignatureService::saveVisualEmbedding($productId, $imageId, $embedding, $model, $description);
    }

    // ═══════════════════════════════════════════════════════════════
    //  RAG CONTEXT: construye el contexto para el modelo de decisión
    //   - Coherencia texto-imagen (cosine similarity)
    //   - Reglas RAG (artisan_policy, plagiarism_policy)
    //   - Decisiones previas de productos similares
    //   - Matches por descripción visual
    // ═══════════════════════════════════════════════════════════════

    private static function buildRagContext(
        int $productId,
        array $productData,
        array &$result,
        ?array $textEmbedding = null,
        ?array $visualDescriptionEmbedding = null
    ): array
    {
        $ragRules = [];
        $exampleDecisions = [];
        $visualDescMatches = [];

        if ($textEmbedding !== null && is_array($visualDescriptionEmbedding) && $visualDescriptionEmbedding !== []) {
            $coherence = TextEmbeddingService::computeTextImageCoherenceEmbedding($textEmbedding, [$visualDescriptionEmbedding]);
        } else {
            $coherence = ['status' => 'no_evaluada', 'score' => 0.0, 'reason' => 'Sin descripción visual para evaluar'];
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

        // Buscar productos similares por embedding y traer sus decisiones previas
        $ragContextText = trim((string) (($productData['title'] ?? '') . ' ' . ($productData['description'] ?? '')));
        if ($ragContextText !== '' && $textEmbedding !== null) {
            try {
                $similarProducts = TextEmbeddingService::searchSimilarTextByEmbeddingExcludingProducer(
                    $textEmbedding,
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

        if (is_array($visualDescriptionEmbedding) && $visualDescriptionEmbedding !== []) {
            try {
                $stmt = Database::getInstance()->ejecutar('ai.fun_val_search_by_visual_desc', [
                    ':vec' => self::vectorLiteral($visualDescriptionEmbedding),
                    ':pid' => $productId,
                    ':limit' => 5,
                ]);
                $visualDescMatches = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            } catch (Throwable $e) {
                error_log('[ProductValidation] Error en búsqueda por descripción visual: ' . $e->getMessage());
            }
        }

        return [$coherence, $ragRules, $exampleDecisions, $ragContextText, $visualDescMatches];
    }

    // ─────────────────────────────────────────────────────────────
    //  Helpers de RAG
    // ─────────────────────────────────────────────────────────────

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

    // ═══════════════════════════════════════════════════════════════
    //  HELPERS: firmas de imágenes, resolución de IDs, utilidades
    // ═══════════════════════════════════════════════════════════════

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

    private static function bestMatch(array $matches): array
    {
        usort($matches, static fn (array $a, array $b): int => (float) ($b['similarity'] ?? $b['score'] ?? 0.0) <=> (float) ($a['similarity'] ?? $a['score'] ?? 0.0));
        return $matches[0] ?? [];
    }

    private static function plagiarismPayload(array $match, float $score): array
    {
        return [
            'status' => 'posible',
            'detection_method' => $match['detection_method'] ?? 'hash_exacto',
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

    // ═══════════════════════════════════════════════════════════════
    //  PRE-LLM: decide si alcanza con reglas sin llamar al modelo
    // ═══════════════════════════════════════════════════════════════

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

    /**
     * Evalúa reglas de decisión pre-LLM. Si alguna regla decide, setea $result y retorna true.
     * Si ninguna regla aplica, retorna false y se procede a llamar al LLM.
     */
    private static function applyPreLlmRules(array &$result): ?string
    {
        return null;
    }

    // ═══════════════════════════════════════════════════════════════
    //  LLM DECISION: construye evidencia y llama al modelo de
    //  decisión con todo el contexto RAG
    // ═══════════════════════════════════════════════════════════════

    private static function buildEvidence(array $productData, array $hashResults, array $coherence, array $ragRules, array $artisanNotes, array $exampleDecisions = [], $visualDescription = null, array $visualDescMatches = []): array
    {
        return [
            'product' => [
                'title' => $productData['title'] ?? '',
                'description' => $productData['description'] ?? '',
                'category' => $productData['category'] ?? '',
                'materials' => $productData['materials'] ?? '',
            ],
            'hash_results' => array_slice($hashResults, 0, 5),
            'text_image_coherence' => $coherence,
            'rag_rules' => array_slice($ragRules, 0, 10),
            'similar_product_decisions' => array_slice($exampleDecisions, 0, 5),
            'artisan_assessment' => $artisanNotes,
            'visual_description' => $visualDescription,
            'visual_desc_matches' => array_slice($visualDescMatches, 0, 5),
        ];
    }

    private static function applyDecisionModel(array &$result, array $evidence): void
    {
        $systemPrompt = "Eres un validador de productos artesanales para un e-commerce que acepta tres perfiles:\n" .
            "1. Artesanía indígena tradicional (mochilas, hamacas, cerámica, talla, etc.)\n" .
            "2. Artesanía popular no indígena (marroquinería, alimentos artesanales, etc.)\n" .
            "3. Piezas vintage, antigüedades y objetos de mercadillo con valor cultural (como una olla de bronce precolombina)\n\n" .
            "Tenés acceso a REGLAS de artesanalidad, EJEMPLOS de productos previamente aprobados y rechazados, y una DESCRIPCIÓN VISUAL semántica generada por un modelo multimodal experto.\n" .
            "La decisión final es text-only: evaluá esa descripción visual junto con el texto del producto y el contexto RAG, sin pedir ni procesar imágenes.\n" .
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

    // ═══════════════════════════════════════════════════════════════
    //  PERSISTENCIA: guarda resultado + actualiza estado del producto
    // ═══════════════════════════════════════════════════════════════

    private static function saveResult(int $productId, int $producerId, array $result): int
    {
        $decision = $result['decision'] ?? 'pending_validacion_ia';
        $newStatus = match ($decision) {
            'approved' => 'approved',
            'revision_humana' => 'pending_review',
            'rejected' => 'rejected',
            default => 'pending_review',
        };

        $db = Database::getInstance();
        $conn = $db->connection;
        $conn->beginTransaction();

        try {
            $plagiarism = $result['plagio_visual'] ?? [];
            $coherence = $result['coherencia_texto_imagen'] ?? [];
            $artisan = $result['artesanalidad'] ?? [];
            $stmt = $db->ejecutar('ai.fun_c_validation_result', [
                ':product_id' => $productId,
                ':producer_id' => $producerId,
                ':decision' => $decision,
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
            $resultId = (int) $stmt->fetchColumn();

            $isActive = in_array($decision, ['approved'], true) ? 'true' : 'false';
            $db->ejecutar('actualizarValidacionStatus', [
                ':id_producto' => $productId,
                ':validation_status' => $newStatus,
                ':is_active' => $isActive,
            ]);

            $conn->commit();
            return $resultId;
        } catch (Throwable $e) {
            $conn->rollBack();
            error_log('[ProductValidationService] Error transaccional en saveResult: ' . $e->getMessage());
            throw $e;
        }
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

    // ═══════════════════════════════════════════════════════════════
    //  UTILIDADES: formateo de vectores para pgvector
    // ═══════════════════════════════════════════════════════════════

    private static function vectorLiteral(array $embedding): string
    {
        if ($embedding === []) {
            throw new InvalidArgumentException('El embedding no puede estar vacío.');
        }

        $values = [];
        foreach ($embedding as $value) {
            if (!is_int($value) && !is_float($value) && !is_numeric($value)) {
                throw new InvalidArgumentException('El embedding contiene valores no numéricos.');
            }
            $values[] = (string) (float) $value;
        }

        return '[' . implode(',', $values) . ']';
    }

    private static function normalizeEmbedding(array $embedding, int $targetDim = 2048): array
    {
        $values = [];
        foreach ($embedding as $value) {
            if (!is_int($value) && !is_float($value) && !is_numeric($value)) {
                throw new InvalidArgumentException('El embedding contiene valores no numéricos.');
            }
            $values[] = (float) $value;
        }

        if (count($values) > $targetDim) {
            return array_slice($values, 0, $targetDim);
        }

        while (count($values) < $targetDim) {
            $values[] = 0.0;
        }

        return $values;
    }
}
