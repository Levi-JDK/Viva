<?php
try {
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_POST['accion'] = 'registro';
    $_POST['nombre'] = 'Test';
    $_POST['apellido'] = 'Test';
    $_POST['email'] = 'testrun@example.com';
    $_POST['contrasena'] = '123456';
    require 'src/functions/auth_controller.php';
} catch (\Throwable $e) {
    file_put_contents('error_clean.txt', $e->getMessage() . "\n" . $e->getTraceAsString() . "\n" . $e->getFile() . ":" . $e->getLine());
}
