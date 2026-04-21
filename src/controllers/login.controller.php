<?php 
require_once ROOT_PATH . 'src/functions/auth_helper.php';
require_once ROOT_PATH . 'src/services/LoginService.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo json_encode(LoginService::autenticarUsuario($_POST));
    exit;
}

header('Content-Type: text/html; charset=UTF-8');

if (AuthHelper::verifyToken()) {
    $destino = LoginService::resolverRedirectSeguro($_GET['redirect'] ?? '');

    if (!headers_sent()) {
        header('Location: ' . $destino);
        exit;
    } else {
        echo "<script>window.location.href = '" . addslashes($destino) . "';</script>";
        exit;
    }
}

require_once ROOT_PATH . "src/views/login.view.php";
?>
