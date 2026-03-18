<?php
/**
 * Manejador de Borrado Lógico de Productos
 */

require_once __DIR__ . '/database.php';

// Detectar BASE_URL
$protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$proyecto_folder = str_replace('/src/functions', '', $script_dir);
$proyecto_folder = rtrim($proyecto_folder, '/');
if (!defined('BASE_URL')) {
    define('BASE_URL', $protocolo . "://" . $host . $proyecto_folder . "/");
}

header('Content-Type: application/json');

require_once __DIR__ . '/auth_helper.php';
$userData = AuthHelper::protectRoute();
$id_user = $userData->id_user;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = Database::getInstance();
        $conn = $db->connection;

        // Leer datos JSON del cuerpo de la petición
        $data = json_decode(file_get_contents('php://input'), true);
        $id_producto = $data['id_producto'] ?? null;

        if (empty($id_producto)) {
            throw new Exception("ID de producto no proporcionado.");
        }

        // Obtener id_productor actual
        $stmtProd = $db->ejecutar('obtenerIdProductor', [':id_user' => $id_user]);
        $id_productor = $stmtProd->fetchColumn();

        if (!$id_productor) {
            throw new Exception("No eres un productor válido.");
        }

        $conn->beginTransaction();

        // Eliminar lógicamente el producto (Soft Delete) verificando que el productor sea el dueño
        // Al marcar is_deleted = TRUE, también marcamos is_active = FALSE y stock_productor = 0 por seguridad y restricciones
        $stmt = $db->ejecutar('eliminarProductoLogicamente', [
            ':id_producto'  => $id_producto,
            ':id_productor' => $id_productor
        ]);
        
        if ($stmt->rowCount() === 0) {
            throw new Exception("El producto no existe o no tienes permisos para eliminarlo.");
        }

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Producto eliminado correctamente.']);

    } catch (Exception $e) {
        if (isset($conn)) {
            $conn->rollBack();
        }
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
