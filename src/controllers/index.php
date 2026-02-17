<?php 
// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(0, '/');
    session_start();
}

// Inicializar variables por defecto
$is_logged_in = false;
$nombre_usuario = '';
$apellido_usuario = '';
$nombre_completo = '';
$email_usuario = '';
$foto_usuario = 'images/default.jpg';

// Cargar dependencias necesarias (siempre, no solo si hay usuario logueado)
require_once ROOT_PATH . 'vendor/autoload.php';
require_once ROOT_PATH . 'src/functions/Database.php';

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(ROOT_PATH);
$dotenv->load();

// Si el usuario está logueado, obtener sus datos
if (isset($_SESSION['id_user'])) {
    try {
        // Usar Singleton pattern
        $db = Database::getInstance();
        
        // Obtener datos del usuario usando consulta preparada
        $id_usuario = $_SESSION['id_user'];
        $stmt = $db->ejecutar('obtenerUsuarioPorId', [':id' => $id_usuario]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario) {
            $is_logged_in = true;
            $nombre_usuario = $usuario['nom_user'] ?? 'Usuario';
            $apellido_usuario = $usuario['ape_user'] ?? '';
            $nombre_completo = $nombre_usuario . ' ' . $apellido_usuario;
            $email_usuario = $usuario['mail_user'] ?? '';
            $foto_usuario = $usuario['foto_user'] ?? 'images/default.jpg';
        }
        // Verificar si es productor
        $stmt_productor = $db->ejecutar('validarProductor', [':id_user' => $id_usuario]);
        $es_productor = $stmt_productor->fetchColumn(); 
    } catch (Exception $e) {
        // Si hay error, simplemente no mostrar datos de usuario
        error_log("Error al obtener datos de usuario en index: " . $e->getMessage());
    }
}

// Get featured stands for the affiliates section (max 3)
$featured_stands = [];
try {
    $db = Database::getInstance();
    $stmt = $db->connection->prepare('SELECT * FROM tab_stand WHERE is_deleted = FALSE ORDER BY RANDOM() LIMIT 3');
    $stmt->execute();
    $featured_stands = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Error al obtener stands destacados: " . $e->getMessage());
}

// Get featured products for the landing page (max 4)
$featured_products = [];
try {
    $db = Database::getInstance();
    $stmt = $db->ejecutar('obtenerProductosDestacados', [':limit' => 4]);
    $featured_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Error al obtener productos destacados: " . $e->getMessage());
}

// Usamos ROOT_PATH para que el include sea absoluto desde el disco
require_once ROOT_PATH . "src/views/index.view.php";
?>