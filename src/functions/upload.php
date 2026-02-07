<?php
// 1. Detectar el protocolo y host (Mismo logica que index.php)
$protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];

// Ajuste para el folder del proyecto: Como estamos en src/functions, debemos subir 2 niveles
// dirname($_SERVER['SCRIPT_NAME']) devuelve .../viva/src/functions
// Queremos .../viva
$script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$proyecto_folder = str_replace('/src/functions', '', $script_dir); // Quitamos la parte de src/functions
$proyecto_folder = rtrim($proyecto_folder, '/');

if (!defined('BASE_URL')) {
    define('BASE_URL', $protocolo . "://" . $host . $proyecto_folder . "/");
}

// Incluir archivos necesarios (estamos en el mismo directorio)
require_once 'profile_upload.php';

// Cargar variables de entorno desde .env usando Composer autoload
require_once __DIR__ . '/../../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['imagen_perfil'])) {
    /**
     * Directorio de destino para las fotos de perfil
     * IMPORTANTE: Usar ruta absoluta desde la raíz del proyecto
     * - Ruta física: C:\www\Apache24\htdocs\vivaServer\images\profiles\
     * - Ruta web: http://localhost:3000/vivaServer/images/profiles/
     */
    $target_directory = __DIR__ . '/../../images/profiles/'; // Dos niveles arriba desde src/functions/
    
    // Llamar a la función de upload pasando el directorio correcto
    $result = handleProfilePictureUpload($_FILES['imagen_perfil'], $target_directory);
    
    if ($result['success']) {
        // VARIABLE: $ruta_imagen_final
        // Esta variable almacena la ruta RELATIVA para guardar en la base de datos
        // Debe ser: images/profiles/foto.webp (NO ruta absoluta de Windows)
        $ruta_imagen_final = $result['path'];

        // ----------------------------------------
        // UPDATE A LA BASE DE DATOS
        // ----------------------------------------
        
        // 1. Iniciar sesión si no está iniciada para obtener el ID del usuario
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Obtener ID del usuario de la sesión (asegurarse de que existe)
        // NOTA: Si tu sistema de login no guarda 'id_user' en $_SESSION, debes ajustarlo.
        // Por defecto asumimos que existe o usamos 1 para pruebas si no hay sesión (CUIDADO EN PRODUCCIÓN)
        $id_usuario = $_SESSION['id_user'] ?? null;

        if ($id_usuario) {
            try {
                // Incluir configuración de base de datos (están en el mismo directorio)
                require_once 'Database.php';
                $config = require 'config.php';
                
                // Cargar variables de entorno - ya cargadas arriba con dotenv
                $db = new Database($config, $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);
                $pdo = $db->connection;

                // 2. Preparar la consulta SQL para actualizar la foto del usuario
                // IMPORTANTE: El orden de parámetros debe coincidir con la definición de la función SQL
                // fun_u_foto_user(p_id_user integer, p_foto_user varchar)
                $sql = "SELECT fun_u_foto_user(:id, :foto) as resultado";
                
                // 3. Ejecutar la sentencia
                $stmt = $pdo->prepare($sql);
                // Bind en el orden correcto: primero id, luego foto
                $stmt->bindParam(':id', $id_usuario, PDO::PARAM_INT);
                $stmt->bindParam(':foto', $ruta_imagen_final, PDO::PARAM_STR);
                $stmt->execute();
                
                // 4. Obtener el resultado de la función
                $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Verificar si la actualización fue exitosa
                if ($resultado && $resultado['resultado'] === true) {
                    // Actualización exitosa
                    error_log("Foto de perfil actualizada correctamente para el usuario ID: " . $id_usuario);
                } else {
                    // La función retornó false (usuario no encontrado u otro error)
                    error_log("Error: La función fun_u_foto_user retornó FALSE para el usuario ID: " . $id_usuario);
                }

            } catch (Exception $e) {
                // Registrar error pero permitir que el usuario continúe (o detener si es crítico)
                error_log("Error al actualizar foto en BD: " . $e->getMessage());
                // Opcional: die("Error db: " . $e->getMessage());
            }
        }

        // Redirigir con éxito al dashboard, agregando un parámetro en la URL para mostrar mensajes
        header("Location: " . BASE_URL . "dashboard#profile?success=photo_updated");
        exit;
    } else {
        // Manejar error (podrías pasarlo por URL también)
        die("Error: " . $result['message']);
    }
} else {
    header("Location: " . BASE_URL . "dashboard");
    exit;
}
?>