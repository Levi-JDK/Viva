<?php
/**
 * API REST — Catálogo de Productos
 * 
 * Método: GET
 * Respuesta: JSON
 * 
 * Parámetros opcionales (query string):
 *   q          → Búsqueda de texto
 *   cat        → ID de categoría
 *   oficio     → ID de oficio artesanal
 *   materia    → ID de materia prima
 *   min_price  → Precio mínimo
 *   max_price  → Precio máximo
 */

// === Cabeceras de API REST ===
header('Content-Type: application/json; charset=UTF-8');
header('X-Content-Type-Options: nosniff');



require_once(__DIR__ . '/../functions/database.php');

$db = Database::getInstance();

// === Capturar y sanear filtros de la petición GET ===
$filtros = [
    'search'    => isset($_GET['q'])         ? trim($_GET['q'])         : null,
    'categoria' => isset($_GET['cat'])        ? intval($_GET['cat'])     : null,
    'oficio'    => isset($_GET['oficio'])     ? intval($_GET['oficio'])  : null,
    'materia'   => isset($_GET['materia'])    ? intval($_GET['materia']) : null,
    'min_price' => isset($_GET['min_price'])  ? floatval($_GET['min_price']) : null,
    'max_price' => isset($_GET['max_price'])  ? floatval($_GET['max_price']) : null,
];

try {
    $productos = $db->obtenerProductosCatalogoFiltrado($filtros);

    // Determinar BASE_URL para construir URLs absolutas de imágenes
    // La constante BASE_URL puede no estar definida si se accede directamente al archivo.
    $base_url = defined('BASE_URL') ? BASE_URL : '/viva/';

    // Mapear los resultados a un array limpio y seguro para el frontend
    $data = array_map(function($p) use ($base_url) {
        return [
            'id_producto'     => (int) ($p['id_producto'] ?? 0),
            'nom_producto'    => $p['nom_producto'] ?? '',
            'precio_producto' => (float) ($p['precio_producto'] ?? 0),
            'id_productor'    => (int) ($p['id_productor'] ?? 0),
            'nom_stand'       => $p['nom_stand'] ?? 'Stand artesanal',
            'img_stand'       => !empty($p['img_stand'])
                                    ? $base_url . $p['img_stand']
                                    : $base_url . 'images/default.jpg',
            'imagen_producto' => !empty($p['primera_imagen'])
                                    ? $base_url . $p['primera_imagen']
                                    : $base_url . 'images/default_product.jpg',
            'url_producto'    => $base_url . 'producto?id=' . ($p['id_producto'] ?? ''),
            'url_stand'       => $base_url . 'stand/' . ($p['id_productor'] ?? ''),
        ];
    }, $productos);

    echo json_encode([
        'success' => true,
        'total'   => count($data),
        'data'    => $data,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener los productos: ' . $e->getMessage(),
        'data'    => [],
    ], JSON_UNESCAPED_UNICODE);
}
