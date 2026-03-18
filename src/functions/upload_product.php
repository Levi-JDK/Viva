<?php
/**
 * Manejador de Subida de Productos
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

// 2. Lógica principal
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = Database::getInstance();
        $conn = $db->connection;

        // --- Validación de campos de entrada ---
        $nom_producto = $_POST['nom_producto'] ?? '';
        $precio = $_POST['precio_producto'] ?? 0;
        $stock = $_POST['stock_productor'] ?? 0;
        $id_categoria = $_POST['id_categoria'] ?? null;
        $id_color = $_POST['id_color'] ?? null;
        $id_oficio = $_POST['id_oficio'] ?? null;
        $id_materia = $_POST['id_materia'] ?? null;
        $desc = $_POST['desc_prod_personal'] ?? '';

        if (empty($nom_producto) || empty($id_categoria) || empty($id_color)) {
            throw new Exception("Faltan campos obligatorios.");
        }

        // --- Validación del lado del servidor ---

        // 1. Validación numérica
        if (!is_numeric($precio) || $precio < 1) {
            throw new Exception("El precio debe ser un número válido mayor a 0.");
        }
        if (!is_numeric($stock) || $stock < 1) {
            throw new Exception("El stock debe ser un número válido mayor a 0.");
        }

        // 2. Sanitización y validación de texto (evitar caracteres especiales)
        // Se permiten letras, números, espacios y puntuación básica: . , - _
        if (!preg_match('/^[a-zA-Z0-9\s\.\,\-\_áéíóúÁÉÍÓÚñÑüÜ]+$/u', $nom_producto)) {
            throw new Exception("El nombre del producto contiene caracteres no permitidos.");
        }

        // Se permite un rango más amplio de caracteres para la descripción, incluyendo saltos de línea
        if (!empty($desc) && strlen($desc) > 5000) {
            throw new Exception("La descripción es demasiado larga (máx 5000 caracteres).");
        }

        // Sanitizar para la BD (aunque el binding de PDO ya lo maneja en su mayoría)
        $nom_producto = strip_tags($nom_producto);
        $desc = strip_tags($desc); // Protección básica contra XSS

        // Obtener id_productor
        $stmtProd = $db->ejecutar('obtenerIdProductor', [':id_user' => $id_user]);
        $id_productor = $stmtProd->fetchColumn();

        if (!$id_productor) {
            throw new Exception("No se encontró el perfil de productor.");
        }

        // --- 3. Validación Inicial de Imágenes ANTES de tocar la BD ---
        // Esto evita que se cree un producto "fantasma" si las imágenes son inválidas.
        
        $files_to_process = [];
        if (isset($_FILES['imagen_producto'])) {
            $files = $_FILES['imagen_producto'];
            $count = is_array($files['name']) ? count($files['name']) : 1;

            if (!is_array($files['name'])) {
                $files = ['name' => [$files['name']], 'type' => [$files['type']], 'tmp_name' => [$files['tmp_name']], 'error' => [$files['error']], 'size' => [$files['size']]];
            }

            for ($i = 0; $i < $count; $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_NO_FILE) continue; // Si algún campo vino vacío
                
                if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                    throw new Exception("Error nativo al subir imagen: " . $files['error'][$i]);
                }

                // Check extension
                $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
                if (!in_array($ext, ['jpg', 'jpeg', 'webp', 'png'])) {
                    throw new Exception("La imagen " . $files['name'][$i] . " tiene un formato no válido.");
                }

                // Check Size (5MB)
                $max_size = 5 * 1024 * 1024;
                if ($files['size'][$i] > $max_size) {
                    throw new Exception("La imagen " . $files['name'][$i] . " excede el tamaño máximo de 5MB.");
                }

                $files_to_process[] = [
                    'name'     => $files['name'][$i],
                    'type'     => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error'    => $files['error'][$i],
                    'size'     => $files['size'][$i]
                ];
            }
        }

        if (empty($files_to_process)) {
            throw new Exception("Debe subir al menos una imagen válida.");
        }

        // --- FIN VALIDACIONES, EJECUTAR INSERCIÓN ---

        $conn->beginTransaction();

        // 1. Insertar producto
        $stmt = $db->ejecutar('registrarProducto', [
            ':id_productor'       => $id_productor,
            ':nom_producto'       => $nom_producto,
            ':stock_productor'    => $stock,
            ':id_categoria'       => $id_categoria,
            ':id_color'           => $id_color,
            ':id_oficio'          => $id_oficio,
            ':id_materia'         => $id_materia,
            ':precio_producto'    => $precio,
            ':descripcion_producto' => $desc,
            ':is_active'          => 'true'
        ]);

        $result_insert = $stmt->fetchColumn();

        if (!$result_insert) {
            throw new Exception("Error al crear el producto en la base de datos.");
        }

        // Recuperar el ID generado
        $stmtId = $conn->query("SELECT MAX(id_producto) FROM tab_productos");
        $id_producto = $stmtId->fetchColumn();

        // 2. Subir imágenes físicas con el ID generado
        $uploaded_paths = [];
        $target_directory = __DIR__ . '/../../images/products/';

        foreach ($files_to_process as $current_file) {
            $result = handleImageUpload($current_file, $target_directory, 'prod_' . $id_producto . '_', 'images/products/');
            if ($result['success']) {
                $uploaded_paths[] = $result['path'];
            } else {
                // Falla crítica: deshacer bd y abortar
                throw new Exception("Fallo en compresión de imagen: " . $result['message']);
            }
        }

        // Doble chequeo crítico
        if (empty($uploaded_paths)) {
            throw new Exception("Fallo general al procesar las imágenes físicas.");
        }

        // 3. Insertar imágenes
        foreach ($uploaded_paths as $index => $path) {
            $db->ejecutar('registrarImagen', [
                ':id_producto' => $id_producto,
                ':url_imagen'  => $path
            ]);
        }

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Producto publicado exitosamente.']);

    }
    catch (Exception $e) {
        if (isset($conn) && $conn->inTransaction()) {
            $conn->rollBack();
        }
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
