<?php

/**
 * Middleware de Autenticación JWT para VIVA Ecommerce
 * Centraliza la emisión, validación y destrucción de cookies seguras HttpOnly.
 */

require_once __DIR__ . '/../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

// Detectar BASE_URL
$protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
// Dependiendo desde dónde se llame, limpiamos los subdirectorios conocidos:
$proyecto_folder = str_replace(['/src/functions', '/src/api', '/src/controllers/mis_productos', '/src/controllers'], '', $script_dir);
$proyecto_folder = rtrim($proyecto_folder, '/');
if (!defined('BASE_URL')) {
    define('BASE_URL', $protocolo . "://" . $host . $proyecto_folder . "/");
}

class AuthHelper {

    /**
     * @var string Clave secreta para firmar el token (Idealmente desde $_ENV)
     */
    private static string $secret_key = 'TU_SUPER_CLAVE_SECRETA_AQUI_CAMBIAME'; 
    
    /**
     * @var string Algoritmo de encriptación utilizado por Firebase JWT
     */
    private static string $encrypt = 'HS256';

    /**
     * @var string Nombre oficial de la cookie que almacenará el token
     */
    private static string $cookie_name = 'access_token';

    /**
     * Genera un nuevo Token JWT y lo firma.
     * 
     * @param array $user_data Datos a inyectar en el payload (ej. id_user, rol)
     * @param int $expSeconds Tiempo de vida en segundos (por defecto 7 días).
     * @return string Token JWT codificado
     */
    public static function generateToken(array $user_data, int $expSeconds = 604800): string {
         $time = time();
         
         $payload = [
             'iat'  => $time,
             'exp'  => $time + $expSeconds,
             'data' => $user_data
         ];

         return JWT::encode($payload, self::$secret_key, self::$encrypt);
    }

    /**
     * Almacena el Token JWT de manera segura en el navegador del cliente.
     * 
     * @param string $token JWT en formato string
     * @param int $expSeconds Segundos de vida para la cookie. Mismo valor que el token.
     * @return void
     */
    public static function setAuthCookie(string $token, int $expSeconds = 604800): void {
        $expires = time() + $expSeconds;
        
        // Obtener dominio de forma dinámica
        $domain = $_SERVER['HTTP_HOST'] ?? '';
        // Limpiar el puerto si viene en el host (ej. localhost:8080)
        if (strpos($domain, ':') !== false) {
            $domain = explode(':', $domain)[0];
        }
        
        // En localhost el domain de la cookie suele comportarse mejor si se deja vacío
        if ($domain === 'localhost' || $domain === '127.0.0.1') {
            $domain = '';
        }

        $options = [
            'expires'  => $expires,
            'path'     => '/',
            'domain'   => $domain,
            'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,     // Previene acceso desde JavaScript
            'samesite' => 'Lax'     // Previene envíos cruzados
        ];

        setcookie(self::$cookie_name, $token, $options);
    }

    /**
     * Verifica la validez algorítmica y temporal de la cookie JWT actual.
     * Si expiró, purga la cookie y expulsa al usuario devolviendo false.
     * 
     * @return object|false Devuelve el payload ($obj->data) o false si es inválido
     */
    public static function verifyToken(): object|false {
        if (!isset($_COOKIE[self::$cookie_name])) {
            return false;
        }

        $jwt = $_COOKIE[self::$cookie_name];

        try {
            // Decodifica y valida simultáneamente
            $decoded = JWT::decode($jwt, new Key(self::$secret_key, self::$encrypt));
            return match (true) {
                isset($decoded->data) => $decoded->data,
                default => false
            };
            
        } catch (ExpiredException $e) {
            // Si el token expiró oficialmente según su fecha 'exp'
            self::clearAuthCookie();
            return false;
        } catch (\Exception $e) {
            // Firmas inválidas o alteraciones maliciosas
            return false;
        }
    }

    /**
     * Destruye la cookie configurando su caducidad en el pasado.
     * 
     * @return void
     */
    public static function clearAuthCookie(): void {
        $domain = $_SERVER['HTTP_HOST'] ?? '';
        if (strpos($domain, ':') !== false) { $domain = explode(':', $domain)[0]; }
        if ($domain === 'localhost' || $domain === '127.0.0.1') { $domain = ''; }

        $options = [
            'expires'  => time() - 3600,
            'path'     => '/',
            'domain'   => $domain,
            'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax'
        ];
        
        setcookie(self::$cookie_name, '', $options);
    }

    /**
     * Middleware de protección de rutas. 
     * Maneja inteligentemente peticiones tradicionales vs peticiones AJAX/Fetch API.
     * 
     * @return object Datos del usuario logueado
     */
    public static function protectRoute(): object {
        $userData = self::verifyToken();
        
        if (!$userData) {
            // Detectar si la petición es AJAX (Fetch API o jQuery)
            $isAjax = false;
            
            // Check manual de headers comunes para Fetch/AJAX
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                $isAjax = true;
            }
            // Muchos clientes Fetch modernos envían application/json en el Accept
            if (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
                $isAjax = true;
            }

            if ($isAjax) {
                // Responder 401 Unauthorized en JSON puro para el API
                header('Content-Type: application/json');
                http_response_code(401);
                echo json_encode([
                    'success' => false, 
                    'error' => 'unauthorized', 
                    'message' => 'La sesión ha expirado o no es válida.'
                ]);
                exit();
            } else {
                // Petición de navegación tradicional (redirección)
                header("Location: " . BASE_URL . "login"); 
                exit();
            }
        }

        return $userData;
    }

    /**
     * Valida si el usuario actual posee permisos para el ID del menú solicitado.
     * Si no los posee, redirige a una página segura (index) o aborta.
     * 
     * @param int $id_menu El identificador en tab_menu a validar
     * @return void
     */
    public static function checkAccess(int $id_menu): void {
        $userData = self::protectRoute();
        
        require_once __DIR__ . '/database.php';
        $db = Database::getInstance();
        $stmtMenu = $db->ejecutar('obtenerNavegacionUsuario', [':id_user' => $userData->id_user]);
        $user_menus = $stmtMenu->fetchAll(\PDO::FETCH_ASSOC);

        $hasAccess = false;
        foreach ($user_menus as $menu) {
            if (isset($menu['id_menu']) && $menu['id_menu'] == $id_menu) {
                $hasAccess = true;
                break;
            }
        }

        if (!$hasAccess) {
            // Check for AJAX to return JSON instead
            if ((!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || 
                (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
                header('Content-Type: application/json');
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'forbidden', 'message' => 'Acceso denegado al módulo solicitado.']);
                exit();
            } else {
                header("Location: " . BASE_URL . "?error=access_denied");
                exit();
            }
        }
    }
}
