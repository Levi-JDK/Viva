<?php
// src/controllers/stand_detail.php
// Página de detalle de stand individual — muestra información completa de un stand específico

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../functions/Database.php';

try {
    // Inicializar variables de usuario para el header
    $is_logged_in = false;
    $nombre_usuario = '';
    $foto_usuario = 'images/default.jpg';
    $es_productor = false;

    $db = Database::getInstance();

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
    // Obtener el ID del stand desde el parámetro de la URL
    // Formato de URL: /stand?id=id_productor
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        // Redirigir a la página de prueba o mostrar error
        header('Location: ' . BASE_URL . 'test-stands');
        exit;
    }

    $id_productor = (int)$_GET['id'];
    
    $db = Database::getInstance();
    
    // Obtener información del stand
    $stmt = $db->ejecutar('obtenerStand', [':id_p' => $id_productor]);
    $stand = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Si el stand no existe, redirigir
    if (!$stand) {
        header('Location: ' . BASE_URL . 'test-stands');
        exit;
    }
    
    // Obtener productos de este stand
    $productos_stand = [];
    $promedio_estrellas_stand = 0;
    $total_resenas_stand = 0;

    try {
        $productosRaw = $db->ejecutar('obtenerProductosCatalogo', []);
        $productosRaw = $productosRaw->fetchAll(PDO::FETCH_ASSOC);
        
        // Filtrar productos del catalogo que sean de este stand (productor)
        $productos_stand = array_filter($productosRaw, function($p) use ($id_productor) {
            return $p['id_productor'] == $id_productor;
        });

        // Obtener promedio del stand
        $stmtPromedioStand = $db->ejecutar('obtenerPromedioEstrellasStand', [':id_productor' => $id_productor]);
        $promedioStandRow = $stmtPromedioStand->fetch(PDO::FETCH_ASSOC);
        
        if ($promedioStandRow) {
            $promedio_estrellas_stand = round((float)$promedioStandRow['promedio'], 1);
            $total_resenas_stand = (int)$promedioStandRow['total_resenas'];
        }
    } catch (Exception $e) {
        error_log("Error obteniendo datos dinamicos del stand: " . $e->getMessage());
    }
    
    // Cargar la vista
    require_once ROOT_PATH . 'src/views/stand_detail.view.php';
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit;
}
