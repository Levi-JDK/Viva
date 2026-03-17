<?php
// src/controllers/mis_productos/form_add_product.php

// La conexión $db es provista por el controlador principal

try {
    $categorias = $db->ejecutar('obtenerCategorias')->fetchAll(PDO::FETCH_ASSOC);
    $colores = $db->ejecutar('obtenerColores')->fetchAll(PDO::FETCH_ASSOC);
    $oficios = $db->ejecutar('obtenerOficios')->fetchAll(PDO::FETCH_ASSOC);
    $materias = $db->ejecutar('obtenerMaterias')->fetchAll(PDO::FETCH_ASSOC);
    
    // Si viene un ID, estamos en modo Edición
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $id_prod_edit = (int) $_GET['id'];
        $stmt_edit = $db->ejecutar('obtenerProductoPorId', [':id_producto' => $id_prod_edit]);
        $prod_data = $stmt_edit->fetch(PDO::FETCH_ASSOC);
        
        // Validar que el producto exista y pertenezca al productor logueado
        if ($prod_data && $prod_data['id_productor'] == $id_productor) {
            $producto_editar = $prod_data;
        } else {
            // Si intenta editar un producto que no es suyo, devolvemos al inventario
            header('Location: ' . BASE_URL . 'mis_productos');
            exit;
        }
    }
} catch (Exception $e) {
    $error = "Error al cargar datos del formulario: " . $e->getMessage();
}

require_once ROOT_PATH . "src/views/mis_productos/form_add_product.view.php";
