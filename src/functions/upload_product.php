<?php
/**
 * Manejador de Subida de Productos
 */

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/error_handler.php';
require_once __DIR__ . '/product_validation_queue.php';

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

        // --- FIN VALIDACIONES, EJECUTAR INSERCIÓN ---

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
            ':is_active'          => 'false'
        ]);

        $result_insert = $stmt->fetchColumn();

        if (!$result_insert) {
            throw new Exception("Error al crear el producto en la base de datos.");
        }

        // Recuperar el ID generado
        $stmtId = $db->ejecutar('obtenerUltimoIdProducto');
        $id_producto = $stmtId->fetchColumn();

        // 2. Guardar imágenes temporales (sin procesar)
        $tempDir = __DIR__ . '/../../images/products/temp/';
        if (!is_dir($tempDir) && !mkdir($tempDir, 0775, true)) {
            throw new Exception("No se pudo crear el directorio temporal.");
        }

        $tempPaths = [];
        $files = $_FILES['imagen_producto'] ?? null;
        if ($files && !is_array($files['error'])) {
            // Normalizar: un solo archivo
            $files = [
                'name' => [$files['name']],
                'error' => [$files['error']],
                'tmp_name' => [$files['tmp_name']],
            ];
        }

        if ($files && is_array($files['error'])) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            foreach ($files['error'] as $i => $error) {
                if ($error !== UPLOAD_ERR_OK) continue;
                
                $origName = $files['name'][$i] ?? '';
                $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed, true)) continue;
                
                $tempName = 'prod_' . $id_producto . '_' . time() . '_' . $i . '.' . $ext;
                $tempFullPath = $tempDir . $tempName;
                
                if (move_uploaded_file($files['tmp_name'][$i], $tempFullPath)) {
                    $tempPaths[] = 'images/products/temp/' . $tempName;
                }
            }
        }

        if (empty($tempPaths)) {
            throw new Exception("No se pudo procesar la imagen.");
        }

        // 3. Insertar imágenes temporales en tab_imagenes
        foreach ($tempPaths as $tempPath) {
            $db->ejecutar('registrarImagen', [
                ':id_producto' => $id_producto,
                ':url_imagen'  => $tempPath,
            ]);
        }

        // 4. Encolar validación (el worker procesará las imágenes)
        viva_enqueue_product_validation(
            (int) $id_producto,
            (int) $id_productor,
            viva_product_validation_images($tempPaths),
            $nom_producto,
            $desc,
            $id_materia ?? '',
            $id_categoria ?? ''
        );

        echo json_encode(['success' => true, 'message' => 'Producto añadido satisfactoriamente, en espera de revisión.']);

    }
    catch (Exception $e) {
        $resp = ErrorHandler::jsonResponse($e, 'upload_product');
        echo json_encode($resp);
        exit;
    }
}
else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
