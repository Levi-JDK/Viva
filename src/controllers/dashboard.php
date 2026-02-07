<?php 
// IMPORTANTE: Iniciar sesión ANTES de cualquier verificación
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar que el usuario esté autenticado
// Si hay una sesión antigua sin id_user pero con email, obtener el id_user
if (!isset($_SESSION['id_user']) && isset($_SESSION['email'])) {
    // Sesión antigua, obtener id_user desde el email
    try {
        require_once ROOT_PATH . 'vendor/autoload.php';
        require_once ROOT_PATH . 'src/functions/Database.php';
        require_once ROOT_PATH . 'src/functions/config.php';
        
        $dotenv = Dotenv\Dotenv::createImmutable(ROOT_PATH);
        $dotenv->load();
        
        $config = require ROOT_PATH . 'src/functions/config.php';
        $db = new Database($config, $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);
        $pdo = $db->connection;
        
        $stmt = $pdo->prepare("SELECT id_user FROM tab_users WHERE mail_user = :email");
        $stmt->execute([':email' => $_SESSION['email']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            $_SESSION['id_user'] = $result['id_user'];
        } else {
            // Usuario no encontrado, cerrar sesión
            session_destroy();
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
    } catch (Exception $e) {
        error_log("Error al migrar sesión antigua: " . $e->getMessage());
        session_destroy();
        header('Location: ' . BASE_URL . 'login');
        exit;
    }
}

// Si todavía no hay id_user, redirigir al login
if (!isset($_SESSION['id_user'])) {
    header('Location: ' . BASE_URL . 'login');
    exit;
}

// Cargar las dependencias necesarias
require_once ROOT_PATH . 'vendor/autoload.php';
require_once ROOT_PATH . 'src/functions/Database.php';
require_once ROOT_PATH . 'src/functions/config.php';

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(ROOT_PATH);
$dotenv->load();

// Conectar a la base de datos
try {
    $config = require ROOT_PATH . 'src/functions/config.php';
    $db = new Database($config, $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);
    $pdo = $db->connection;
} catch (Exception $e) {
    die("Error de conexión a base de datos: " . $e->getMessage());
}

// Obtener datos del usuario actual
try {
    $id_usuario = $_SESSION['id_user'];
    $stmt = $pdo->prepare("SELECT nom_user,ape_user, mail_user, foto_user, created_at FROM tab_users WHERE id_user = :id");
    $stmt->execute([':id' => $id_usuario]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Si no se encuentra el usuario, cerrar sesión
    if (!$usuario) {
        session_destroy();
        header('Location: ' . BASE_URL . 'login');
        exit;
    }
} catch (PDOException $e) {
    die("Error al obtener datos del usuario: " . $e->getMessage());
}

// ============================================================================
// PROCESAR DATOS DEL USUARIO PARA LA VISTA
// ============================================================================
$nombre_usuario = $usuario['nom_user'] ?? 'Usuario';
$apellido_usuario = $usuario['ape_user'] ?? '';
$nombre_completo = $nombre_usuario . ' ' . $apellido_usuario;
$email_usuario = $usuario['mail_user'] ?? '';
$foto_usuario = $usuario['foto_user'] ?? 'images/default.jpg';
$fecha_registro = $usuario['created_at'] ?? null;

// Formatear fecha de registro si existe
if ($fecha_registro) {
    $fecha_obj = new DateTime($fecha_registro);
    $fecha_formateada = $fecha_obj->format('F Y'); // Ejemplo: "Febrero 2026"
    // Traducir meses al español
    $meses = [
        'January' => 'Enero', 'February' => 'Febrero', 'March' => 'Marzo',
        'April' => 'Abril', 'May' => 'Mayo', 'June' => 'Junio',
        'July' => 'Julio', 'August' => 'Agosto', 'September' => 'Septiembre',
        'October' => 'Octubre', 'November' => 'Noviembre', 'December' => 'Diciembre'
    ];
    $fecha_formateada = str_replace(array_keys($meses), array_values($meses), $fecha_formateada);
} else {
    $fecha_formateada = 'Fecha desconocida';
}

// Obtener la inicial del nombre para el avatar
$inicial_usuario = strtoupper(substr($nombre_usuario, 0, 1));

// Usamos ROOT_PATH para que el include sea absoluto desde el disco
require_once ROOT_PATH . "src/views/dashboard.view.php";
?>
