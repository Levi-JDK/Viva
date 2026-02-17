<?php
// Controlador para la vista de catálogo
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(0, '/');
    session_start();
}

require_once(__DIR__ . '/../functions/Database.php');

$db = Database::getInstance();

// Inicializar variables de usuario para el header
$is_logged_in = false;
$nombre_usuario = '';
$foto_usuario = 'images/default.jpg';
$es_productor = false;

if (isset($_SESSION['id_user'])) {
    $id_user = $_SESSION['id_user'];
    try {
        $stmtUser = $db->ejecutar('obtenerUsuarioPorId', [':id' => $id_user]);
        $usuario = $stmtUser->fetch(PDO::FETCH_ASSOC);
        if ($usuario) {
            $is_logged_in = true;
            $nombre_usuario = $usuario['nom_user'];
            $foto_usuario = !empty($usuario['foto_user']) ? $usuario['foto_user'] : 'images/default.jpg';
        }
        $stmtProductor = $db->ejecutar('validarProductor', [':id_user' => $id_user]);
        $es_productor = $stmtProductor->fetchColumn();
    } catch (Exception $e) {
        error_log("Error cargando usuario: " . $e->getMessage());
    }
}

// Capturar filtros de la URL
$search = isset($_GET['q']) && !empty($_GET['q']) ? $_GET['q'] : null;
$categoria = isset($_GET['cat']) && !empty($_GET['cat']) ? $_GET['cat'] : null;
$min_precio = isset($_GET['min_price']) && is_numeric($_GET['min_price']) ? $_GET['min_price'] : null;
$max_precio = isset($_GET['max_price']) && is_numeric($_GET['max_price']) ? $_GET['max_price'] : null;
// Obtener productos
try {
    // For now, get all products - filtering can be added later
    $stmt = $db->ejecutar('obtenerProductosCatalogo', []);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener categorías para el sidebar (placeholder for future)
    $categorias_list = [];
    
} catch (Exception $e) {
    $error_message = "Error al cargar productos: " . $e->getMessage();
    error_log($error_message);
    $productos = [];
    $categorias_list = [];
}

// Cargar vista
require_once ROOT_PATH . "src/views/catalogo.view.php";
