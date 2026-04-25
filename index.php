<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/functions/error_handler.php';
require_once __DIR__ . '/src/functions/url_helper.php';

set_exception_handler(function (Throwable $e): void {
    $response = ErrorHandler::handle($e, 'index.php');
    $relativePath = request_relative_path();
    $isApiRequest = is_api_route_path($relativePath);

    if (!headers_sent()) {
        http_response_code(500);
    }

    if ($isApiRequest) {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }

        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    if (!headers_sent()) {
        header('Content-Type: text/html; charset=utf-8');
    }

    $message = htmlspecialchars($response['message'] ?? 'Error en el servidor. Por favor, intente más tarde.', ENT_QUOTES, 'UTF-8');

    echo "<h1>Ha ocurrido un error</h1>"
        . "<p>{$message}</p>";
    exit;
});

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

// Detectar carpeta del proyecto
$proyecto_folder = parse_url(BASE_URL, PHP_URL_PATH) ?? '';
$proyecto_folder = rtrim($proyecto_folder, '/');

// Definir Root_path para la direccion de la carpeta

define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT'] . $proyecto_folder . DIRECTORY_SEPARATOR);

if (!function_exists('send_json_error_response')) {
    function send_json_error_response(int $statusCode, string $message): void
    {
        if (!headers_sent()) {
            http_response_code($statusCode);
            header('Content-Type: application/json; charset=utf-8');
        }

        echo json_encode([
            'success' => false,
            'message' => $message,
            'status' => $statusCode,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}

// Enrutar
$request_uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$relative_uri = request_relative_path($request_uri);
// Definir rutas
$routes = [
    '/'             => 'src/controllers/index.controller.php',
    '/index.php'    => 'src/controllers/index.controller.php',
    '/login'        => 'src/controllers/login.controller.php',
    '/registro'     => 'src/controllers/register.controller.php',
    '/dashboard'    => function() {
        header('Location: ' . base_url_path('perfil'));
        exit;
    },
    '/perfil'       => 'src/controllers/perfil.controller.php',
    '/vender'       => 'src/controllers/registro_vendedor.controller.php',
    '/registro_vendedor' => 'src/controllers/vendor_registration.controller.php',
    '/logout'       => 'src/controllers/logout.controller.php',
    '/mis_productos'=> 'src/controllers/mis_productos.controller.php',
    '/catalogo'     => 'src/controllers/catalogo.controller.php',
    '/admin_dashboard'=> 'src/controllers/admin.controller.php',
    '/admin'        => 'src/controllers/admin.controller.php',
    '/api/upload_product' => 'src/functions/upload_product.php',
    '/api/update_product' => 'src/functions/update_product.php',
    '/api/delete_product' => 'src/functions/delete_product.php',
    '/api/upload'         => 'src/functions/upload.php',
    '/carrito'            => 'src/controllers/carrito.controller.php',
    '/api/carrito'        => 'src/controllers/carrito.controller.php',
    '/favoritos'          => 'src/controllers/favoritos.controller.php',
    '/api/favoritos'      => 'src/controllers/favoritos.controller.php',
    '/api/resenas'        => 'src/controllers/resenas.controller.php',
    '/api/ciudades'       => 'src/controllers/ciudades.controller.php',
    '/producto'           => 'src/controllers/producto.controller.php',
    '/stand'              => 'src/controllers/stand_detail.controller.php',
    '/stands'             => 'src/controllers/stands.controller.php',
    '/checkout'           => 'src/controllers/checkout.controller.php',
    '/checkout/respuesta' => 'src/controllers/checkout.controller.php',
    '/pedido'             => 'src/controllers/pedido.controller.php',
    '/terminos_condiciones'=> 'src/controllers/terminos_condiciones.controller.php',
    '/politica_privacidad' => 'src/controllers/politica_privacidad.controller.php',
    '/recuperar'           => 'src/controllers/recuperar.controller.php',
];

if (array_key_exists($relative_uri, $routes)) {
    $route = $routes[$relative_uri];
    if (is_callable($route)) {
        $route();
    } else {
        require_once ROOT_PATH . $route;
    }
} else {
    if (strpos($relative_uri, '/api/') === 0) {
        send_json_error_response(404, 'Ruta API no encontrada.');
    }

    require_once ROOT_PATH . "src/views/404.php";
}

?>
