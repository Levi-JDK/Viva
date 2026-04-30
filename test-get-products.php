<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
require_once __DIR__ . '/src/functions/database.php';

$db = Database::getInstance();
$stmt = $db->connection->query('SELECT id FROM productos LIMIT 5');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
