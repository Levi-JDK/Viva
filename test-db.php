<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
require_once __DIR__ . '/src/functions/database.php';

$db = Database::getInstance();
$stmt = $db->ejecutar('gestionarCarrito', [
    ':id_user' => 1,
    ':accion' => 'obtener',
    ':id_producto' => null,
    ':cantidad' => null,
]);

$fila = $stmt->fetch(PDO::FETCH_ASSOC);
$result = json_decode($fila['fun_carrito'] ?? $fila[array_key_first($fila)], true);

var_dump($result);
