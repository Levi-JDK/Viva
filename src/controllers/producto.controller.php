<?php
// Controlador para el detalle de producto


require_once __DIR__ . '/../services/ProductDetailService.php';

$id_producto = isset($_GET['id']) && is_numeric($_GET['id']) ? (int) $_GET['id'] : null;

extract(ProductDetailService::obtenerContexto($id_producto));

require_once ROOT_PATH . "src/views/producto.view.php";
