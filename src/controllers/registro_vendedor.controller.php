<?php

require_once __DIR__ . '/../functions/auth_helper.php';
require_once __DIR__ . '/../functions/error_handler.php';
require_once __DIR__ . '/../services/VendorService.php';

$userData = AuthHelper::protectRoute();
AuthHelper::checkAccess(9);
$id_user = $userData->id_user;

try {
    $registro = VendorService::obtenerDatosRegistro((int) $id_user);
    $es_productor = $registro['es_productor'];

    if ($es_productor) {
        header('Location: ' . BASE_URL . 'mis_productos');
        exit();
    }

    $tipos_doc = $registro['tipos_doc'];
    $departamentos = $registro['departamentos'];
    $grupos = $registro['grupos'];
    $bancos = $registro['bancos'];

} catch (Exception $e) {
    ErrorHandler::handle($e, 'registro_vendedor.obtenerDatosRegistro');
    throw $e;
}

require_once ROOT_PATH . "src/views/registro_vendedor.view.php";
?>
