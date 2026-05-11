<?php

require_once ROOT_PATH . 'src/functions/auth_helper.php';
require_once ROOT_PATH . 'src/functions/admin_graphics.php';
require_once ROOT_PATH . 'src/services/UserService.php';

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

$menuIds = UserService::obtenerMenuIdsUsuario((int) ($userData->id_user ?? 0));
if (!in_array(8, $menuIds, true)) {
    http_response_code(403);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'Acceso denegado al panel de administración.',
        'data' => [],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$data = [
    'revenue_vs_orders' => getRevenueVsOrders(),
    'top_products' => getTopProducts(5),
    'category_distribution' => getCategoryDistribution(),
];

echo json_encode([
    'exito' => true,
    'mensaje' => 'Estadísticas de administrador obtenidas correctamente.',
    'data' => $data,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
