<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
require_once __DIR__ . '/src/functions/database.php';
require_once __DIR__ . '/src/workers/Config/RedisConfig.php';
require_once __DIR__ . '/src/services/CartService.php';

// Clean start
$db = Database::getInstance();
$db->ejecutar('gestionarCarrito', [':id_user' => 1, ':accion' => 'limpiar', ':id_producto' => null, ':cantidad' => null]);
$redis = RedisConfig::getConnection();
$redis->flushDB();

echo "Step 1: Add A\n";
$res1 = CartService::redisUpdate(1, [['accion' => 'agregar', 'id_producto' => 1, 'cantidad' => 1]]);
print_r($res1);

echo "Step 2: Add B (simulate flush with B)\n";
$res2 = CartService::flushToPostgres(1, false, [['accion' => 'agregar', 'id_producto' => 2, 'cantidad' => 1]]);
print_r($res2);

echo "Step 3: Check Postgres\n";
$stmt = $db->ejecutar('gestionarCarrito', [':id_user' => 1, ':accion' => 'obtener', ':id_producto' => null, ':cantidad' => null]);
$fila = $stmt->fetch(PDO::FETCH_ASSOC);
$result = json_decode($fila['fun_carrito'] ?? $fila[array_key_first($fila)], true);
print_r($result['carrito']);
