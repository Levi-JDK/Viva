<?php

require_once __DIR__ . '/../functions/database.php';
require_once __DIR__ . '/../functions/error_handler.php';
require_once __DIR__ . '/../workers/Config/RedisConfig.php';

class CartService
{
    private const QUEUE_CARRITO = 'viva:cola:carrito';
    private const SUPPORTED_ACTIONS = ['agregar', 'actualizar', 'eliminar', 'limpiar'];
    private const ACTION_ALIASES = [
        'add' => 'agregar',
        'agregar' => 'agregar',
        'update' => 'actualizar',
        'actualizar' => 'actualizar',
        'set' => 'actualizar',
        'remove' => 'eliminar',
        'delete' => 'eliminar',
        'eliminar' => 'eliminar',
        'clear' => 'limpiar',
        'limpiar' => 'limpiar',
    ];

    public static function gestionarItemsCarrito(int $userId, string $accion, $productoId = null, $cantidad = null): array
    {
        $db = Database::getInstance();

        $params = [
            ':id_user' => $userId,
            ':accion' => $accion,
            ':id_producto' => $productoId ? (int) $productoId : null,
            ':cantidad' => $cantidad ? (int) $cantidad : null,
        ];

        $stmt = $db->ejecutar('gestionarCarrito', $params);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        return json_decode($fila['fun_carrito'] ?? $fila[array_key_first($fila)], true);
    }

    public static function redisUpdate(int $userId, array $acciones): array
    {
        if ($userId <= 0) {
            throw new InvalidArgumentException('id_user inválido para redis_update.');
        }

        if (!array_is_list($acciones) || empty($acciones)) {
            throw new InvalidArgumentException('acciones debe ser array no vacío para redis_update.');
        }

        $redis = RedisConfig::getConnection();
        $hashKey = self::getRedisCartHashKey($userId);
        $timestamp = date('Y-m-d H:i:s');

        $snapshot = self::readRedisCartSnapshot($redis, $hashKey, $userId);

        $normalizedActions = [];

        foreach ($acciones as $indice => $accion) {
            $normalizedAction = self::normalizeAction($accion, $indice);
            self::applyRedisCartAction($snapshot, $normalizedAction);
            $normalizedActions[] = $normalizedAction;
        }

        self::writeRedisCartSnapshot($redis, $hashKey, $snapshot);
        self::pushCartQueueJob($redis, $userId, $normalizedActions, $timestamp);

        return [
            'success' => true,
            'message' => 'Carrito consolidado en Redis',
            'hash_key' => $hashKey,
            'acciones' => $acciones,
            'items_count' => count($snapshot),
            'updated_at' => $timestamp,
        ];
    }

    public static function flushToPostgres(int $userId, bool $forceSync = false, ?array $acciones = null): array
    {
        if ($userId <= 0) {
            throw new InvalidArgumentException('id_user inválido para flush_to_postgres.');
        }

        $redis = RedisConfig::getConnection();
        $hashKey = self::getRedisCartHashKey($userId);
        $timestamp = date('Y-m-d H:i:s');

        if (!$redis->exists($hashKey)) {
            return [
                'success' => true,
                'message' => 'No hay cambios pendientes en Redis',
                'force_sync' => $forceSync,
                'flushed' => false,
                'items_count' => 0,
            ];
        }

        $snapshot = self::readRedisCartSnapshot($redis, $hashKey, $userId);

        if ($acciones !== null) {
            if (!array_is_list($acciones)) {
                throw new InvalidArgumentException('acciones debe ser array válido para flush_to_postgres.');
            }

            foreach ($acciones as $indice => $accion) {
                self::applyRedisCartAction($snapshot, self::normalizeAction($accion, $indice));
            }

            self::writeRedisCartSnapshot($redis, $hashKey, $snapshot);
        }

        if (empty($snapshot)) {
            return [
                'success' => true,
                'message' => 'No hay cambios pendientes en Redis',
                'force_sync' => $forceSync,
                'flushed' => false,
                'items_count' => 0,
            ];
        }

        $db = Database::getInstance();

        try {
            $db->connection->beginTransaction();

            self::gestionarItemsCarrito($userId, 'limpiar');

            foreach ($snapshot as $productoId => $cantidad) {
                self::gestionarItemsCarrito($userId, 'agregar', (int) $productoId, (int) $cantidad);
            }

            $db->connection->commit();
        } catch (Throwable $exception) {
            if ($db->connection->inTransaction()) {
                $db->connection->rollBack();
            }

            ErrorHandler::handle($exception, 'cart.flushToPostgres');
            throw $exception;
        }

        $redis->del([$hashKey]);

        return [
            'success' => true,
            'message' => 'Carrito persistido desde Redis a Postgres',
            'force_sync' => $forceSync,
            'flushed' => true,
            'items_count' => count($snapshot),
            'flushed_at' => date('Y-m-d H:i:s'),
        ];
    }

