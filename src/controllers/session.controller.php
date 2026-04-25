<?php
require_once ROOT_PATH . 'src/functions/auth_helper.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $userData = AuthHelper::verifyToken();

    if ($userData) {
        echo json_encode([
            'loggedIn' => true,
            'nombre' => $userData->nombre ?? '',
            'email' => $userData->email ?? ''
        ]);
    } else {
        echo json_encode(['loggedIn' => false]);
    }
    exit;
}

// Si entra por POST
echo json_encode(["mensaje" => "Método no permitido.", "clase" => "mensaje-error"]);
?>
