<?php
/**
 * Manejador de Subida de Productos
 */

require_once __DIR__ . '/../utils/image_uploader.php';

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
        $stmtId = $db->ejecutar('obtenerUltimoIdProducto');
        $id_producto = $stmtId->fetchColumn();

        // 2. Subir imágenes físicas con validación centralizada
        $target_directory = __DIR__ . '/../../images/products/';
        $result = processAndUploadImages($_FILES['imagen_producto'] ?? null, $target_directory, 'prod_' . $id_producto . '_', 'images/products/');
        if (!$result['success']) {
            throw new Exception($result['message']);
        }

        $uploaded_paths = $result['paths'] ?? [];
        if (empty($uploaded_paths) && !empty($result['path'])) {
            $uploaded_paths = [$result['path']];
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
        throw $e;
    }
}
else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
