<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
require_once __DIR__ . '/src/functions/database.php';
require_once __DIR__ . '/src/services/CartService.php';

// Try to get a cart. We will add a product to user 1 manually via DB if possible, or just see the output structure.
$dbItems = CartService::gestionarItemsCarrito(1, 'obtener');
var_dump($dbItems);
