<?php

require_once __DIR__ . '/../functions/database.php';

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
            self::incrementarVista($db, $idProducto);

            $data['producto'] = $producto;
            $data['resenas'] = self::obtenerResenas($db, $idProducto);

            $promedio = self::obtenerPromedioResenas($db, $idProducto);
            $data['promedio_estrellas'] = $promedio['promedio_estrellas'];
            $data['total_resenas'] = $promedio['total_resenas'];
            $data['productos_relacionados'] = self::obtenerRelacionados($db, $producto, $idProducto);

            return $data;
        } catch (Exception $e) {
            throw $e;
        }
    }

    private static function normalizarProducto(array $producto): array
    {
        $imagenes = json_decode($producto['imagenes'] ?? '[]', true);
        $producto['imagenes'] = is_array($imagenes) ? $imagenes : [];
        $producto['imagen_principal'] = !empty($producto['imagenes'][0]['url'])
            ? $producto['imagenes'][0]['url']
            : 'images/default_product.png';

        return $producto;
    }

    private static function incrementarVista(Database $db, int $idProducto): void
    {
        try {
            $db->ejecutar('incrementarVistasProducto', [':id_producto' => $idProducto]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    private static function obtenerResenas(Database $db, int $idProducto): array
    {
        try {
            $stmt = $db->ejecutar('obtenerResenasProducto', [':id_producto' => $idProducto]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
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

            return array_slice(array_values($relacionados), 0, 4);
        } catch (Exception $e) {
            throw $e;
        }
    }
}
