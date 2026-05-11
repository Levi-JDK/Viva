<?php
require_once __DIR__ . '/../../services/MyProductsService.php';
require_once __DIR__ . '/../../functions/error_handler.php';

$stats = [
    'total_productos' => 0,
    'productos_activos' => 0,
    'productos_inactivos' => 0,
    'vistas_totales' => 0,
    'stock_total' => 0,
    'promedio_vistas' => 0,
];
$top_productos = [];

try {
    $estadisticas = MyProductsService::obtenerEstadisticas((int) ($id_productor ?? 0));
    $stats = $estadisticas['stats'];
    $top_productos = $estadisticas['top_productos'];
} catch (Exception $e) {
    ErrorHandler::handle($e, 'mis_productos.statistics.obtenerEstadisticas');
    throw $e;
}

require_once ROOT_PATH . "src/views/mis_productos/statistics.view.php";
