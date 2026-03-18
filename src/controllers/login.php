<?php 
require_once ROOT_PATH . 'src/functions/auth_helper.php';

// Si el usuario ya está autenticado, redirigirlo a donde iba (o a la home)
if (AuthHelper::verifyToken()) {
    // Respetar el parámetro ?redirect= si apunta al mismo host (seguridad)
    $destino = BASE_URL;
    if (!empty($_GET['redirect'])) {
        $redirect = $_GET['redirect'];
        $host_actual = parse_url(BASE_URL, PHP_URL_HOST);
        $host_redirect = parse_url($redirect, PHP_URL_HOST);
        if ($host_redirect === $host_actual) {
            $destino = $redirect;
        }
    }
    if (!headers_sent()) {
        header('Location: ' . $destino);
        exit;
    } else {
        echo "<script>window.location.href = '" . addslashes($destino) . "';</script>";
        exit;
    }
}

// Usamos ROOT_PATH para que el include sea absoluto desde el disco
require_once ROOT_PATH . "src/views/login.view.php";
?>