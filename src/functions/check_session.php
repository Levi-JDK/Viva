<?php
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(0, '/');
    session_start();
}
header('Content-Type: application/json');

if (isset($_SESSION['email'])) {
    $response = [
        "loggedIn" => true,
        "nombre" => $_SESSION['nombre']
    ];
} else {
    $response = [
        "loggedIn" => false
    ];
}

echo json_encode($response);
?>