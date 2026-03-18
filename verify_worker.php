<?php
require 'vendor/autoload.php';
require_once __DIR__ . '/src/workers/Config/RedisConfig.php';

$redis = RedisConfig::getConnection();
$prefix = RedisConfig::getPrefix();

echo "--- REDIS QUEUE STATE ---\n";
echo "Registros (viva:cola:registros): " . $redis->llen($prefix . 'cola:registros') . "\n";
echo "DLQ (viva:cola:deadletter): " . $redis->llen($prefix . 'cola:deadletter') . "\n";

echo "\n--- PENDING HASHES ---\n";
$keys = $redis->keys($prefix . 'user:*');
foreach ($keys as $k) {
    echo "$k -> ";
    print_r($redis->hgetall($k));
}

echo "\n--- DB CHECK ---\n";
try {
    $pdo = new PDO('pgsql:host=localhost;dbname=db_viva', 'postgres', 'Gerson03#');
    $stmt = $pdo->query("SELECT id_user, nom_user, email_user FROM tab_user ORDER BY id_user DESC LIMIT 3");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo "DB Error: " . $e->getMessage();
}
