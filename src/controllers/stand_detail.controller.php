<?php
// src/controllers/stand_detail.php
// Página de detalle de stand individual — muestra información completa de un stand específico



require_once __DIR__ . '/../services/StandDetailService.php';
require_once __DIR__ . '/../functions/error_handler.php';

try {
    $id_stand = isset($_GET['id']) && $_GET['id'] !== '' ? (int) $_GET['id'] : null;
    extract(StandDetailService::obtenerContexto($id_stand));

    if (!empty($redirect)) {
        header('Location: ' . $redirect);
        exit;
    }

    require_once ROOT_PATH . 'src/views/stand_detail.view.php';
} catch (Exception $e) {
    ErrorHandler::handle($e, 'stand_detail.obtenerContexto');
    throw $e;
}
