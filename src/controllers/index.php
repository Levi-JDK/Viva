<?php 
// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inicializar variables por defecto
$is_logged_in = false;
$nombre_usuario = '';
$apellido_usuario = '';
$nombre_completo = '';
$email_usuario = '';
$foto_usuario = 'images/default.jpg';

// Si el usuario está logueado, obtener sus datos
if (isset($_SESSION['id_user'])) {
    try {
        // Cargar las dependencias necesarias
        require_once ROOT_PATH . 'vendor/autoload.php';
        require_once ROOT_PATH . 'src/functions/Database.php';
        require_once ROOT_PATH . 'src/functions/config.php';
        
        // Cargar variables de entorno
        $dotenv = Dotenv\Dotenv::createImmutable(ROOT_PATH);
        $dotenv->load();
        
        // Conectar a la base de datos
        $config = require ROOT_PATH . 'src/functions/config.php';
        $db = new Database($config, $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);
        $pdo = $db->connection;
        
        // Obtener datos del usuario
        $id_usuario = $_SESSION['id_user'];
        $stmt = $pdo->prepare("SELECT nom_user, ape_user, mail_user, foto_user FROM tab_users WHERE id_user = :id");
        $stmt->execute([':id' => $id_usuario]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario) {
            $is_logged_in = true;
            $nombre_usuario = $usuario['nom_user'] ?? 'Usuario';
            $apellido_usuario = $usuario['ape_user'] ?? '';
            $nombre_completo = $nombre_usuario . ' ' . $apellido_usuario;
            $email_usuario = $usuario['mail_user'] ?? '';
            $foto_usuario = $usuario['foto_user'] ?? 'images/default.jpg';
        }
    } catch (Exception $e) {
        // Si hay error, simplemente no mostrar datos de usuario
        error_log("Error al obtener datos de usuario en index: " . $e->getMessage());
    }
}

// Usamos ROOT_PATH para que el include sea absoluto desde el disco
require_once ROOT_PATH . "src/views/index.view.php";
?>