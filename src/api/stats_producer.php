<?php

require_once ROOT_PATH . 'src/functions/auth_helper.php';
require_once ROOT_PATH . 'src/functions/database.php';
require_once ROOT_PATH . 'src/functions/producer_graphics.php';

header('Content-Type: application/json; charset=utf-8');

$userData = AuthHelper::verifyToken();
if (!$userData) {
    http_response_code(401);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'La sesión ha expirado o no es válida.',
        'data' => [],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$idUser = (int) ($userData->id_user ?? 0);

$stmt = Database::getInstance()->ejecutar('obtenerIdProductor', [':id_user' => $idUser]);
$idProductor = (int) ($stmt->fetchColumn() ?: 0);

if ($idProductor <= 0) {
    http_response_code(403);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'Acceso denegado: la sesión no pertenece a un productor.',
        'data' => [],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$data = [
    'revenue_vs_sales' => getProducerRevenueVsSales($idProductor),
    'top_products' => getProducerTopProducts($idProductor, 3),
    'shipping_status' => getProducerShippingStatus($idProductor),
];

echo json_encode([
    'exito' => true,
    'mensaje' => 'Estadísticas del productor obtenidas correctamente.',
    'data' => $data,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
