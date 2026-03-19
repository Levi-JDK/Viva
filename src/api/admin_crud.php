<?php
require_once __DIR__ . '/../functions/auth_helper.php';
require_once __DIR__ . '/../functions/database.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');// Mapeo seguro mediante middleware nativo 
AuthHelper::checkAccess(8); // El ID de menú del admin dashboard debe ser 8

if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') {
    $accion = $_REQUEST['accion'] ?? '';
    $entidad = $_REQUEST['entidad'] ?? '';
    
    // Clonamos REQUEST excluyendo accion/entidad para obtener el DTO puro
    $datos = $_REQUEST;
    unset($datos['accion'], $datos['entidad']);
    
    if (!$accion || !$entidad) {
        echo json_encode(['success' => false, 'message' => 'Parámetros obligatorios faltantes.']);
        exit;
    }
    
    try {
        $db = Database::getInstance();
        $resultado = $db->gestionarCRUDAdmin($accion, $entidad, $datos);
        
        echo json_encode(['success' => true, 'data' => $resultado]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
}
?>
