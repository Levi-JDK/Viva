<?php
require_once __DIR__ . '/../services/ProductService.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['ajax']) || isset($_GET['api'])) {
    header('Content-Type: application/json; charset=UTF-8');
    header('X-Content-Type-Options: nosniff');

    $response = ProductService::obtenerRespuestaCatalogoApi($_GET);
    http_response_code($response['http_code']);
    echo json_encode($response['payload'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    exit;
}

$catalogoData = ProductService::obtenerDatosCatalogo($_GET);
$filtros = $catalogoData['filtros'];
$categorias_list = $catalogoData['categorias_list'];
$oficios_list = $catalogoData['oficios_list'];
$materias_list = $catalogoData['materias_list'];
$productos = $catalogoData['productos'];

require_once __DIR__ . '/../views/catalogo.view.php';
