<?php
// 1. Detectar el protocolo y host
$protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];

// Detectar carpeta del proyecto
$proyecto_folder = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$proyecto_folder = rtrim($proyecto_folder, '/');

// 3. Definir BASE_URL = url https
define('BASE_URL', $protocolo . "://" . $host . $proyecto_folder . "/");

// Definir Root_path para la direccion de la carpeta

define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT'] . $proyecto_folder . DIRECTORY_SEPARATOR);

// Enrutar
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$relative_uri = str_replace($proyecto_folder, '', $request_uri);
$relative_uri = '/' . ltrim($relative_uri, '/');

// Normalizar URI (quitar slash final si no es la raíz)
if ($relative_uri !== '/' && substr($relative_uri, -1) === '/') {
    $relative_uri = rtrim($relative_uri, '/');
}

if ($relative_uri === '/' || $relative_uri === '/index.php'){
    require_once ROOT_PATH . "src/controllers/index.php";
}else if($relative_uri === '/login'){
    require_once ROOT_PATH . "src/controllers/login.php";
}else if($relative_uri === '/dashboard' || $relative_uri === '/perfil'){
    require_once ROOT_PATH . "src/controllers/dashboard.php";
}else{
    require_once ROOT_PATH . "src/views/404.php";
}

?>