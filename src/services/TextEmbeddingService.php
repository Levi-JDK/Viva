<?php

require_once __DIR__ . '/../functions/database.php';
require_once __DIR__ . '/AIProviderRouter.php';

class TextEmbeddingService
{
    private const VECTOR_DIMENSION = 2048;
    private const SIMILARITY_THRESHOLD = 0.75;

    /**
     * @param array<string,mixed> $data
     * @return array{id:int, embedding:array<int,float>, provider:string, model:string}
     * @throws AIProviderException
     * @throws Exception
     */
    public static function embedAndSaveProductData(int $productId, array $data): array
    {
        $producerId = isset($data['producer_id']) ? (int) $data['producer_id'] : throw new InvalidArgumentException('Falta producer_id');

        $parts = [];
        if (!empty($data['title'])) {
            $parts[] = trim((string) $data['title']);
        }
        if (!empty($data['description'])) {
            $parts[] = trim((string) $data['description']);
        }

        $content = implode(". ", $parts);
        if ($content === '') {
            throw new InvalidArgumentException('No hay datos de producto para embeber.');
        }

        return self::embedAndPersist($content, $productId, $producerId);
    }

    /**
     * @return array<int,array>
     * @throws AIProviderException
     * @throws Exception
     */
    public static function searchSimilarText(string $text, int $limit = 5): array
    {
        return self::search($text, null, $limit);
    }

    /**
     * @return array<int,array>
     * @throws AIProviderException
     * @throws Exception
     */
    public static function searchSimilarTextExcludingProducer(string $text, int $producerId, int $limit = 5): array
    {
        return self::search($text, $producerId, $limit);
    }

