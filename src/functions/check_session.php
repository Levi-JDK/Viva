<?php
session_start();
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