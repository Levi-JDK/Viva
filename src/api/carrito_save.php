<?php
/**
 * API: Guardar Carrito en Redis
 * Ruta: POST /api/carrito_save
 *
 * Recibe el estado actual del carrito y lo guarda en Redis.
 * También agrega el user_id a la cola de procesamiento asíncrono.
 *
 * Parámetros esperados (JSON body):
 *   - user_id    integer  Requerido: ID del usuario
 *   - items      array    Requerido: Array de productos
 *   - resumen    object   Opcional: { total_items, total_precio }
 *
 * Respuesta JSON:
 *   { success: boolean, cart_id: string, message?: string }
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../workers/Config/RedisConfig.php';

try {
    // Solo aceptar POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode([
            'success' => false,
            'message' => 'Método no permitido'
        ]);
        exit;
    }

    // Leer JSON body
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        echo json_encode([
            'success' => false,
            'message' => 'Cuerpo de solicitud inválido o vacío'
        ]);
        exit;
    }

    // Validar user_id
    $userId = isset($input['user_id']) ? (int)$input['user_id'] : 0;
    if ($userId <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'user_id es requerido y debe ser mayor a 0'
        ]);
        exit;
    }

    // Validar items
    $items = $input['items'] ?? [];
    if (empty($items)) {
        echo json_encode([
            'success' => false,
            'message' => 'El carrito está vacío'
        ]);
        exit;
    }

    // Conectar a Redis
    $redis = RedisConfig::getConnection();
    $prefix = RedisConfig::getPrefix();

    // Generar cart_id único
    $cartId = $prefix . 'carrito:' . $userId . ':' . time();

    // Preparar datos del carrito
    $carritoData = [
        'user_id' => $userId,
        'items' => json_encode($items),
        'resumen' => json_encode($input['resumen'] ?? [
            'total_items' => 0,
            'total_precio' => 0
        ]),
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];

    // Guardar en Hash Redis - sintaxis: hset('key', 'field', 'value', 'field', 'value', ...)
    $redis->hset($cartId, 
        'user_id', $carritoData['user_id'],
        'items', $carritoData['items'],
        'resumen', $carritoData['resumen'],
        'created_at', $carritoData['created_at'],
        'updated_at', $carritoData['updated_at']
    );

    // Establecer TTL de 24 horas
    $redis->expire($cartId, 86400);

    // También guardar en una clave más accesible para lectura rápida
    $quickKey = $prefix . 'carrito:quick:' . $userId;
    $redis->set($quickKey, json_encode([
        'cart_id' => $cartId,
        'items' => $items,
        'resumen' => $input['resumen'] ?? ['total_items' => 0, 'total_precio' => 0],
        'updated_at' => date('Y-m-d H:i:s')
    ]));
    $redis->expire($quickKey, 86400);

    // Agregar a la cola de procesamiento
    $colaKey = $prefix . 'cola:carrito';
    $redis->rpush($colaKey, json_encode([
        'cart_id' => $cartId,
        'user_id' => $userId,
        'action' => 'save',
        'timestamp' => date('Y-m-d H:i:s')
    ]));

    echo json_encode([
        'success' => true,
        'cart_id' => $cartId,
        'message' => 'Carrito guardado correctamente'
    ]);

} catch (Exception $e) {
    error_log('[carrito_save] Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al guardar el carrito'
    ]);
}
