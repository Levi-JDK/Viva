<?php
/**
 * Manejador de Actualización de Productos
 */

require_once __DIR__ . '/image_uploader.php';
require_once __DIR__ . '/database.php';

// Detectar BASE_URL
$protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$proyecto_folder = str_replace('/src/functions', '', $script_dir);
$proyecto_folder = rtrim($proyecto_folder, '/');
if (!defined('BASE_URL')) {
    define('BASE_URL', $protocolo . "://" . $host . $proyecto_folder . "/");
}

header('Content-Type: application/json');

require_once __DIR__ . '/auth_helper.php';
$userData = AuthHelper::protectRoute();
$id_user = $userData->id_user;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = Database::getInstance();
        $conn = $db->connection;

        $id_producto  = $_POST['id_producto'] ?? null;
        $nom_producto = $_POST['nom_producto'] ?? '';
        $precio       = $_POST['precio_producto'] ?? 0;
        $stock        = $_POST['stock_productor'] ?? 0;
        $id_categoria = $_POST['id_categoria'] ?? null;
        $id_color     = $_POST['id_color'] ?? null;
        $id_oficio    = $_POST['id_oficio'] ?? null;
        $id_materia   = $_POST['id_materia'] ?? null;
        $desc         = $_POST['desc_prod_personal'] ?? '';

        if (empty($id_producto) || empty($nom_producto) || empty($id_categoria) || empty($id_color)) {
            throw new Exception("Faltan campos obligatorios.");
        }

        if (!is_numeric($precio) || $precio < 1) {
            throw new Exception("El precio debe ser un número válido mayor a 0.");
        }
        if (!is_numeric($stock) || $stock < 0) {
            throw new Exception("El stock debe ser un número válido mayor o igual a 0.");
        }

        if (!preg_match('/^[a-zA-Z0-9\s\.\,\-\_áéíóúÁÉÍÓÚñÑüÜ]+$/u', $nom_producto)) {
            throw new Exception("El nombre del producto contiene caracteres no permitidos.");
        }

        if (!empty($desc) && strlen($desc) > 5000) {
            throw new Exception("La descripción es demasiado larga (máx 5000 caracteres).");
        }

        $nom_producto = strip_tags($nom_producto);
        $desc = strip_tags($desc);

        // Validar propiedad del producto
        $stmtProd = $db->ejecutar('obtenerIdProductor', [':id_user' => $id_user]);
        $id_productor = $stmtProd->fetchColumn();

        if (!$id_productor) {
            throw new Exception("No se encontró el perfil de productor.");
        }

        // Verificar que el producto sea de este productor
        $checkStmt = $conn->prepare("SELECT id_producto FROM tab_productos WHERE id_producto = ? AND id_productor = ?");
        $checkStmt->execute([$id_producto, $id_productor]);
        if (!$checkStmt->fetchColumn()) {
            throw new Exception("No tienes permiso para editar este producto.");
        }

        // --- Validación Previa de Nuevas Imágenes ---
        $files_to_process = [];
        if (isset($_FILES['imagen_producto'])) {
            $files = $_FILES['imagen_producto'];
            $count = is_array($files['name']) ? count($files['name']) : 1;

            if (!is_array($files['name'])) {
                $files = ['name' => [$files['name']], 'type' => [$files['type']], 'tmp_name' => [$files['tmp_name']], 'error' => [$files['error']], 'size' => [$files['size']]];
            }

            for ($i = 0; $i < $count; $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_NO_FILE) continue; 
                
                if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                    throw new Exception("Error al subir imagen: " . $files['error'][$i]);
                }

                $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
                if (!in_array($ext, ['jpg', 'jpeg', 'webp', 'png'])) {
                    throw new Exception("Formato inválido: " . $files['name'][$i]);
                }

                if ($files['size'][$i] > 5 * 1024 * 1024) {
                    throw new Exception("Excede el límite de 5MB: " . $files['name'][$i]);
                }

                $files_to_process[] = [
                    'name' => $files['name'][$i], 'type' => $files['type'][$i], 
                    'tmp_name' => $files['tmp_name'][$i], 'error' => $files['error'][$i], 'size' => $files['size'][$i]
                ];
            }
        }

        // Obtener imágenes actuales de la BD para la validación final
        $stmtImg = $conn->prepare("SELECT id_imagen, url_imagen FROM tab_imagenes WHERE id_producto = ?");
        $stmtImg->execute([$id_producto]);
        $imagenes_actuales_db = $stmtImg->fetchAll(PDO::FETCH_ASSOC);
        $urls_actuales_db = array_column($imagenes_actuales_db, 'url_imagen');

        $imagenes_mantenidas_json = $_POST['imagenes_existentes'] ?? '[]';
        $imagenes_mantenidas = json_decode($imagenes_mantenidas_json, true) ?: [];
        $urls_mantenidas = array_column($imagenes_mantenidas, 'url');

        // Validación: debe quedar al menos UNA imagen (ya sea persistente o nueva)
        $total_final_images = count($urls_mantenidas) + count($files_to_process);
        if ($total_final_images === 0) {
            throw new Exception("El producto debe tener al menos una imagen.");
        }

        $conn->beginTransaction();

        // 1. Actualizar producto usando PostgreSQL function call manually as there is no prepared statement yet
        $stmtUpdate = $conn->prepare("SELECT fun_u_producto(
            ?, ?, ?, ?, ?, ?, ?
        )");
        
        $stmtUpdate->execute([
            $id_producto,
            $nom_producto,
            $stock,
            $id_categoria,
            $id_color,
            $id_oficio,
            $id_materia
        ]);

        $result_update = $stmtUpdate->fetchColumn();

        if (!$result_update) {
            throw new Exception("Error al actualizar el producto en la base de datos.");
        }

        // Actualizar descripcion y precio directamente (fun_u_producto no las incluye)
        $stmtDesc = $conn->prepare("UPDATE tab_productos SET descripcion_producto = ?, precio_producto = ? WHERE id_producto = ?");
        $stmtDesc->execute([$desc, $precio, $id_producto]);

        // 2. Gestionar imágenes si se proporcionaron o se eliminaron algunas
        // A. Procesar imágenes existentes eliminadas
        $imagenes_mantenidas_json = $_POST['imagenes_existentes'] ?? '[]';
        $imagenes_mantenidas = json_decode($imagenes_mantenidas_json, true) ?: [];
        $urls_mantenidas = array_column($imagenes_mantenidas, 'url');

        // Obtener imágenes actuales de la BD
        $stmtImg = $conn->prepare("SELECT id_imagen, url_imagen FROM tab_imagenes WHERE id_producto = ?");
        $stmtImg->execute([$id_producto]);
        $imagenes_actuales = $stmtImg->fetchAll(PDO::FETCH_ASSOC);

        $imagenes_a_borrar = [];
        foreach ($imagenes_actuales as $img_actual) {
            if (!in_array($img_actual['url_imagen'], $urls_mantenidas)) {
                $imagenes_a_borrar[] = $img_actual;
            }
        }

        // Borrar archivos físicos y registros de DB para las eliminadas
        foreach ($imagenes_a_borrar as $img_borrar) {
            $ruta_fisica = __DIR__ . '/../../' . $img_borrar['url_imagen'];
            if (file_exists($ruta_fisica)) {
                unlink($ruta_fisica);
            }
            $delStmt = $conn->prepare("DELETE FROM tab_imagenes WHERE id_imagen = ?");
            $delStmt->execute([$img_borrar['id_imagen']]);
        }

        // B. Subir nuevas imágenes físicas (validadas previamente)
        $uploaded_paths = [];
        $target_directory = __DIR__ . '/../../images/products/';

        foreach ($files_to_process as $current_file) {
            $result = handleImageUpload($current_file, $target_directory, 'prod_' . $id_producto . '_' . time() . '_', 'images/products/');
            
            if ($result['success']) {
                $uploaded_paths[] = $result['path'];
            } else {
                throw new Exception("Error al procesar imagen " . $current_file['name'] . ": " . $result['message']);
            }
        }

        foreach ($uploaded_paths as $path) {
            $db->ejecutar('registrarImagen', [
                ':id_producto' => $id_producto,
                ':url_imagen'  => $path
            ]);
        }

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Producto actualizado exitosamente.']);

    }
    catch (Throwable $e) {
        if (isset($conn) && $conn->inTransaction()) {
            $conn->rollBack();
        }
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
