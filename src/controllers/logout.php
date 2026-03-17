<?php
// Logout - Cerrar sesión del usuario usando JWT

require_once __DIR__ . '/../functions/auth_helper.php';

// Destruir la cookie de sesión
AuthHelper::clearAuthCookie();

// Redirigir al inicio
header('Location: ' . BASE_URL);
exit;
?>
