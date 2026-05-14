<?php

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/../workers/Config/RedisConfig.php';

if (!function_exists('viva_product_validation_images')) {
    function viva_product_validation_images(array $paths): array
    {
        return array_values(array_map(static function (string $path): array {
            return [
                'path' => $path,
                'url' => defined('BASE_URL') ? rtrim(BASE_URL, '/') . '/' . ltrim($path, '/') : $path,
                'temp_path' => $path,
            ];
        }, $paths));
    }
}

if (!function_exists('viva_enqueue_product_validation')) {
    function viva_enqueue_product_validation(int $productId, int $producerId, array $images, string $title, string $description, mixed $materials, mixed $category): void
    {
        try {
            $payload = json_encode([
                'product_id' => $productId,
                'producer_id' => $producerId,
                'productData' => [
                    'images' => $images,
                    'title' => $title,
                    'description' => $description,
                    'materials' => $materials ?? '',
                    'category' => (string) ($category ?? ''),
                ],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            if ($payload === false) {
                throw new RuntimeException('No se pudo serializar payload de validación IA.');
            }

            RedisConfig::getConnection()->lpush('viva:cola:validacion', $payload);
        } catch (Throwable $e) {
            error_log('No se pudo encolar validación IA: ' . $e->getMessage());
            throw new RuntimeException('No se pudo encolar la validación IA. Intente nuevamente.', 0, $e);
        }
    }
}

if (!function_exists('viva_reenqueue_product_validation')) {
    function viva_reenqueue_product_validation(int $productId): void
    {
        // Obtener datos del producto desde la DB
        $stmt = Database::getInstance()->ejecutar('obtenerDatosProductoValidacion', [
            ':id_producto' => $productId
        ]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$producto) {
            throw new InvalidArgumentException("Producto $productId no encontrado.");
        }

        // Obtener imágenes
        $stmt = Database::getInstance()->ejecutar('obtenerImagenesProducto', [
            ':id_producto' => $productId
        ]);
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $imagePaths = array_values(array_map(static function (array $img): array {
            $path = $img['url_imagen'] ?? '';
            return [
                'id_imagen' => (int) ($img['id_imagen'] ?? 0),
                'path' => $path,
                'url' => defined('BASE_URL') ? rtrim(BASE_URL, '/') . '/' . ltrim($path, '/') : $path,
                'temp_path' => null,
            ];
        }, $images));

        viva_enqueue_product_validation(
            $productId,
            (int) $producto['id_productor'],
            $imagePaths,
            $producto['nom_producto'] ?? '',
            $producto['descripcion_producto'] ?? '',
            $producto['id_materia'] ?? '',
            $producto['id_categoria'] ?? ''
        );
    }
}
