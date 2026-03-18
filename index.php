<?php
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Centralizar sesión aquí para que sea consistente en TODAS las rutas.
// domain='' evita que la cookie quede atada a 'localhost' o '127.0.0.1' específicamente.
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',       // Acepta cualquier host (localhost / 127.0.0.1)
        'secure'   => false,    // true en producción con HTTPS
        'httponly' => true,
        'samesite' => 'Lax',    // Lax permite la redirección desde ePayco
    ]);
    session_start();
}

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
// Definir rutas
$routes = [
    '/'             => 'src/controllers/index.php',
    '/index.php'    => 'src/controllers/index.php',
    '/login'        => 'src/controllers/login.php',
    '/dashboard'    => function() {
        header('Location: ' . BASE_URL . 'perfil');
        exit;
    },
    '/perfil'       => 'src/controllers/perfil.php',
    '/vender'       => 'src/controllers/registro_vendedor.php',
    '/logout'       => 'src/controllers/logout.php',
    '/mis_productos'=> 'src/controllers/mis_productos.php',
    '/catalogo'     => 'src/controllers/catalogo.php',
    '/admin_dashboard'=> 'src/controllers/admin_dashboard.php',
    '/api/productos'      => 'src/api/productos.php',
    '/api/upload_product' => 'src/functions/upload_product.php',
    '/api/update_product' => 'src/functions/update_product.php',
    '/api/delete_product' => 'src/functions/delete_product.php',
    '/api/upload'         => 'src/functions/upload.php',
    '/api/carrito'        => 'src/api/carrito.php',
    '/api/favoritos'      => 'src/api/favoritos.php',
    '/api/resenas'        => 'src/api/resenas.php',
    '/api/get_ciudades'   => 'src/api/get_ciudades.php',
    '/api/guardar_cliente'=> 'src/api/guardar_cliente.php',
    '/producto'           => 'src/controllers/producto.php',
    '/stand'              => 'src/controllers/stand_detail.php',
    '/stands'             => 'src/controllers/stands.php',
    '/checkout'           => 'src/controllers/checkout.php',
    '/checkout/respuesta' => 'src/controllers/checkout_response.php',
    '/pedido'             => 'src/controllers/pedido.php',
    '/terminos_condiciones'=> 'src/controllers/terminos_condiciones.php',
    '/politica_privacidad' => 'src/controllers/politica_privacidad.php',
    '/recuperar'           => 'src/controllers/recuperar.php',
    '/api/recuperar'       => 'src/api/recuperar.php',
    '/api/admin/actualizar_parametros' => 'src/api/admin/actualizar_parametros.php',
];

if (array_key_exists($relative_uri, $routes)) {
    $route = $routes[$relative_uri];
    if (is_callable($route)) {
        $route();
    } else {
        require_once ROOT_PATH . $route;
    }
} else {
    require_once ROOT_PATH . "src/views/404.php";
}

?>