    private static function getRedisCartHashKey(int $userId): string
    {
        return RedisConfig::getPrefix() . 'carrito:user:' . $userId;
    }

    private static function readRedisCartSnapshot($redis, string $hashKey, int $userId = null): array
    {
        $rawHash = $redis->hgetall($hashKey);

        if (!is_array($rawHash) || empty($rawHash)) {
            if ($userId !== null) {
                $dbItems = self::gestionarItemsCarrito($userId, 'obtener');
                $snapshot = [];
                // Extract items array if response is wrapped, otherwise iterate directly
                $items = $dbItems['items'] ?? $dbItems;
                if (is_array($items)) {
                    foreach ($items as $item) {
                        if (is_array($item) && isset($item['id_producto'], $item['cantidad'])) {
                            $snapshot[$item['id_producto']] = (int) $item['cantidad'];
                        }
                    }
                }
                return $snapshot;
            }
            return [];
        }

        $snapshot = [];

        foreach ($rawHash as $field => $value) {
            if (str_starts_with((string) $field, '__')) {
                continue;
            }

            $productoId = (int) $field;
            $cantidad = (int) $value;

            if ($productoId <= 0 || $cantidad < 0) {
                throw new UnexpectedValueException('Hash de carrito Redis corrupto para user_id ' . $hashKey . '.');
            }

            if ($cantidad > 0) {
                $snapshot[$productoId] = $cantidad;
            }
        }

        return $snapshot;
    }

    private static function writeRedisCartSnapshot($redis, string $hashKey, array $snapshot): void
    {
        $redis->del([$hashKey]);

        foreach ($snapshot as $productoId => $cantidad) {
            $redis->hset($hashKey, (string) $productoId, (string) $cantidad);
        }

        $redis->expire($hashKey, 86400);
    }

    private static function pushCartQueueJob($redis, int $userId, array $actions, string $timestamp): void
    {
        $payload = json_encode([
            'user_id' => $userId,
            'acciones_json' => json_encode($actions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'queued_at' => $timestamp,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($payload === false) {
            throw new RuntimeException('No se pudo serializar el job de carrito Redis.');
        }

        $redis->lpush(self::QUEUE_CARRITO, $payload);
    }

    private static function normalizeAction(array $accion, int $indice): array
    {
        $nombreAccion = self::normalizeActionName(
            $accion['accion']
                ?? $accion['action']
                ?? $accion['type']
                ?? null,
            $indice
        );

        $productoId = self::normalizeNullableInt(
            $accion['id_producto']
                ?? $accion['idProducto']
                ?? $accion['product_id']
                ?? $accion['id']
                ?? null
        );

        $cantidad = self::normalizeNullableInt(
            $accion['cantidad']
                ?? $accion['qty']
                ?? $accion['quantity']
                ?? null
        );

        if ($nombreAccion !== 'limpiar' && ($productoId === null || $productoId <= 0)) {
            throw new InvalidArgumentException('id_producto inválido en posición ' . $indice . '.');
        }

        if (in_array($nombreAccion, ['agregar', 'actualizar'], true) && ($cantidad === null || $cantidad <= 0)) {
            throw new InvalidArgumentException('cantidad inválida en posición ' . $indice . '.');
        }

        return [
            'accion' => $nombreAccion,
            'id_producto' => $productoId,
            'cantidad' => $cantidad,
        ];
    }

    private static function normalizeActionName(mixed $value, int $indice): string
    {
        $action = strtolower(trim((string) $value));

        if ($action === '' || !isset(self::ACTION_ALIASES[$action])) {
            throw new InvalidArgumentException('Acción de carrito inválida en posición ' . $indice . '.');
        }

        $normalized = self::ACTION_ALIASES[$action];

        if (!in_array($normalized, self::SUPPORTED_ACTIONS, true)) {
            throw new InvalidArgumentException('Acción de carrito inválida en posición ' . $indice . '.');
        }

        return $normalized;
    }

    private static function normalizeNullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_numeric($value)) {
            throw new InvalidArgumentException('Valor numérico inválido en acción de carrito.');
        }

        return (int) $value;
    }

    private static function applyRedisCartAction(array &$snapshot, array $accion): void
    {
        $nombreAccion = $accion['accion'];

        if ($nombreAccion === 'limpiar') {
            $snapshot = [];
            return;
        }

        $productoId = (int) $accion['id_producto'];

        if ($nombreAccion === 'eliminar') {
            unset($snapshot[$productoId]);
            return;
        }

        if ($nombreAccion === 'actualizar') {
            $snapshot[$productoId] = (int) $accion['cantidad'];
            return;
        }

        $snapshot[$productoId] = (int) ($snapshot[$productoId] ?? 0) + (int) $accion['cantidad'];

        if ($snapshot[$productoId] <= 0) {
            unset($snapshot[$productoId]);
        }
    }
}
