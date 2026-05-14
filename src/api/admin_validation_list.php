<?php
/**
 * Admin: listar productos por estado de validación IA.
 * GET /src/api/admin_validation_list.php?status=pending_review&page=1&limit=20
 */

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR);
}

require_once ROOT_PATH . 'src/functions/auth_helper.php';
require_once ROOT_PATH . 'src/functions/database.php';
require_once ROOT_PATH . 'src/functions/error_handler.php';
require_once ROOT_PATH . 'src/services/UserService.php';

header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido', 'data' => null], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    $userData = AuthHelper::verifyToken();
    if (!$userData) {
        http_response_code(401);
        echo json_encode(['exito' => false, 'mensaje' => 'La sesión ha expirado o no es válida.', 'data' => null], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    $menuIds = UserService::obtenerMenuIdsUsuario((int) ($userData->id_user ?? 0));
    if (!in_array(8, $menuIds, true)) {
        http_response_code(403);
        echo json_encode(['exito' => false, 'mensaje' => 'Solo administradores pueden acceder a este recurso.', 'data' => null], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    $status = $_GET['status'] ?? null;
    $validStatuses = ['pending_review', 'approved', 'rejected'];
    if ($status !== null && $status !== '' && !in_array($status, $validStatuses, true)) {
        http_response_code(400);
        echo json_encode(['exito' => false, 'mensaje' => 'Status inválido. Valores: ' . implode(', ', $validStatuses), 'data' => null], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
    $status = $status === '' ? null : $status;

    $page = max(1, (int) ($_GET['page'] ?? 1));
    $limit = min(100, max(1, (int) ($_GET['limit'] ?? 20)));
    $offset = ($page - 1) * $limit;

    $db = Database::getInstance();
    $params = [':validation_status' => $status ?? ''];

    $stmtCount = $db->ejecutar('contarProductosPorValidacionStatus', $params);
    $total = (int) (($stmtCount->fetch(PDO::FETCH_ASSOC)['total'] ?? 0));

    $stmt = $db->ejecutar('productosPorValidacionStatus', $params + [
        ':limit' => $limit,
        ':offset' => $offset,
    ]);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'exito' => true,
        'mensaje' => 'Productos de validación obtenidos correctamente.',
        'data' => [
            'productos' => $productos,
            'items' => $productos,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => (int) max(1, ceil($total / $limit)),
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    $resp = ErrorHandler::jsonResponse($e, 'admin_validation_list');
    echo json_encode([
        'exito' => false,
        'mensaje' => $resp['mensaje'] ?? $resp['message'] ?? 'Error en el servidor. Por favor, intente más tarde.',
        'data' => null,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
