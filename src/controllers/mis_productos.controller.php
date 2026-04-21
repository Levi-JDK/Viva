<?php
// Dashboard de vendedor - Mis Productos

require_once __DIR__ . '/../functions/auth_helper.php';
require_once __DIR__ . '/../services/MyProductsService.php';

$userData = AuthHelper::protectRoute();
$id_user = $userData->id_user;

try {
    $dashboard = MyProductsService::obtenerDashboardContext((int) $id_user);
    $es_productor = $dashboard['es_productor'];

    if (!$es_productor) {
        header('Location: ' . BASE_URL . 'registro_vendedor');
        exit;
    }

    $usuario = $dashboard['usuario'];
    $nombre_usuario = $dashboard['nombre_usuario'];
    $foto_usuario = $dashboard['foto_usuario'];
    $id_productor = $dashboard['id_productor'];

    $view = $_GET['view'] ?? 'inventory';

    $allowed_views = [
        'inventory'     => 'inventory.controller.php',
        'add_product'   => 'form_add_product.controller.php',
        'stand'         => 'stand.controller.php',
        'statistics'    => 'statistics.controller.php',
        'configuration' => 'configuration.controller.php',
    ];

    if (array_key_exists($view, $allowed_views)) {
        $current_controller = __DIR__ . '/mis_productos/' . $allowed_views[$view];
    } else {
        $current_controller = __DIR__ . '/mis_productos/inventory.controller.php';
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (file_exists($current_controller)) {
            require_once $current_controller;
            exit;
        }
    }

    require_once ROOT_PATH . "src/views/mis_productos.view.php";
} catch (Exception $e) {
    throw $e;
}
