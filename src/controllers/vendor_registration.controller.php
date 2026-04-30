<?php

require_once __DIR__ . '/../functions/auth_helper.php';
require_once __DIR__ . '/../services/VendorService.php';

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido.',
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$userData = AuthHelper::protectRoute();

echo json_encode(
    VendorService::registrarVendedor((int) $userData->id_user, $_POST),
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
);
exit;
