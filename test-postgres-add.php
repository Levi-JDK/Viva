<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
require_once __DIR__ . '/src/functions/database.php';
require_once __DIR__ . '/src/services/CartService.php';

$res1 = CartService::gestionarItemsCarrito(1, 'agregar', 1, 1);
print_r($res1);
$res2 = CartService::gestionarItemsCarrito(1, 'agregar', 2, 1);
print_r($res2);
