<?php
require_once __DIR__ . '/../../services/VendorService.php';
require_once __DIR__ . '/../../functions/error_handler.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=UTF-8');

    try {
        echo json_encode(
            VendorService::actualizarConfiguracionVendedor((int) ($id_user ?? 0), $_POST),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    } catch (Exception $e) {
        echo json_encode(
            ErrorHandler::jsonResponse($e, 'mis_productos.configuration.actualizarConfiguracion'),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }

    exit;
}

try {
    $config_data = VendorService::obtenerConfiguracionVendedor((int) ($id_user ?? 0));
} catch (Exception $e) {
    ErrorHandler::handle($e, 'mis_productos.configuration.obtenerConfiguracion');
    throw $e;
}

require_once ROOT_PATH . "src/views/mis_productos/configuration.view.php";
