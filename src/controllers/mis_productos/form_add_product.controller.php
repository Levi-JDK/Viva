<?php
require_once __DIR__ . '/../../services/MyProductsService.php';

try {
    $id_prod_edit = isset($_GET['id']) && is_numeric($_GET['id']) ? (int) $_GET['id'] : null;
    $formulario = MyProductsService::obtenerDatosFormularioProducto((int) ($id_productor ?? 0), $id_prod_edit);

    if ($formulario['debe_redirigir']) {
        header('Location: ' . BASE_URL . 'mis_productos');
        exit;
    }

    $categorias = $formulario['categorias'];
    $colores = $formulario['colores'];
    $oficios = $formulario['oficios'];
    $materias = $formulario['materias'];
    $producto_editar = $formulario['producto_editar'];
} catch (Exception $e) {
    throw $e;
}

require_once ROOT_PATH . "src/views/mis_productos/form_add_product.view.php";
