<?php

require_once __DIR__ . '/../functions/database.php';
require_once __DIR__ . '/AIProviderRouter.php';

class ImageSignatureService
{
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
    public static function saveVisualEmbedding(int $productId, int $imageId, array $embedding, string $model): void
    {
        Database::getInstance()->ejecutar('ai.fun_c_visual_embedding', [
            ':id_producto' => $productId,
            ':id_imagen' => $imageId,
            ':visual_embedding' => self::vectorLiteral($embedding),
            ':embedding_model' => $model,
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
}
