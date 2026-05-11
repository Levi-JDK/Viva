<?php
require_once __DIR__ . '/../../services/MyProductsService.php';
require_once __DIR__ . '/../../functions/error_handler.php';

$productos = [];
$total_productos = 0;
$productos_activos = 0;
$vistas_totales = 0;

try {
    $inventario = MyProductsService::obtenerInventario((int) ($id_productor ?? 0));
    $productos = $inventario['productos'];
    $total_productos = $inventario['total_productos'];
    $productos_activos = $inventario['productos_activos'];
    $vistas_totales = $inventario['vistas_totales'];
} catch (Exception $e) {
    ErrorHandler::handle($e, 'mis_productos.inventory.obtenerInventario');
    throw $e;
}

require_once ROOT_PATH . "src/views/mis_productos/kpi_cards.view.php";
require_once ROOT_PATH . "src/views/mis_productos/inventory.view.php";
