<?php
// src/controllers/mis_productos/inventory.php

// La conexión $db, $id_user y $id_productor son provistos por el controlador principal

$productos = [];
if (isset($id_productor) && $id_productor) {
    try {
        $productos_raw = $db->ejecutar('obtenerProductos', [':id_productor' => $id_productor])->fetchAll(PDO::FETCH_ASSOC);
        
        // Procesar imágenes (de JSON a array/string)
        $productos = array_map(function($prod) {
            $imgs = json_decode($prod['imagenes'], true);
            $url_img = 'images/default_product.png';
            if (is_array($imgs) && count($imgs) > 0 && isset($imgs[0]['url'])) {
                $url_img = $imgs[0]['url'];
            }
            // La vista espera 'imagen' como URL principal de la imagen
            $prod['imagen'] = $url_img;
            return $prod;
        }, $productos_raw);
    } catch (Exception $e) {
        $error = "Error al cargar productos: " . $e->getMessage();
    }
}

// Datos para las tarjetas de KPIs
$total_productos = count($productos);
$productos_activos = count(array_filter($productos, fn($p) => $p['activo']));
$vistas_totales = array_sum(array_column($productos, 'vistas'));

require_once ROOT_PATH . "src/views/mis_productos/kpi_cards.view.php";
require_once ROOT_PATH . "src/views/mis_productos/inventory.view.php";
