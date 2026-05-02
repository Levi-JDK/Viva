<?php
require_once __DIR__ . '/../../services/MyProductsService.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    try {
        echo json_encode(MyProductsService::guardarStand((int) ($id_user ?? 0), $_POST, $_FILES));
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
    
    exit;
}

$stand = [];

try {
    $stand = MyProductsService::obtenerStand((int) ($id_productor ?? 0));
} catch (Exception $e) {
    throw $e;
}

require_once ROOT_PATH . "src/views/mis_productos/stand.view.php";
