<?php
require_once __DIR__ . '/../functions/auth_helper.php';
require_once __DIR__ . '/../services/RegisterService.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['ajax']) || isset($_GET['api'])) {
    header('Content-Type: application/json');
    echo json_encode(RegisterService::registrarUsuario($_POST));
    exit;
}

header('Content-Type: text/html; charset=UTF-8');

if (AuthHelper::verifyToken()) {
    $destino = RegisterService::resolverRedirectSeguro($_GET['redirect'] ?? '');

    if (!headers_sent()) {
        header('Location: ' . $destino);
        exit;
    }

    echo "<script>window.location.href = '" . addslashes($destino) . "';</script>";
    exit;
}

require_once __DIR__ . '/../views/registro.view.php';
