<?php
require_once ROOT_PATH . 'src/functions/auth_helper.php';

// Si ya está autenticado, redirigir al inicio
if (AuthHelper::verifyToken()) {
    header('Location: ' . BASE_URL);
    exit;
}

require_once ROOT_PATH . 'src/views/recuperar.view.php';
