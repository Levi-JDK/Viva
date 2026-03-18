<?php
// src/controllers/stand_detail.php
// Página de detalle de stand individual — muestra información completa de un stand específico



require_once __DIR__ . '/../functions/database.php';

try {


    $db = Database::getInstance();

    // Obtener el ID del stand desde el parámetro de la URL
    // Formato de URL: /stand?id=id_stand
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        // Redirigir a la página de prueba o mostrar error
        header('Location: ' . BASE_URL . 'test-stands');
        exit;
    }

    $id_stand = (int)$_GET['id'];
    
    // Obtener información del stand
    $stmt = $db->ejecutar('obtenerStand', [':id_s' => $id_stand]);
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
        $productos_stand = array_filter($productosRaw, function($p) use ($stand) {
            return $p['id_productor'] == $stand['id_productor'];
        });

        // Obtener promedio del stand
        $stmtPromedioStand = $db->ejecutar('obtenerPromedioEstrellasStand', [':id_stand' => $id_stand]);
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
