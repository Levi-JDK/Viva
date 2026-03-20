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
    
    try {
        $db = Database::getInstance();

        // --- Acciones especiales del dashboard admin ---
        if ($accion === 'list_users') {
            $stmt = $db->ejecutar('listarUsuariosAdmin');
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $usuarios]);
            exit;
        }

        if ($accion === 'list_products') {
            $stmt = $db->ejecutar('listarProductosAdmin');
            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $productos]);
            exit;
        }

        if ($accion === 'toggle_user') {
            $id_user = (int)($_REQUEST['id_user'] ?? 0);
            $is_active = $_REQUEST['is_active'] ?? 'true';
            if (!$id_user) {
                echo json_encode(['success' => false, 'message' => 'ID de usuario requerido.']);
                exit;
            }
            // is_active=true → is_deleted=false (activar). is_active=false → is_deleted=true (desactivar)
            $db->ejecutar('toggleUsuarioActivo', [
                ':id_user' => $id_user,
                ':is_deleted' => ($is_active === 'false')
            ]);
            echo json_encode(['success' => true, 'message' => 'Estado del usuario actualizado.']);
            exit;
        }

        if ($accion === 'toggle_product') {
            $id_producto = (int)($_REQUEST['id_producto'] ?? 0);
            $is_active = $_REQUEST['is_active'] ?? 'true';
            if (!$id_producto) {
                echo json_encode(['success' => false, 'message' => 'ID de producto requerido.']);
                exit;
            }
            // is_active=false → archivar (is_deleted=TRUE), is_active=true → restaurar
            $db->ejecutar('toggleProductoActivo', [
                ':id_producto' => $id_producto,
                ':is_deleted' => ($is_active === 'false')
            ]);
            echo json_encode(['success' => true, 'message' => 'Estado del producto actualizado.']);
            exit;
        }

        if ($accion === 'list_user_menus') {
            $id_user = (int)($_REQUEST['id_user'] ?? 0);
            if (!$id_user) { echo json_encode(['success' => false, 'message' => 'ID requerido.']); exit; }
            $stmt = $db->ejecutar('listarMenusUsuario', [':id_user' => $id_user]);
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
            exit;
        }

        if ($accion === 'assign_menu') {
            $id_user = (int)($_REQUEST['id_user'] ?? 0);
            $id_menu = (int)($_REQUEST['id_menu'] ?? 0);
            if (!$id_user || !$id_menu) { echo json_encode(['success' => false, 'message' => 'Parámetros requeridos.']); exit; }
            $stmt = $db->ejecutar('asignarMenuUsuario', [':id_user' => $id_user, ':id_menu' => $id_menu]);
            echo json_encode(['success' => true, 'data' => $stmt->fetchColumn()]);
            exit;
        }

        if ($accion === 'revoke_menu') {
            $id_user = (int)($_REQUEST['id_user'] ?? 0);
            $id_menu = (int)($_REQUEST['id_menu'] ?? 0);
            if (!$id_user || !$id_menu) { echo json_encode(['success' => false, 'message' => 'Parámetros requeridos.']); exit; }
            $stmt = $db->ejecutar('revocarMenuUsuario', [':id_user' => $id_user, ':id_menu' => $id_menu]);
            echo json_encode(['success' => true, 'data' => $stmt->fetchColumn()]);
            exit;
        }

        // --- Acciones CRUD genéricas ---
        if (!$accion || !$entidad) {
            echo json_encode(['success' => false, 'message' => 'Parámetros obligatorios faltantes.']);
            exit;
        }

        // Clonamos REQUEST excluyendo accion/entidad para obtener el DTO puro
        $datos = $_REQUEST;
        unset($datos['accion'], $datos['entidad']);

        $resultado = $db->gestionarCRUDAdmin($accion, $entidad, $datos);
        echo json_encode(['success' => true, 'data' => $resultado]);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
}
?>

