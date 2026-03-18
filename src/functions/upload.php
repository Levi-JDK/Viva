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
require_once __DIR__ . '/image_uploader.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['imagen_perfil'])) {
    /**
     * Directorio de destino para las fotos de perfil
     * IMPORTANTE: Usar ruta absoluta desde la raíz del proyecto
     * - Ruta física: C:\www\Apache24\htdocs\vivaServer\images\profiles\
     * - Ruta web: http://localhost:3000/vivaServer/images/profiles/
     */
    $target_directory = __DIR__ . '/../../images/profiles/'; // Dos niveles arriba desde src/functions/
    
    // Llamar a la función de upload pasando el directorio correcto y prefijo 'user_'
    $result = handleImageUpload($_FILES['imagen_perfil'], $target_directory, 'user_', 'images/profiles/');
    
    if ($result['success']) {
        // VARIABLE: $ruta_imagen_final
        // Esta variable almacena la ruta RELATIVA para guardar en la base de datos
        // Debe ser: images/profiles/foto.webp (NO ruta absoluta de Windows)
        $ruta_imagen_final = $result['path'];

        // ----------------------------------------
        // UPDATE A LA BASE DE DATOS
        // ----------------------------------------
        


        // Obtener ID del usuario de la sesión validada
        require_once __DIR__ . '/auth_helper.php';
        $userData = AuthHelper::protectRoute();
        $id_usuario = $userData->id_user;

        if ($id_usuario) {
            try {
                // Incluir configuración de base de datos
                require_once 'database.php';
                
                // Usar Singleton pattern
                $db = Database::getInstance();

                // Actualizar foto del usuario usando consulta preparada
                $stmt = $db->ejecutar('actualizarFotoUsuario', [
                    ':id' => $id_usuario,
                    ':foto' => $ruta_imagen_final
                ]);
                
                // Obtener el resultado de la función
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

        // Redirigir con éxito al perfil
        header("Location: " . BASE_URL . "perfil?success=photo_updated#profile");
        exit;
    } else {
        // Manejar error redirigiendo con mensaje
        $errorMsg = urlencode($result['message']);
        header("Location: " . BASE_URL . "perfil?error=" . $errorMsg . "#profile");
        exit;
    }
} else {
    header("Location: " . BASE_URL . "perfil");
    exit;
}
?>
