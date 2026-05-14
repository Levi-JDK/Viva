<?php

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR);
}

require_once ROOT_PATH . 'src/functions/auth_helper.php';
require_once ROOT_PATH . 'src/functions/database.php';

header('Content-Type: application/json; charset=utf-8');

try {
    if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'], true)) {
        http_response_code(405);
        echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido', 'data' => null], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    AuthHelper::protectRoute();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $body = json_decode(file_get_contents('php://input') ?: '{}', true);
        $productId = is_array($body) ? (int) ($body['product_id'] ?? 0) : 0;
    } else {
        $productId = (int) ($_GET['product_id'] ?? 0);
    }

    if ($productId <= 0) {
        http_response_code(400);
        echo json_encode(['exito' => false, 'mensaje' => 'product_id es requerido', 'data' => null], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    $stmt = Database::getInstance()->ejecutar('ai.fun_val_latest_validation_result', [':product_id' => $productId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

    echo json_encode([
        'exito' => true,
        'mensaje' => $result === null ? 'Sin resultados de validación' : 'Resultado encontrado',
        'data' => $result,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'Error interno: ' . $e->getMessage(), 'data' => null], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
