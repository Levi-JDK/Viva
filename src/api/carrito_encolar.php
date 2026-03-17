<?php
/**
 * API: Encolar Carrito para Procesamiento
 * Ruta: POST /api/carrito_encolar
 * 
 * Encola el carrito del usuario para que el worker lo procese.
 * Se usa cuando:
 * - El usuario cierra la pestaña (sendBeacon)
 * - El usuario hace click en "Proceder al Pago"
 * 
 * Parámetros esperados (JSON body):
 *   - user_id    integer  Requerido: ID del usuario
 * 
 * Respuesta JSON:
 *   { success: boolean, message: string }
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../workers/Config/RedisConfig.php';

try {
    // Solo aceptar POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode([
            'success' => false,
            'message' => 'Metodo no permitido'
        ]);
        exit;
    }

    // Leer JSON body
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        echo json_encode([
            'success' => false,
            'message' => 'Cuerpo de solicitud invalido o vacio'
        ]);
        exit;
    }

    // Validar user_id
    $userId = isset($input['user_id']) ? (int)$input['user_id'] : 0;
    if ($userId <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'user_id es requerido'
        ]);
        exit;
    }

    // Conectar a Redis
    $redis = RedisConfig::getConnection();
    $prefix = RedisConfig::getPrefix();

    // Buscar el carrito rapido del usuario
    $quickKey = $prefix . 'carrito:quick:' . $userId;
    $carritoData = $redis->get($quickKey);

    if (!$carritoData) {
        echo json_encode([
            'success' => false,
            'message' => 'No hay carrito para encolar'
        ]);
        exit;
    }

    // Encolar para procesamiento
    $colaKey = $prefix . 'cola:carrito';
    $redis->rpush($colaKey, json_encode([
        'user_id' => $userId,
        'action' => 'procesar',
        'timestamp' => date('Y-m-d H:i:s')
    ]));

    echo json_encode([
        'success' => true,
        'message' => 'Carrito encolado para procesamiento'
    ]);

} catch (Exception $e) {
    error_log('[carrito_encolar] Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al encolar el carrito'
    ]);
}
