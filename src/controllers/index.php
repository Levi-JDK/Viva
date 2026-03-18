<?php
require_once ROOT_PATH . 'src/functions/database.php';

// Obtener stands destacados para la sección de afiliados (máx 3)
$featured_stands = [];
try {
    $db = Database::getInstance();
    $stmt = $db->ejecutar('obtenerStandsDestacados', [':limit' => 3]);
    $featured_stands = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Error al obtener stands destacados: " . $e->getMessage());
}

// Obtener productos destacados para el landing (máx 4)
$featured_products = [];
try {
    $stmt = $db->ejecutar('obtenerProductosDestacados', [':limit' => 4]);
    $featured_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Error al obtener productos destacados: " . $e->getMessage());
}

// Obtener categorías con contador de productos
$categorias_destacadas = [];
try {
    $stmt = $db->ejecutar('obtenerFiltrosCategorias');
    $categorias_destacadas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Error al obtener categorías destacadas: " . $e->getMessage());
}

// Obtener textos y parámetros globales desde tab_pmtros
$pmtros = [];
try {
    $pmtros = $db->obtenerConfiguracion();
} catch (Exception $e) {
    error_log("Error al obtener configuracion: " . $e->getMessage());
}

// Usamos ROOT_PATH para que el include sea absoluto desde el disco
require_once ROOT_PATH . "src/views/index.view.php";
?>