    /**
     * @param array<int,float> $embedding
     * @return array<int,array>
     * @throws Exception
     */
    public static function searchSimilarTextByEmbeddingExcludingProducer(array $embedding, int $producerId, int $limit = 5): array
    {
        self::assertPgvectorAvailable();
        $stmt = Database::getInstance()->ejecutar('ai.fun_val_search_similar_text_exclude', [
            ':embedding' => self::vectorLiteral(self::normalizeEmbedding($embedding)),
            ':producer_id' => $producerId,
            ':threshold' => self::SIMILARITY_THRESHOLD,
            ':limit' => max(1, $limit),
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * @throws AIProviderException
     * @throws Exception
     */
    public static function saveRagRule(int $id, string $type, string $content): void
    {
        Database::getInstance()->ejecutar('ai.fun_c_rag_rule', [
            ':id' => $id,
            ':type' => $type,
            ':content' => $content,
        ]);
    }

    /**
     * @return array<int,array>
     * @throws Exception
     */
    public static function getRagRulesByType(string $type): array
    {
        $stmt = Database::getInstance()->ejecutar('ai.fun_get_rag_rules', [
            ':p_types' => '{' . $type . '}',
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * @param array<int,string> $types
     * @return array<int,array>
     * @throws Exception
     */
    public static function getRagRulesByTypes(array $types): array
    {
        if ($types === []) {
            return [];
        }

        $stmt = Database::getInstance()->ejecutar('ai.fun_get_rag_rules', [
            ':p_types' => '{' . implode(',', $types) . '}',
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * @return array{status:string, score:float, reason:string}
     * @throws AIProviderException
     */
    public static function computeTextImageCoherence(string $description, array $imageEmbeddings): array
    {
        if ($imageEmbeddings === []) {
            return ['status' => 'no_evaluada', 'score' => 0.0, 'reason' => 'Sin embeddings de imagen'];
        }

        if (trim($description) === '') {
            return ['status' => 'no_evaluada', 'score' => 0.0, 'reason' => 'Sin descripción para evaluar'];
        }

        $embedding = AIProviderRouter::generateTextEmbedding($description);
        return self::computeTextImageCoherenceEmbedding($embedding['embedding'], $imageEmbeddings);
    }

    /**
     * @return array{status:string, score:float, reason:string}
     */
    public static function computeTextImageCoherenceEmbedding(?array $textEmbedding, array $imageEmbeddings): array
    {
        if ($imageEmbeddings === []) {
            return ['status' => 'no_evaluada', 'score' => 0.0, 'reason' => 'Sin embeddings de imagen'];
        }

        if ($textEmbedding === null || $textEmbedding === []) {
            return ['status' => 'no_evaluada', 'score' => 0.0, 'reason' => 'Sin descripción para evaluar'];
        }

        $bestScore = 0.0;
        foreach ($imageEmbeddings as $imageEmbedding) {
            $vector = is_array($imageEmbedding) && isset($imageEmbedding['embedding']) && is_array($imageEmbedding['embedding'])
                ? $imageEmbedding['embedding']
                : $imageEmbedding;
            if (!is_array($vector) || $vector === []) {
                continue;
            }
            $bestScore = max($bestScore, self::cosineSimilarity($textEmbedding, $vector));
        }

        if ($bestScore >= 0.80) {
            return ['status' => 'alta', 'score' => round($bestScore, 4), 'reason' => 'La descripción es coherente con al menos una imagen.'];
        }

        if ($bestScore >= 0.50) {
            return ['status' => 'media', 'score' => round($bestScore, 4), 'reason' => 'Hay coherencia parcial entre texto e imagen.'];
        }

        return ['status' => 'baja', 'score' => round($bestScore, 4), 'reason' => 'La descripción no parece representar las imágenes provistas.'];
    }

    /**
     * @throws AIProviderException
     * @throws Exception
     */
    private static function embedAndPersist(
        string $text,
        int $productId,
        int $producerId
    ): array {
        $content = trim($text);
        if ($content === '') {
            throw new InvalidArgumentException('El texto a embeber no puede estar vacío.');
        }

        self::assertPgvectorAvailable();
        $embeddingResult = AIProviderRouter::generateTextEmbedding($content);
        $embedding = self::normalizeEmbedding($embeddingResult['embedding']);

        $stmt = Database::getInstance()->ejecutar('ai.fun_c_text_embedding', [
            ':product_id' => $productId,
            ':producer_id' => $producerId,
            ':content' => $content,
            ':text_embedding' => self::vectorLiteral($embedding),
        ]);

        return [
            'id' => (int) $stmt->fetchColumn(),
            'embedding' => $embedding,
            'provider' => (string) ($embeddingResult['provider'] ?? ''),
            'model' => (string) ($embeddingResult['model'] ?? ''),
        ];
    }

    /**
     * @throws AIProviderException
     * @throws Exception
     */
    private static function search(string $text, ?int $producerId, int $limit): array
    {
        $content = trim($text);
        if ($content === '') {
            throw new InvalidArgumentException('El texto de búsqueda no puede estar vacío.');
        }

        self::assertPgvectorAvailable();
        $embeddingResult = AIProviderRouter::generateTextEmbedding($content);
        $embedding = self::vectorLiteral(self::normalizeEmbedding($embeddingResult['embedding']));
        $queryName = $producerId === null ? 'ai.fun_val_search_similar_text' : 'ai.fun_val_search_similar_text_exclude';
        $params = [
            ':embedding' => $embedding,
            ':threshold' => self::SIMILARITY_THRESHOLD,
            ':limit' => max(1, $limit),
        ];
        if ($producerId !== null) {
            $params[':producer_id'] = $producerId;
        }

        $stmt = Database::getInstance()->ejecutar($queryName, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * @throws Exception
     */
    private static function assertPgvectorAvailable(): void
    {
        $stmt = Database::getInstance()->ejecutar('ai.fun_val_check_pgvector');
        $available = $stmt->fetchColumn();
        if ($available !== true && $available !== 't' && $available !== '1' && $available !== 1) {
            throw new RuntimeException('La extensión pgvector no está instalada. Ejecutá CREATE EXTENSION vector; antes de usar búsqueda vectorial.');
        }
    }

    private static function cosineSimilarity(array $a, array $b): float
    {
        $length = min(count($a), count($b));
        if ($length === 0) {
            return 0.0;
        }

        $dot = 0.0;
        $normA = 0.0;
        $normB = 0.0;
        for ($i = 0; $i < $length; $i++) {
            $av = (float) $a[$i];
            $bv = (float) $b[$i];
            $dot += $av * $bv;
            $normA += $av * $av;
            $normB += $bv * $bv;
        }

        if ($normA == 0.0 || $normB == 0.0) {
            return 0.0;
        }

        return max(-1.0, min(1.0, $dot / (sqrt($normA) * sqrt($normB))));
    }

    private static function normalizeEmbedding(array $embedding): array
    {
        $values = [];
        foreach ($embedding as $value) {
            if (!is_int($value) && !is_float($value) && !is_numeric($value)) {
                throw new InvalidArgumentException('El embedding contiene valores no numéricos.');
            }
            $values[] = (float) $value;
        }

        return self::padEmbedding($values);
    }

    private static function padEmbedding(array $embedding, int $targetDim = self::VECTOR_DIMENSION): array
    {
        if (count($embedding) > $targetDim) {
            return array_slice($embedding, 0, $targetDim);
        }

        while (count($embedding) < $targetDim) {
            $embedding[] = 0.0;
        }

        return $embedding;
    }

    private static function vectorLiteral(array $embedding): string
    {
        if ($embedding === []) {
            throw new InvalidArgumentException('El embedding no puede estar vacío.');
        }

        return '[' . implode(',', array_map(static fn ($value): string => (string) (float) $value, $embedding)) . ']';
    }
}
