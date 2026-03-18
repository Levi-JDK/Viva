<?php

/**
 * API: Carrito de Compras
 * Ruta: POST /api/carrito
 *
 * Gestiona todas las operaciones del carrito del usuario autenticado.
 * Delega la lógica y validaciones a la función SQL fun_carrito().
 *
 * Parámetros esperados (JSON body o form-data):
 *   - accion      string   Requerido: 'obtener' | 'agregar' | 'eliminar' | 'actualizar' | 'limpiar'
 *   - id_producto integer  Requerido para: agregar, eliminar, actualizar
 *   - cantidad    integer  Requerido para: agregar, actualizar
 *
 * Respuesta JSON:
 *   { exito, mensaje, carrito: [...], resumen: { total_items, total_precio } }
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../functions/auth_helper.php';
$userData = AuthHelper::protectRoute();
$id_user = $userData->id_user;

// ── Solo aceptar POST ────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido']);
    exit;
}

// ── Leer parámetros (soporta JSON body y form-data) ─────────────────────────
$input = json_decode(file_get_contents('php://input'), true) ?? [];

$accion      = $input['accion']      ?? $_POST['accion']      ?? null;
$id_producto = $input['id_producto'] ?? $_POST['id_producto'] ?? null;
$cantidad    = $input['cantidad']    ?? $_POST['cantidad']    ?? null;

// ── Validar acción ───────────────────────────────────────────────────────────
$acciones_validas = ['obtener', 'agregar', 'eliminar', 'actualizar', 'limpiar'];

if (!$accion || !in_array($accion, $acciones_validas)) {
    echo json_encode([
        'exito'   => false,
        'mensaje' => 'Acción no válida. Use: ' . implode(', ', $acciones_validas)
    ]);
    exit;
}

// ── Llamar a la función de la BD ─────────────────────────────────────────────
try {
    require_once __DIR__ . '/../functions/database.php';
    $db = Database::getInstance();

    $params = [
        ':id_user'     => $id_user,
        ':accion'      => $accion,
        ':id_producto' => $id_producto ? (int) $id_producto : null,
        ':cantidad'    => $cantidad    ? (int) $cantidad    : null,
    ];

    $stmt = $db->ejecutar('gestionarCarrito', $params);
    $fila = $stmt->fetch(PDO::FETCH_ASSOC);

    // La función devuelve una columna llamada 'fun_carrito' con el JSON
    $resultado = json_decode($fila['fun_carrito'] ?? $fila[array_key_first($fila)], true);

    echo json_encode($resultado);

} catch (Exception $e) {
    error_log('[Carrito API] Error: ' . $e->getMessage());
    echo json_encode([
        'exito'   => false,
        'mensaje' => 'Error interno del servidor'
    ]);
}
