<?php
require_once ROOT_PATH . 'src/functions/auth_helper.php';

if (in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'], true)) {
    AuthHelper::clearAuthCookie();

    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION = [];
        session_destroy();
    }

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        echo json_encode(['mensaje' => 'Sesión cerrada.', 'clase' => 'mensaje-exito']);
    } else {
        header('Location: ' . BASE_URL . 'login');
    }
    exit;
}

// Solo rechazar métodos no soportados
header('Content-Type: application/json');
echo json_encode(["mensaje" => "Método no permitido.", "clase" => "mensaje-error"]);
?>
