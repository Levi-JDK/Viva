<?php
require_once __DIR__ . '/../functions/auth_helper.php';
require_once __DIR__ . '/../functions/error_handler.php';
require_once __DIR__ . '/../services/OrderService.php';

if (isset($_GET['ajax']) && $_GET['ajax'] === 'tracking') {
    $userData = AuthHelper::protectRoute();
    $idFacturaAjax = $_GET['id'] ?? null;

    header('Content-Type: application/json');

    if (!$idFacturaAjax || !is_numeric($idFacturaAjax)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Pedido inválido.', 'checkpoints' => []]);
        exit;
    }

    $response = OrderService::obtenerCheckpoints((int) $idFacturaAjax, (int) $userData->id_user);
    if (($response['success'] ?? false) !== true) {
        http_response_code(403);
    }

    echo json_encode($response);
    exit;
}

if (isset($_GET['ajax']) && $_GET['ajax'] === 'confirm_delivery') {
    $userData = AuthHelper::protectRoute();
    $idFacturaAjax = $_GET['id'] ?? null;

    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
        exit;
    }

    if (!$idFacturaAjax || !is_numeric($idFacturaAjax)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Pedido inválido.']);
        exit;
    }

    $response = OrderService::confirmarEntrega((int) $idFacturaAjax, (int) $userData->id_user);
    if (($response['success'] ?? false) !== true) {
        http_response_code(400);
    }

    echo json_encode($response);
    exit;
}

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
    ErrorHandler::handle($e, 'pedido.obtenerDetallePedido');
    throw $e;
}

require_once ROOT_PATH . 'src/views/pedido.view.php';
