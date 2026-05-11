<?php

require_once __DIR__ . '/../functions/database.php';
require_once __DIR__ . '/../functions/error_handler.php';

class ProductDetailService
{
    public static function obtenerContexto(?int $idProducto): array
    {
        $data = [
            'producto' => null,
            'error_message' => null,
            'resenas' => [],
            'promedio_estrellas' => 0,
            'total_resenas' => 0,
            'productos_relacionados' => [],
        ];

        if (!$idProducto) {
            $data['error_message'] = 'Producto no válido.';

            return $data;
        }

        try {
            $db = Database::getInstance();
            $stmt = $db->ejecutar('obtenerDetalleProducto', [':id_producto' => $idProducto]);
            $producto = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$producto) {
                $data['error_message'] = 'Producto no encontrado.';

                return $data;
            }

            $producto = self::normalizarProducto($producto);
            $vistasActualizadas = self::incrementarVista($db, $idProducto);

            if ($vistasActualizadas !== null) {
                $producto['vistas'] = $vistasActualizadas;
            }

            $data['producto'] = $producto;
            $data['resenas'] = self::obtenerResenas($db, $idProducto);

            $promedio = self::obtenerPromedioResenas($db, $idProducto);
            $data['promedio_estrellas'] = $promedio['promedio_estrellas'];
            $data['total_resenas'] = $promedio['total_resenas'];
            $data['productos_relacionados'] = self::obtenerRelacionados($db, $producto, $idProducto);

            return $data;
        } catch (Exception $e) {
            ErrorHandler::handle($e, 'product_detail.obtenerContexto');
            throw $e;
        }
    }

    private static function normalizarProducto(array $producto): array
    {
        $producto['id_productor'] = isset($producto['id_productor']) && is_numeric($producto['id_productor'])
            ? (int) $producto['id_productor']
            : null;
        $producto['id_stand'] = isset($producto['id_stand']) && is_numeric($producto['id_stand'])
            ? (int) $producto['id_stand']
            : ($producto['id_productor'] ?? null);

        $producto['img_stand'] = self::normalizarRutaImagen($producto['img_stand'] ?? null);
        $producto['portada_stand'] = self::normalizarRutaImagen($producto['portada_stand'] ?? null);
        $producto['foto_user'] = self::normalizarRutaImagen($producto['foto_user'] ?? null);
        $producto['imagenes'] = self::normalizarImagenes($producto['imagenes'] ?? []);

        $primeraImagen = self::normalizarRutaImagen($producto['primera_imagen'] ?? $producto['imagen_principal'] ?? null);
        if ($primeraImagen === '') {
            $primeraImagen = $producto['imagenes'][0]['url'] ?? 'images/default.webp';
        }

        $producto['primera_imagen'] = $primeraImagen;
        $producto['imagen_principal'] = $primeraImagen;

        return $producto;
    }

    private static function incrementarVista(Database $db, int $idProducto): ?int
    {
        try {
            $stmt = $db->ejecutar('incrementarVistasProducto', [':id_producto' => $idProducto]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

            if (!isset($row['resultado']) || filter_var($row['resultado'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== true) {
                return null;
            }

            $stmtVistas = $db->ejecutar('obtenerVistasProducto', [':id_producto' => $idProducto]);
            $vistasRow = $stmtVistas->fetch(PDO::FETCH_ASSOC) ?: [];

            return isset($vistasRow['vistas']) ? (int) $vistasRow['vistas'] : null;
        } catch (Exception $e) {
            ErrorHandler::handle($e, 'product_detail.incrementarVista');
            throw $e;
        }
    }

    private static function obtenerResenas(Database $db, int $idProducto): array
    {
        try {
            $stmt = $db->ejecutar('obtenerResenasProducto', [':id_producto' => $idProducto]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            ErrorHandler::handle($e, 'product_detail.obtenerResenas');
            throw $e;
        }
    }

    private static function obtenerPromedioResenas(Database $db, int $idProducto): array
    {
        try {
            $stmt = $db->ejecutar('obtenerPromedioEstrellasProducto', [':id_producto' => $idProducto]);
            $promedioRow = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

            return [
                'promedio_estrellas' => round((float) ($promedioRow['promedio'] ?? 0), 1),
                'total_resenas' => (int) ($promedioRow['total_resenas'] ?? 0),
            ];
        } catch (Exception $e) {
            ErrorHandler::handle($e, 'product_detail.obtenerPromedioResenas');
            throw $e;
        }
    }

    private static function obtenerRelacionados(Database $db, array $producto, int $idProducto): array
    {
        try {
            $relacionados = $db->obtenerProductosCatalogoFiltrado([
                'categoria' => $producto['id_categoria'] ?? null,
            ]);

            $relacionados = array_filter($relacionados, static function (array $productoRelacionado) use ($idProducto): bool {
                return (int) ($productoRelacionado['id_producto'] ?? 0) !== $idProducto;
            });

            $relacionados = array_map([self::class, 'normalizarProductoCatalogoRelacionado'], array_values($relacionados));

            return array_slice($relacionados, 0, 4);
        } catch (Exception $e) {
            ErrorHandler::handle($e, 'product_detail.obtenerRelacionados');
            throw $e;
        }
    }

    private static function normalizarProductoCatalogoRelacionado(array $producto): array
    {
        $primeraImagen = self::normalizarRutaImagen($producto['primera_imagen'] ?? null);

        if ($primeraImagen === '') {
            $primeraImagen = 'images/default.webp';
        }

        $producto['id_stand'] = isset($producto['id_stand']) && is_numeric($producto['id_stand'])
            ? (int) $producto['id_stand']
            : (isset($producto['id_productor']) && is_numeric($producto['id_productor']) ? (int) $producto['id_productor'] : 0);
        $producto['img_stand'] = self::normalizarRutaImagen($producto['img_stand'] ?? null);
        $producto['primera_imagen'] = $primeraImagen;

        return $producto;
    }

    private static function normalizarImagenes(mixed $imagenes): array
    {
        if (is_string($imagenes)) {
            $decoded = json_decode($imagenes, true);
            $imagenes = json_last_error() === JSON_ERROR_NONE ? $decoded : [];
        }

        if (!is_array($imagenes)) {
            return [];
        }

        $normalizadas = [];

        foreach ($imagenes as $imagen) {
            if (is_string($imagen)) {
                $url = self::normalizarRutaImagen($imagen);
                if ($url !== '') {
                    $normalizadas[] = ['url' => $url];
                }
                continue;
            }

            if (!is_array($imagen)) {
                continue;
            }

            $url = self::normalizarRutaImagen($imagen['url'] ?? $imagen['url_imagen'] ?? $imagen['src'] ?? null);
            if ($url === '') {
                continue;
            }

            $imagen['url'] = $url;
            $normalizadas[] = $imagen;
        }

        return array_values($normalizadas);
    }

    private static function normalizarRutaImagen(mixed $path): string
    {
        if (!is_string($path)) {
            return '';
        }

        $path = trim($path);
        if ($path === '') {
            return '';
        }

        return $path;
    }
}
