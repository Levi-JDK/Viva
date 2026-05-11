<?php
require_once __DIR__ . '/../functions/auth_helper.php';
require_once __DIR__ . '/../services/RecoveryService.php';

if (AuthHelper::verifyToken()) {
    header('Location: ' . BASE_URL);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['ajax']) || isset($_GET['api'])) {
    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido']);
        exit;
    }
    try {
        echo json_encode(RecoveryService::procesarSolicitud($_POST));
    } catch (\Throwable $e) {
        error_log('[Recuperar Controller] Error crítico: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['exito' => false, 'mensaje' => 'Error temporal del sistema.']);
    }

    exit;
}

require_once __DIR__ . '/../views/recuperar.view.php';
