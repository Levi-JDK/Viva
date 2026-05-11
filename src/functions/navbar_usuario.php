<?php
/**
 * src/functions/navbar_usuario.php
 *
 * Prepara las variables que necesita el navbar (partials/navbar.php):
 * $is_logged_in, $nombre_usuario, $foto_usuario, $email_usuario, $navbar_menus, $dropdown_menus.
 *
 * USO:
 *   require_once __DIR__ . '/navbar_usuario.php';
 *   cargar_datos_navbar();
 *
 * FUNCIONAMIENTO:
 * 1. Carga menú público por defecto (visitantes).
 * 2. Verifica JWT vía AuthHelper::verifyToken().
 * 3. Si hay token válido:
 *    a) Consulta Postgres para datos del usuario y menús autorizados.
 *    b) Si el usuario no está en Postgres aún (async registration), hace fallback a Redis
 *       para mostrar un estado básico (solo "Cerrar Sesión" en dropdown).
 *    c) Si no está ni en Postgres ni en Redis, destruye el token orphan.
 * 4. Si no hay token, devuelve defaults de visitante.
 */

require_once __DIR__ . '/error_handler.php';

function cargar_datos_navbar(): void
{
    // Valores por defecto seguros (el navbar los lee aunque no haya sesión)
    $GLOBALS['is_logged_in']   = false;
    $GLOBALS['nombre_usuario'] = '';
    $GLOBALS['email_usuario']  = '';
    $GLOBALS['foto_usuario']   = 'images/profiles/default.webp';
    $GLOBALS['navbar_menus']   = [];
    $GLOBALS['dropdown_menus'] = [];

    // Cargar los menús de la Navbar Principal para visitantes (Inicio, Categorías, Catálogo)
    try {
        require_once __DIR__ . '/database.php';
        $db = Database::getInstance();
        $stmtPublic = $db->ejecutar('obtenerMenuPublico');
        $GLOBALS['navbar_menus'] = $stmtPublic->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        ErrorHandler::handle($e, 'navbar_usuario.cargarMenuPublico');
        throw $e;
    }

    require_once __DIR__ . '/auth_helper.php';
    $userData = AuthHelper::verifyToken();
    $id_user = $userData ? $userData->id_user : null;

    if (!$id_user) {
        return; // No hay sesión activa, los defaults son suficientes
    }

    try {
        // Database usa Singleton, así que no importa cuántas veces se llame getInstance()
        require_once __DIR__ . '/database.php';
        $db = Database::getInstance();

        // Obtener datos del usuario autenticado
        $stmt    = $db->ejecutar('obtenerUsuarioPorId', [':id' => $id_user]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            $GLOBALS['is_logged_in']   = true;
            $GLOBALS['nombre_usuario'] = $usuario['nom_user']  ?? '';
            $GLOBALS['email_usuario']  = $usuario['mail_user'] ?? '';
            $GLOBALS['foto_usuario']   = !empty($usuario['foto_user'])
                                            ? $usuario['foto_user']
                                            : 'images/profiles/default.webp';

            // Cargar todos los menús autorizados del usuario
            $stmtMenu = $db->ejecutar('obtenerNavegacionUsuario', [':id_user' => $id_user]);
            $all_menus = $stmtMenu->fetchAll(PDO::FETCH_ASSOC);

            // Separar Navbar Principal (1, 2, 3) del Dropdown de Perfil
            $GLOBALS['navbar_menus'] = array_filter($all_menus, fn($m) => in_array($m['id_menu'], [1, 2, 3]));
            $dropdown_raw = array_filter($all_menus, fn($m) => !in_array($m['id_menu'], [1, 2, 3]));

            // Adaptar la URL de "Mi Stand" (ID 11) de forma dinámica
            // Si tiene stand → página pública, si no → formulario de creación
            $GLOBALS['dropdown_menus'] = array_map(function($m) use ($db, $id_user) {
                if ($m['id_menu'] == 11) {
                    try {
                        $stmt = $db->ejecutar('obtenerIdStandPorUser', [':id_user' => $id_user]);
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        if ($row && isset($row['id_stand'])) {
                            $m['url_menu'] = 'stand?id=' . $row['id_stand'];
                        } else {
                            $m['url_menu'] = 'mis_productos?view=stand';
                        }
                    } catch (Exception $e) {
                        ErrorHandler::handle($e, 'navbar_usuario.resolverMiStand');
                        throw $e;
                    }
                }
                return $m;
            }, $dropdown_raw);

        } else {
            // Usuario no encontrado en BD. Intentar Fallback en Redis (Ghost State)
            require_once dirname(__DIR__) . '/workers/Config/RedisConfig.php';
            try {
                $redis = RedisConfig::getConnection();
                $prefix = RedisConfig::getPrefix();
                $redisUser = $redis->hgetall($prefix . 'user:' . $id_user);

                if (!empty($redisUser)) {
                    $GLOBALS['is_logged_in']   = true;
                    // Usar nombre de Redis (sin badge para que el usuario no note la sincronización)
                    $GLOBALS['nombre_usuario'] = $redisUser['nombre'] ?? 'Usuario';
                    $GLOBALS['email_usuario']  = $redisUser['email'] ?? '';

                    // No sobreescribimos $GLOBALS['foto_usuario'] (ya tiene el default de arriba)
                    // ni $GLOBALS['navbar_menus'] (ya cargó el menú público 1, 2, 3 de BD)

                    // El dropdown queda vacío, por lo que en el navbar solo se renderizará el botón de "Cerrar Sesión"
                    $GLOBALS['dropdown_menus'] = [];
                    // Mantener el menú público en navbar (1, 2, 3), que ya fue cargado al inicio.
                    // Evitamos menús de Vender o Registro de Vendedor.
                } else {
                    // Si no está ni en BD ni en Redis, el token es huérfano, limpiar sesión
                    require_once __DIR__ . '/auth_helper.php';
                    AuthHelper::clearAuthCookie();
                    $GLOBALS['is_logged_in'] = false;
                }
            } catch (Exception $e) {
                ErrorHandler::handle($e, 'navbar_usuario.cargarFallbackRedis');
                throw $e;
            }
        }

    } catch (Exception $e) {
        ErrorHandler::handle($e, 'navbar_usuario.cargarDatos');
        throw $e;
    }
}
