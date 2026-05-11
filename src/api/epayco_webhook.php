<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../functions/database.php';
require_once __DIR__ . '/../functions/error_handler.php';
require_once __DIR__ . '/../services/EpaycoService.php';

if (!isset($_ENV['DB_HOST'])) {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2));
    $dotenv->safeLoad();
}

header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.'], JSON_UNESCAPED_UNICODE);
    return;
}

$result = EpaycoService::procesarWebhook($_POST, $_SERVER);
http_response_code((int) $result['status']);
echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
