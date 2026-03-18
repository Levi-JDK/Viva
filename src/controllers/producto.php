<?php
// Controlador para el detalle de producto


require_once(__DIR__ . '/../functions/database.php');

$db = Database::getInstance();




// Obtener ID del producto
$id_producto = isset($_GET['id']) && is_numeric($_GET['id']) ? $_GET['id'] : null;

$producto = null;
$error_message = null;
$resenas = [];
$promedio_estrellas = 0;
$total_resenas = 0;
$productos_relacionados = [];

if ($id_producto) {
    try {
        $stmt = $db->ejecutar('obtenerDetalleProducto', [':id_producto' => $id_producto]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($producto) {
             // Decodificar imágenes
             $producto['imagenes'] = json_decode($producto['imagenes'], true) ?? [];
             // Asegurar que haya al menos una imagen (la principal si no hay array)
             $producto['imagen_principal'] = !empty($producto['imagenes']) && isset($producto['imagenes'][0]['url']) 
                ? $producto['imagenes'][0]['url'] 
                : 'images/default_product.png';
            // Incrementar contador de vistas silenciosamente
            try {
                $db->ejecutar('incrementarVistasProducto', [':id_producto' => $id_producto]);
            } catch (Exception $e) {
                // No interrumpir la carga por error en vistas
                error_log("Error incrementando vistas: " . $e->getMessage());
            }

            // --- INICIO: Carga de Reseñas y Relacionados ---
            try {
                // 1. Obtener Reseñas
                $stmtResenas = $db->ejecutar('obtenerResenasProducto', [':id_producto' => $id_producto]);
                $resenas = $stmtResenas->fetchAll(PDO::FETCH_ASSOC);

                // 2. Obtener Promedio de Estrellas
                $stmtPromedio = $db->ejecutar('obtenerPromedioEstrellasProducto', [':id_producto' => $id_producto]);
                $promedioRow = $stmtPromedio->fetch(PDO::FETCH_ASSOC);
                if ($promedioRow) {
                    $promedio_estrellas = round((float)$promedioRow['promedio'], 1);
                    $total_resenas = (int)$promedioRow['total_resenas'];
                }

                // 3. Obtener Productos Relacionados (Mis categoria u oficio)
                // Usaremos obtenerProductosCatalogoFiltrado con límite manual, excluyendo el actual
                $relacionadosResult = $db->obtenerProductosCatalogoFiltrado([
                    'categoria' => $producto['id_categoria']
                ]);
                
                // Filtramos el mismo producto y limitamos a 4
                $productos_relacionados = array_slice(array_filter($relacionadosResult, function($p) use ($id_producto) {
                    return $p['id_producto'] != $id_producto;
                }), 0, 4);

            } catch (Exception $ex) {
                error_log("Error cargando extras del producto: " . $ex->getMessage());
            }
            // --- FIN: Carga de extras ---

        } else {
            $error_message = "Producto no encontrado.";
        }
    } catch (Exception $e) {
        $error_message = "Error al cargar el producto.";
        error_log($e->getMessage());
    }
} else {
    $error_message = "Producto no válido.";
}

// Si hay error o no existe, podríamos redirigir al catálogo o mostrar error en la vista
if (!$producto && !$error_message) {
     $error_message = "Producto no encontrado.";
}

require_once ROOT_PATH . "src/views/producto.view.php";
