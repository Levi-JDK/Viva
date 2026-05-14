<?php
/**
 * Admin: acciones sobre validación de productos.
 * POST /src/api/admin_validation_action.php
 * Body: { product_id: int, action: "approve"|"reject"|"reprocess", motivo?: string }
 */

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR);
}

require_once ROOT_PATH . 'src/functions/auth_helper.php';
require_once ROOT_PATH . 'src/functions/database.php';
require_once ROOT_PATH . 'src/functions/error_handler.php';
require_once ROOT_PATH . 'src/functions/product_validation_queue.php';
require_once ROOT_PATH . 'src/services/UserService.php';

header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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
        echo json_encode(['exito' => false, 'mensaje' => 'Solo administradores pueden realizar esta acción.', 'data' => null], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    $input = json_decode(file_get_contents('php://input') ?: '{}', true);
    if (!is_array($input)) {
        http_response_code(400);
        echo json_encode(['exito' => false, 'mensaje' => 'Body debe ser JSON válido.', 'data' => null], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    $productId = (int) ($input['product_id'] ?? 0);
    $action = (string) ($input['action'] ?? '');

    if ($productId <= 0) {
        http_response_code(400);
        echo json_encode(['exito' => false, 'mensaje' => 'product_id es requerido.', 'data' => null], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    $db = Database::getInstance();

    // Verificar que el producto existe
    $stmtCheck = $db->ejecutar('obtenerProductoPorId', [':id_producto' => $productId]);
    $productData = $stmtCheck->fetch(PDO::FETCH_ASSOC);
    if (!$productData) {
        http_response_code(400);
        echo json_encode(['exito' => false, 'mensaje' => 'El producto solicitado no existe.', 'data' => null], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
    $producerId = (int) ($productData['id_productor'] ?? 0);

    $validActions = ['approve', 'reject', 'reprocess'];
    if (!in_array($action, $validActions, true)) {
        http_response_code(400);
        echo json_encode(['exito' => false, 'mensaje' => 'Acción inválida. Valores: ' . implode(', ', $validActions), 'data' => null], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    if ($action === 'reprocess') {
        $db->connection->beginTransaction();
        try {
            $db->ejecutar('actualizarValidacionAdmin', [
                ':id_producto' => $productId,
                ':validation_status' => 'pending_review',
                ':is_active' => 'false',
            ]);
            viva_reenqueue_product_validation($productId);
            $db->connection->commit();
        } catch (Throwable $e) {
            if ($db->connection->inTransaction()) {
                $db->connection->rollBack();
            }
            throw $e;
        }

        echo json_encode([
            'exito' => true,
            'mensaje' => 'Validación re-encolada para el producto ' . $productId,
            'data' => ['validation_status' => 'pending_review', 'is_active' => false],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    $newStatus = $action === 'approve' ? 'approved' : 'rejected';
    $isActive = $newStatus === 'approved';

    if ($producerId > 0) {
        $db->ejecutar('ai.fun_admin_approve_product', [
            ':product_id' => $productId,
            ':producer_id' => $producerId,
            ':decision' => $newStatus,
            ':motivo' => $input['motivo'] ?? '',
        ]);
    }

    echo json_encode([
        'exito' => true,
        'mensaje' => 'Producto ' . ($isActive ? 'aprobado' : 'rechazado') . ' exitosamente.',
        'data' => ['validation_status' => $newStatus, 'is_active' => $isActive],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    $resp = ErrorHandler::jsonResponse($e, 'admin_validation_action');
    echo json_encode([
        'exito' => false,
        'mensaje' => $resp['mensaje'] ?? $resp['message'] ?? 'Error en el servidor. Por favor, intente más tarde.',
        'data' => null,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
