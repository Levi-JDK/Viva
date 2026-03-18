<?php
// src/controllers/stands.php
// Página principal del directorio de artesanos (Stands)



require_once __DIR__ . '/../functions/database.php';

try {
    $db = Database::getInstance();
    
    // Obtener parámetros de búsqueda si existen
    $search = isset($_GET['q']) ? trim($_GET['q']) : '';
    
    // Ejecutar la consulta correspondiente usando los statements preparados
    if (!empty($search)) {
        $stmt = $db->ejecutar('buscarStandsActivos', [':search' => '%' . $search . '%']);
    } else {
        $stmt = $db->ejecutar('obtenerStandsActivos');
    }
    
    $stands = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Cargar la vista
    require_once ROOT_PATH . 'src/views/stands.view.php';
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit;
}
?>
