<?php
require_once __DIR__ . '/../functions/auth_helper.php';
require_once __DIR__ . '/../services/OrderService.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'No hay acciones AJAX disponibles para este controlador.'
    ]);
    exit;
}

$userData = AuthHelper::protectRoute();
$id_user = $userData->id_user;

$id_factura = $_GET['id'] ?? null;

if (!$id_factura || !is_numeric($id_factura)) {
    header('Location: ' . BASE_URL . 'mi-cuenta');
    exit;
}

try {
    $detallePedido = OrderService::obtenerDetallePedido((int) $id_factura, (int) $id_user);

    if (!$detallePedido) {
        header('Location: ' . BASE_URL . 'mi-cuenta');
        exit;
    }

    $pedido = $detallePedido['pedido'];
    $detalles = $detallePedido['detalles'];
} catch (PDOException $e) {
    throw $e;
}

require_once ROOT_PATH . 'src/views/pedido.view.php';
