<?php

require_once __DIR__ . '/../functions/database.php';
require_once __DIR__ . '/AIProviderRouter.php';

class ImageSignatureService
{
    private const VECTOR_DIMENSION = 2048;

    /**
     * @param string $fileHash
     * @param string|null $phashBin
     * @param string|null $dhashBin
     * @param int $excludeProductId
     * @param int $excludeImageId
     * @param int $limit
     * @return array
     * @throws Exception
     */
    public static function findHashMatchesUnified(
        string $fileHash,
        ?string $phashBin,
        ?string $dhashBin,
        int $excludeProductId,
        int $excludeImageId,
        int $limit = 20
    ): array {
        $stmt = Database::getInstance()->ejecutar('ai.fun_val_unified_hash_search', [
            ':file_hash' => strtolower($fileHash),
            ':phash' => $phashBin,
            ':dhash' => $dhashBin,
            ':exclude_product_id' => $excludeProductId,
            ':exclude_image_id' => $excludeImageId,
            ':phash_threshold' => (int) ($_ENV['AI_PHASH_THRESHOLD'] ?? 10),
            ':dhash_threshold' => (int) ($_ENV['AI_DHASH_THRESHOLD'] ?? 10),
            ':limit' => max(1, min(1000, $limit)),
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * @throws Exception
     */
    public static function saveImageHashes(int $productId, int $imageId, string $fileHash, string $phash, string $dhash): void
    {
        Database::getInstance()->ejecutar('updateImageHashes', [
            ':id_producto' => $productId,
            ':id_imagen' => $imageId,
            ':file_hash' => strtolower($fileHash),
            ':phash' => $phash,
            ':dhash' => $dhash,
        ]);
    }

    /**
     * @param array $embedding
     * @throws Exception
     */
    public static function saveVisualEmbedding(int $productId, int $imageId, array $embedding, string $model, string $semanticDescription = ''): void
    {
        Database::getInstance()->ejecutar('ai.fun_c_visual_embedding', [
            ':id_producto' => $productId,
            ':id_imagen' => $imageId,
            ':visual_embedding' => self::vectorLiteral(self::normalizeEmbedding($embedding)),
            ':embedding_model' => $model,
            ':semantic_description' => $semanticDescription,
        ]);
    }

    /**
     * @param array $embedding
     * @param float $threshold
     * @param int $limit
     * @return array
     * @throws Exception
     */
    public static function findSimilarByVector(array $embedding, float $threshold = 0.90, int $limit = 10): array
    {
        self::assertPgvectorAvailable();

        $stmt = Database::getInstance()->ejecutar('ai.fun_val_similar_by_vector', [
            ':embedding' => self::vectorLiteral($embedding),
            ':threshold' => $threshold,
            ':limit' => max(1, $limit),
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * @param array $embedding
     * @param int $producerId
     * @param float $threshold
     * @param int $limit
     * @return array
     * @throws Exception
     */
    public static function findSimilarByVectorExcludingProducer(array $embedding, int $producerId, float $threshold = 0.90, int $limit = 10): array
    {
        self::assertPgvectorAvailable();

        $stmt = Database::getInstance()->ejecutar('ai.fun_val_similar_by_vector_exclude', [
            ':embedding' => self::vectorLiteral($embedding),
            ':producer_id' => $producerId,
            ':threshold' => $threshold,
            ':limit' => max(1, $limit),
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * @param array $embedding
     * @param string $status approved|rejected|pending_review
     * @param float $threshold
     * @param int $limit
     * @return array
     * @throws Exception
     */
    public static function findSimilarByStatus(array $embedding, string $status, float $threshold = 0.75, int $limit = 5): array
    {
        self::assertPgvectorAvailable();

        $stmt = Database::getInstance()->ejecutar('ai.fun_val_similar_by_status', [
            ':embedding' => self::vectorLiteral($embedding),
            ':status' => $status,
            ':threshold' => $threshold,
            ':limit' => max(1, $limit),
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * @param array $productIds
     * @return array
     */
    public static function getEmbeddingsByProductIds(array $productIds): array
    {
        $ids = [];
        foreach ($productIds as $productId) {
            if (is_int($productId) || is_float($productId) || is_numeric($productId)) {
                $ids[] = (int) $productId;
            }
        }

        $ids = array_values(array_unique(array_filter($ids, static fn (int $id): bool => $id > 0)));
        if ($ids === []) {
            return [];
        }

        try {
            $stmt = Database::getInstance()->ejecutar('ai.fun_val_visual_embeddings_by_products', [
                ':p_product_ids' => self::numericArrayLiteral($ids),
            ]);

            $grouped = [];
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            foreach ($rows as $row) {
                $productId = (int) ($row['id_producto'] ?? 0);
                if ($productId <= 0) {
                    continue;
                }

                $embedding = self::parseVector((string) ($row['visual_embedding'] ?? ''));
                if ($embedding === []) {
                    continue;
                }

                $grouped[$productId][] = [
                    'id_imagen' => (int) ($row['id_imagen'] ?? 0),
                    'url_imagen' => (string) ($row['url_imagen'] ?? ''),
                    'embedding' => $embedding,
                    'visual_embedding' => $embedding,
                ];
            }

            return $grouped;
        } catch (Throwable $exception) {
            error_log('[ImageSignatureService] No se pudieron obtener embeddings visuales por producto: ' . $exception->getMessage());
            return [];
        }
    }

    /**
     * @return void
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

    /**
     * @param array $embedding
     * @return string
     */
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

    private static function normalizeEmbedding(array $embedding): array
    {
        $values = [];
        foreach ($embedding as $value) {
            if (!is_int($value) && !is_float($value) && !is_numeric($value)) {
                throw new InvalidArgumentException('El embedding contiene valores no numéricos.');
            }
            $values[] = (float) $value;
        }

        if (count($values) > self::VECTOR_DIMENSION) {
            return array_slice($values, 0, self::VECTOR_DIMENSION);
        }

        while (count($values) < self::VECTOR_DIMENSION) {
            $values[] = 0.0;
        }

        return $values;
    }

    /**
     * @param int[] $values
     * @return string
     */
    private static function numericArrayLiteral(array $values): string
    {
        return '{' . implode(',', array_map(static fn (int $value): string => (string) $value, $values)) . '}';
    }

    /**
     * @return float[]
     */
    private static function parseVector(string $value): array
    {
        $value = trim($value);
        if ($value === '') {
            return [];
        }

        if ($value[0] === '[') {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                return array_map('floatval', $decoded);
            }
        }

        $trimmed = trim($value, '()[]{}');
        if ($trimmed === '') {
            return [];
        }

        return array_map('floatval', explode(',', $trimmed));
    }
}
