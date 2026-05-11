<?php
require_once __DIR__ . '/../../services/MyProductsService.php';
require_once __DIR__ . '/../../functions/error_handler.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    try {
        echo json_encode(MyProductsService::guardarStand((int) ($id_user ?? 0), $_POST, $_FILES));
    } catch (Exception $e) {
        echo json_encode(
            ErrorHandler::jsonResponse($e, 'mis_productos.stand.guardarStand'),
            JSON_UNESCAPED_UNICODE
        );
    }
    
    exit;
}

$stand = [];

try {
    $stand = MyProductsService::obtenerStand((int) ($id_productor ?? 0));
} catch (Exception $e) {
    ErrorHandler::handle($e, 'mis_productos.stand.obtenerStand');
    throw $e;
}

require_once ROOT_PATH . "src/views/mis_productos/stand.view.php";
