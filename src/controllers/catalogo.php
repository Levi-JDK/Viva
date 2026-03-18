<?php
// Controlador para la vista de catálogo


require_once(__DIR__ . '/../functions/database.php');

$db = Database::getInstance();




// Capturar filtros iniciales de la URL para la primera carga
$filtros = [
    'search' => isset($_GET['q']) ? $_GET['q'] : null,
    'categoria' => isset($_GET['cat']) ? $_GET['cat'] : null,
    'oficio' => isset($_GET['oficio']) ? $_GET['oficio'] : null,
    'materia' => isset($_GET['materia']) ? $_GET['materia'] : null,
    'min_price' => isset($_GET['min_price']) ? $_GET['min_price'] : null,
    'max_price' => isset($_GET['max_price']) ? $_GET['max_price'] : null,
];

try {
    // 1. Obtener listas para los filtros laterales
    $stmtCats = $db->ejecutar('obtenerFiltrosCategorias');
    $categorias_list = $stmtCats->fetchAll(PDO::FETCH_ASSOC);

    $stmtOficios = $db->ejecutar('obtenerFiltrosOficios');
    $oficios_list = $stmtOficios->fetchAll(PDO::FETCH_ASSOC);

    $stmtMaterias = $db->ejecutar('obtenerFiltrosMaterias');
    $materias_list = $stmtMaterias->fetchAll(PDO::FETCH_ASSOC);

    // 2. Obtener productos iniciales
    $productos = $db->obtenerProductosCatalogoFiltrado($filtros);
    
} catch (Exception $e) {
    $error_message = "Error al cargar información del catálogo: " . $e->getMessage();
    error_log($error_message);
    $productos = [];
    $categorias_list = [];
    $oficios_list = [];
    $materias_list = [];
}

// Cargar vista
require_once ROOT_PATH . "src/views/catalogo.view.php";
