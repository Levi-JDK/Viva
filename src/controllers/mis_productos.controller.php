<?php
// Dashboard de vendedor - Mis Productos

require_once __DIR__ . '/../functions/auth_helper.php';
require_once __DIR__ . '/../services/MyProductsService.php';
require_once ROOT_PATH . 'src/utils/image_processing.php';

$userData = AuthHelper::protectRoute();
AuthHelper::checkAccess(10);
$id_user = $userData->id_user;

// Obtener contexto del vendedor (mínimo necesario para autenticar sub-rutas)
$dashboard = MyProductsService::obtenerDashboardContext((int) $id_user);
$es_productor = $dashboard['es_productor'];

if (!$es_productor) {
    header('Location: ' . BASE_URL . 'registro_vendedor');
    exit;
}

$id_productor = $dashboard['id_productor'];

// Determinar qué vista/sub-controller usar
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

// POST: procesar inmediatamente SIN renderizar la vista
if ($_SERVER['REQUEST_METHOD'] === 'POST' && file_exists($current_controller)) {
    require_once $current_controller;
    exit;
}

// GET: renderizar la vista completa
$usuario = $dashboard['usuario'];
$nombre_usuario = $dashboard['nombre_usuario'];
$foto_usuario = $dashboard['foto_usuario'];

require_once ROOT_PATH . "src/views/mis_productos.view.php";
