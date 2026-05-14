<?php

require_once __DIR__ . '/../../exceptions/AIProviderException.php';
require_once __DIR__ . '/../../services/HashService.php';
require_once __DIR__ . '/../../services/ImageSignatureService.php';
require_once __DIR__ . '/../../services/AIProviderRouter.php';
require_once __DIR__ . '/../../services/TextEmbeddingService.php';
require_once __DIR__ . '/../../services/ProductValidationService.php';
require_once __DIR__ . '/../../functions/database.php';
require_once __DIR__ . '/../../utils/image_uploader.php';

class ProductValidationJob
{
    private int $productId;
    private int $producerId;
    private array $productData;
    private bool $hasTempImages = false;
    private array $tempPaths = [];
    private static $validator = null;

    public function __construct(int $productId, int $producerId, array $productData)
    {
        $this->productId = $productId;
        $this->producerId = $producerId;
        $this->productData = $productData;
    }

    public static function fromRedis(array $data): self
    {
        if (empty($data['product_id'])) {
            throw new InvalidArgumentException('Mensaje de validación inválido: falta product_id.');
        }

        if (empty($data['producer_id'])) {
            throw new InvalidArgumentException('Mensaje de validación inválido: falta producer_id.');
        }

        $productData = $data['productData'] ?? $data['product_data'] ?? null;
        if (!is_array($productData)) {
            $productData = [
                'images' => $data['images'] ?? [],
                'title' => $data['title'] ?? '',
                'description' => $data['description'] ?? '',
                'materials' => $data['materials'] ?? '',
                'category' => $data['category'] ?? '',
            ];
        }

        $productData['images'] = is_array($productData['images'] ?? null) ? $productData['images'] : [];
        $productData['title'] = (string) ($productData['title'] ?? '');
        $productData['description'] = (string) ($productData['description'] ?? '');
        $productData['materials'] = $productData['materials'] ?? '';
        $productData['category'] = (string) ($productData['category'] ?? '');

        $self = new self((int) $data['product_id'], (int) $data['producer_id'], $productData);

        // Detect if this payload includes temp images to process
        $images = $productData['images'] ?? [];
        $self->hasTempImages = !empty($images) && isset($images[0]['temp_path']) && $images[0]['temp_path'] !== null;
        $self->tempPaths = [];
        if ($self->hasTempImages) {
            foreach ($images as $img) {
                if (!empty($img['temp_path'])) {
                    $self->tempPaths[] = [
                        'temp_path' => $img['temp_path'],
                        'id_imagen' => (int) ($img['id_imagen'] ?? 0),
                    ];
                }
            }
        }

        return $self;
    }

    public function handle(): void
    {
        self::validateEnvVars();

        // Process temp images first (move from temp, generate variants)
        $this->processTempImages();

        $validator = self::$validator;
        $result = $validator !== null
            ? $validator($this->productId, $this->producerId, $this->productData['images'], $this->productData)
            : ProductValidationService::validate($this->productId, $this->producerId, $this->productData['images'], $this->productData);

        error_log('[ProductValidationJob] Producto ' . $this->productId . ' validado. decision=' . ($result['decision'] ?? 'desconocida'));
    }

    private function processTempImages(): void
    {
        if (!$this->hasTempImages || empty($this->tempPaths)) {
            return;
        }

        $db = Database::getInstance();
        $rootDir = dirname(__DIR__, 3); // /var/www/html/viva
        $targetDir = $rootDir . '/images/products/';
        $tempDir = $rootDir . '/images/products/temp/';

        if (!is_dir($targetDir)) {
            throw new RuntimeException("El directorio de imágenes no existe: {$targetDir}");
        }

        foreach ($this->tempPaths as $tempImage) {
            $tempPath = $rootDir . '/' . $tempImage['temp_path'];
            $idImagen = $tempImage['id_imagen'];

            if (!file_exists($tempPath)) {
                error_log("[ProductValidationJob] Archivo temporal no encontrado: {$tempPath}");
                continue;
            }

            // Generar nombre final
            $ext = strtolower(pathinfo($tempPath, PATHINFO_EXTENSION));
            $finalName = 'prod_' . $this->productId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $finalPath = $targetDir . $finalName;
            $webPath = 'images/products/' . $finalName;

            // Mover archivo temporal a ubicación final
            if (!copy($tempPath, $finalPath)) {
                throw new RuntimeException("No se pudo mover la imagen a: {$finalPath}");
            }

            try {
                // Generar variantes (WebP, thumb, medium) - función de image_uploader.php
                $variants = generateVariants($finalPath);
            } catch (Throwable $e) {
                // Si falla la generación de variantes, al menos dejamos la original
                error_log("[ProductValidationJob] Error generando variantes para {$finalPath}: " . $e->getMessage());
                $variants = [];
            }

            // Actualizar la ruta en tab_imagenes
            if ($idImagen > 0) {
                $db->ejecutar('actualizarUrlImagenPorId', [
                    ':url' => $webPath,
                    ':id' => $idImagen,
                ]);
            } else {
                // Si no hay id_imagen (viejo formato), buscar por temp_path
                $db->ejecutar('actualizarUrlImagen', [
                    ':url' => $webPath,
                    ':temp' => $tempImage['temp_path'],
                ]);
            }

            // Eliminar archivo temporal
            @unlink($tempPath);
        }

        // Actualizar estado del producto a pending_review
        $db->ejecutar('actualizarValidacionStatus', [
            ':id_producto' => $this->productId,
            ':validation_status' => 'pending_review',
            ':is_active' => 'false',
        ]);

        // Recargar paths de imágenes desde DB para que la validación use los paths finales
        $stmt = $db->ejecutar('obtenerImagenesProducto', [':id_producto' => $this->productId]);
        $finalImages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($finalImages)) {
            $publicBase = rtrim((string) ($_ENV['AI_PUBLIC_URL'] ?? 'http://135.119.114.214/viva'), '/');
            $this->productData['images'] = array_values(array_map(static function (array $img) use ($publicBase): array {
                $path = $img['url_imagen'] ?? '';
                return [
                    'id_imagen' => (int) ($img['id_imagen'] ?? 0),
                    'path' => $path,
                    'url' => $publicBase . '/' . ltrim($path, '/'),
                ];
            }, $finalImages));
        }

        error_log('[ProductValidationJob] Imágenes procesadas para producto ' . $this->productId . ' → pending_review');
    }

    public static function setValidatorForTest(?callable $validator): void
    {
        self::$validator = $validator;
    }

    private static function validateEnvVars(): void
    {
        foreach (self::requiredEnvVars() as $varName) {
            if (!isset($_ENV[$varName]) || trim((string) $_ENV[$varName]) === '') {
                throw new RuntimeException('Variable de entorno requerida no configurada: ' . $varName);
            }
        }
    }

    private static function requiredEnvVars(): array
    {
        return [
            'AI_EMBEDDING_MODEL',
            'AI_DECISION_MODEL',
            'AI_PRIMARY_PROVIDER',
            'AI_SECONDARY_PROVIDER',
        ];
    }
}
