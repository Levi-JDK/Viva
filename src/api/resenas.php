<?php

/**
 * API Endpoint: /api/resenas
 * 
 * Uso:
 * - POST: Crea o actualiza una reseña (requiere sesión).
 *   Body: { id_producto: _, calificacion: _, texto: _ }
 */



header('Content-Type: application/json');

require_once __DIR__ . '/../functions/auth_helper.php';
$userData = AuthHelper::protectRoute();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['exito' => false, 'mensaje' => 'Datos inválidos']);
    exit;
}

$id_producto = isset($input['id_producto']) ? (int)$input['id_producto'] : 0;
$calificacion = isset($input['calificacion']) ? (int)$input['calificacion'] : 0;
$texto = trim($input['texto'] ?? '');
$id_user = (int)$userData->id_user;

// Validaciones estrictas del backend
if (empty($id_producto)) {
    echo json_encode(['exito' => false, 'mensaje' => 'Identificador de producto requerido']);
    exit;
}

if ($calificacion < 1 || $calificacion > 5) {
    echo json_encode(['exito' => false, 'mensaje' => 'La calificación debe estar entre 1 y 5 estrellas']);
    exit;
}

if (empty($texto)) {
    echo json_encode(['exito' => false, 'mensaje' => 'El texto de la reseña no puede estar vacío']);
    exit;
}

try {
    require_once __DIR__ . '/../functions/database.php';
    $db = Database::getInstance();

    // Validar si el usuario es el mismo productor (no puede auto-reseñarse)
    $stmtProd = $db->ejecutar('obtenerDetalleProducto', [':id_producto' => $id_producto]);
    $prod = $stmtProd->fetch(PDO::FETCH_ASSOC);
    $stmt = $db->ejecutar('agregarResena', [
        ':id_user' => $id_user,
        ':id_producto' => $id_producto,
        ':calificacion' => $calificacion,
        ':texto' => $texto
    ]);

    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    $exito = $res['fun_c_resena'] ?? false;
    
    if ($exito) {
        echo json_encode(['exito' => true, 'mensaje' => '¡Gracias por tu reseña!']);
    } else {
        echo json_encode(['exito' => false, 'mensaje' => 'Ocurrió un error o los datos son inválidos']);
    }

} catch (Exception $e) {
    error_log("[Reseñas API] Error: " . $e->getMessage());
    echo json_encode(['exito' => false, 'mensaje' => 'Error interno en el servidor']);
}
