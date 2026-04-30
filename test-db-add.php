<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
require_once __DIR__ . '/src/functions/database.php';

$db = Database::getInstance();
$stmt = $db->ejecutar('gestionarCarrito', [
    ':id_user' => 1,
    ':accion' => 'agregar',
    ':id_producto' => 1,
    ':cantidad' => 2,
]);
$fila = $stmt->fetch(PDO::FETCH_ASSOC);
var_dump(json_decode($fila['fun_carrito'] ?? $fila[array_key_first($fila)], true));
