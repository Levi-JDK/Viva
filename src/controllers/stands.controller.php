<?php
// src/controllers/stands.php
// Página principal del directorio de artesanos (Stands)



require_once __DIR__ . '/../services/StandsListService.php';
require_once __DIR__ . '/../functions/error_handler.php';

try {
    extract(StandsListService::obtenerContexto($_GET));

    require_once ROOT_PATH . 'src/views/stands.view.php';
} catch (Exception $e) {
    ErrorHandler::handle($e, 'stands.obtenerContexto');
    throw $e;
}
?>
