<?php

require_once __DIR__ . '/../functions/database.php';
require_once __DIR__ . '/../functions/error_handler.php';

class ProductService
{
    public static function obtenerRespuestaCatalogoApi(array $query): array
    {
        try {
            $db = Database::getInstance();
            $productos = $db->obtenerProductosCatalogoFiltrado(self::normalizarFiltros($query, true));
            $data = array_map([self::class, 'formatearProductoCatalogo'], $productos);

            return [
                'http_code' => 200,
                'payload' => [
                    'success' => true,
                    'total' => count($data),
                    'data' => $data,
                ],
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }

    public static function obtenerDatosCatalogo(array $query): array
    {
        $filtros = self::normalizarFiltros($query, false);

        try {
            $db = Database::getInstance();
            $stmtCats = $db->ejecutar('obtenerFiltrosCategorias');
            $stmtOficios = $db->ejecutar('obtenerFiltrosOficios');
            $stmtMaterias = $db->ejecutar('obtenerFiltrosMaterias');

            return [
                'filtros' => $filtros,
                'categorias_list' => $stmtCats->fetchAll(PDO::FETCH_ASSOC),
                'oficios_list' => $stmtOficios->fetchAll(PDO::FETCH_ASSOC),
                'materias_list' => $stmtMaterias->fetchAll(PDO::FETCH_ASSOC),
                'productos' => $db->obtenerProductosCatalogoFiltrado($filtros),
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }

    private static function normalizarFiltros(array $query, bool $castNumbers): array
    {
        return [
            'search' => isset($query['q']) ? trim((string) $query['q']) : null,
            'categoria' => self::normalizarNumero($query['cat'] ?? null, $castNumbers, 'int'),
            'oficio' => self::normalizarNumero($query['oficio'] ?? null, $castNumbers, 'int'),
            'materia' => self::normalizarNumero($query['materia'] ?? null, $castNumbers, 'int'),
            'min_price' => self::normalizarNumero($query['min_price'] ?? null, $castNumbers, 'float'),
            'max_price' => self::normalizarNumero($query['max_price'] ?? null, $castNumbers, 'float'),
        ];
    }

    private static function normalizarNumero(mixed $value, bool $castNumbers, string $type): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!$castNumbers) {
            return $value;
        }

        return $type === 'float' ? (float) $value : (int) $value;
    }

    private static function formatearProductoCatalogo(array $producto): array
    {
        $baseUrl = defined('BASE_URL') ? BASE_URL : '/viva/';

        return [
            'id_producto' => (int) ($producto['id_producto'] ?? 0),
            'nom_producto' => $producto['nom_producto'] ?? '',
            'precio_producto' => (float) ($producto['precio_producto'] ?? 0),
            'id_productor' => (int) ($producto['id_productor'] ?? 0),
            'nom_stand' => $producto['nom_stand'] ?? 'Stand artesanal',
            'img_stand' => !empty($producto['img_stand'])
                ? $baseUrl . $producto['img_stand']
                : $baseUrl . 'images/profiles/default.webp',
            'imagen_producto' => !empty($producto['primera_imagen'])
                ? $baseUrl . $producto['primera_imagen']
                : $baseUrl . 'images/default_product.jpg',
            'url_producto' => $baseUrl . 'producto?id=' . ($producto['id_producto'] ?? ''),
            'url_stand' => $baseUrl . 'stand/' . ($producto['id_productor'] ?? ''),
        ];
    }
}
