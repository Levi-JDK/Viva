<?php
require_once __DIR__ . '/../services/CartService.php';
require_once __DIR__ . '/../functions/auth_helper.php';
require_once __DIR__ . '/../functions/error_handler.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido']);
    exit;
}

$rawInput = file_get_contents('php://input');
$input = [];

if ($rawInput !== false && trim($rawInput) !== '') {
    $input = json_decode($rawInput, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new InvalidArgumentException('JSON inválido en carrito.controller.php: ' . json_last_error_msg());
    }
}

$accion      = $input['accion']      ?? $_POST['accion']      ?? null;
$action      = $input['action']      ?? $_POST['action']      ?? null;
$id_producto = $input['id_producto'] ?? $_POST['id_producto'] ?? null;
$cantidad    = $input['cantidad']    ?? $_POST['cantidad']    ?? null;

if ($action === 'redis_update') {
    $userData = AuthHelper::protectRoute();
    $acciones = $input['acciones'] ?? null;

    if (!is_array($acciones)) {
        throw new InvalidArgumentException('Payload inválido para redis_update.');
    }

    echo json_encode(CartService::redisUpdate((int) $userData->id_user, $acciones));
    exit;
}

if ($action === 'flush_to_postgres') {
    $userData = AuthHelper::protectRoute();
    $forceSync = (bool) ($input['force_sync'] ?? $_POST['force_sync'] ?? false);
    $acciones = $input['acciones'] ?? $_POST['acciones'] ?? null;

    if ($acciones !== null && !is_array($acciones)) {
        throw new InvalidArgumentException('Payload inválido para flush_to_postgres.');
    }

    echo json_encode(CartService::flushToPostgres((int) $userData->id_user, $forceSync, $acciones));
    exit;
}

$userData = AuthHelper::protectRoute();
$id_user = $userData->id_user;

if (!$accion || !in_array($accion, ['obtener', 'agregar', 'eliminar', 'actualizar', 'limpiar'])) {
    echo json_encode([
        'exito'   => false,
        'mensaje' => 'Acción no válida. Use: obtener, agregar, eliminar, actualizar, limpiar o action=redis_update|flush_to_postgres'
    ]);
    exit;
}

try {
    echo json_encode(CartService::gestionarItemsCarrito((int) $id_user, $accion, $id_producto, $cantidad));

} catch (Exception $e) {
    $resp = ErrorHandler::jsonResponse($e, 'carrito.gestionarItems');
    echo json_encode($resp);
    exit;
}
