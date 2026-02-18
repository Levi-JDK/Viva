<?php
// Controlador para el detalle de producto
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(0, '/');
    session_start();
}

require_once(__DIR__ . '/../functions/Database.php');

$db = Database::getInstance();

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

// Obtener ID del producto
$id_producto = isset($_GET['id']) && is_numeric($_GET['id']) ? $_GET['id'] : null;

$producto = null;
$error_message = null;

if ($id_producto) {
    try {
        $stmt = $db->ejecutar('obtenerDetalleProducto', [':id_producto' => $id_producto]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($producto) {
             // Decodificar imágenes
             $producto['imagenes'] = json_decode($producto['imagenes'], true) ?? [];
             // Asegurar que haya al menos una imagen (la principal si no hay array)
             $producto['imagen_principal'] = !empty($producto['imagenes']) && isset($producto['imagenes'][0]['url']) 
                ? $producto['imagenes'][0]['url'] 
                : 'images/default_product.png';
        } else {
            $error_message = "Producto no encontrado.";
        }
    } catch (Exception $e) {
        $error_message = "Error al cargar el producto.";
        error_log($e->getMessage());
    }
} else {
    $error_message = "Producto no válido.";
}

// Si hay error o no existe, podríamos redirigir al catálogo o mostrar error en la vista
if (!$producto && !$error_message) {
     $error_message = "Producto no encontrado.";
}

require_once ROOT_PATH . "src/views/producto.view.php";
