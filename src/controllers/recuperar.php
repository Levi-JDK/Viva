<<<<<<< HEAD
<?php
require_once ROOT_PATH . 'src/functions/auth_helper.php';

// Si ya está autenticado, redirigir al inicio
if (AuthHelper::verifyToken()) {
    header('Location: ' . BASE_URL);
    exit;
}

require_once ROOT_PATH . 'src/views/recuperar.view.php';
=======
<?php
require_once ROOT_PATH . 'src/functions/auth_helper.php';

// Si ya está autenticado, redirigir al inicio
if (AuthHelper::verifyToken()) {
    header('Location: ' . BASE_URL);
    exit;
}

require_once ROOT_PATH . 'src/views/recuperar.view.php';
>>>>>>> 885c1ade0c1a4a699a76f6bb4e4c545b4617c99d
