<?php
require 'vendor/autoload.php';
require_once __DIR__ . '/src/workers/Config/RedisConfig.php';

$redis = RedisConfig::getConnection();
$prefix = RedisConfig::getPrefix();

echo "--- REDIS STATE ---\n";
echo "Cola Registros (Length): " . $redis->llen($prefix . 'cola:registros') . "\n";
echo "Cola DLQ (Length): " . $redis->llen('viva:cola:deadletter') . "\n";

$keys = $redis->keys($prefix . 'user:*');
echo "Usuarios pendientes en Hash: " . count($keys) . "\n";
foreach($keys as $key) {
    print_r($redis->hgetall($key));
}

echo "--- POSTGRES STATE ---\n";
$pdo = new PDO('pgsql:host=localhost;dbname=db_viva', 'postgres', 'Gerson03#');
$stmt = $pdo->query("SELECT nom_user, email_user FROM tab_user WHERE email_user = 'test_async1@mail.com'");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
