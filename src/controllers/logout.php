<?php
// Logout - Cerrar sesión del usuario
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Destruir la sesión
session_unset();
session_destroy();

// Redirigir al inicio
header('Location: ' . BASE_URL);
exit;
?>
