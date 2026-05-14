<?php
require_once __DIR__ . '/../functions/auth_helper.php';
require_once __DIR__ . '/../functions/error_handler.php';
require_once __DIR__ . '/../services/UserService.php';

$usuarioData = AuthHelper::protectRoute();
AuthHelper::checkAccess(4);
$id_usuario = $usuarioData->id_user;

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['ajax']) || isset($_GET['api'])) {
    header('Content-Type: application/json');

    $accion = $_POST['accion'] ?? '';

    if ($accion === 'update_profile') {
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');

        if (empty($nombre) || empty($apellido)) {
            echo json_encode(['clase' => 'mensaje-error', 'mensaje' => 'El nombre y apellido son obligatorios.']);
            exit;
        }

        if (preg_match('/[#*\-\'\"]/', $nombre)) {
            echo json_encode(['clase' => 'mensaje-error', 'mensaje' => "El nombre no puede contener los caracteres: # * - ' \""]);
            exit;
        }

        if (preg_match('/[\'\"]/', $apellido)) {
            echo json_encode(['clase' => 'mensaje-error', 'mensaje' => "El apellido no puede contener comillas (' \")"]);
            exit;
        }

        try {
            UserService::actualizarPerfil((int) $id_usuario, $nombre, $apellido);

            echo json_encode(['clase' => 'mensaje-exito', 'mensaje' => 'Perfil actualizado correctamente.']);
            exit;
        } catch (PDOException $e) {
            $resp = ErrorHandler::jsonResponse($e, 'perfil.actualizarPerfil');
            echo json_encode($resp);
            exit;
        } catch (Exception $e) {
            $resp = ErrorHandler::jsonResponse($e, 'perfil.actualizarPerfil');
            echo json_encode($resp);
            exit;
        }
    }

    if ($accion === 'save_shipping_address') {
        $idDepartamento = filter_input(INPUT_POST, 'id_departamento', FILTER_VALIDATE_INT);
        $idCiudad = filter_input(INPUT_POST, 'id_ciudad', FILTER_VALIDATE_INT);
        $direccion = trim($_POST['dir_envio'] ?? '');
        $barrio = trim($_POST['barrio_envio'] ?? '');

        if (!$idDepartamento || !$idCiudad || mb_strlen($direccion) < 5) {
            echo json_encode([
                'exito' => false,
                'mensaje' => 'Completa todos los campos obligatorios (departamento, ciudad y dirección).',
            ]);
            exit;
        }

        try {
            UserService::guardarDireccionEnvio(
                (int) $id_usuario,
                (int) $idDepartamento,
                (int) $idCiudad,
                $direccion,
                $barrio !== '' ? $barrio : null
            );

            echo json_encode([
                'exito' => true,
                'mensaje' => 'Dirección de envío guardada correctamente.',
            ]);
            exit;
        } catch (RuntimeException $e) {
            echo json_encode([
                'exito' => false,
                'mensaje' => $e->getMessage(),
            ]);
            exit;
        } catch (PDOException $e) {
            $resp = ErrorHandler::jsonResponse($e, 'perfil.guardarDireccionEnvio');
            echo json_encode($resp);
            exit;
        } catch (Exception $e) {
            $resp = ErrorHandler::jsonResponse($e, 'perfil.guardarDireccionEnvio');
            echo json_encode($resp);
            exit;
        }
    }

    if ($accion === 'update_theme') {
        $theme = trim($_POST['theme'] ?? '');

        try {
            UserService::guardarPreferenciaTema((int) $id_usuario, $theme);

            echo json_encode([
                'exito' => true,
                'mensaje' => 'Tema actualizado.',
            ]);
            exit;
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode([
                'exito' => false,
                'mensaje' => $e->getMessage(),
            ]);
            exit;
        } catch (PDOException $e) {
            $resp = ErrorHandler::jsonResponse($e, 'perfil.actualizarTema');
            echo json_encode($resp);
            exit;
        } catch (Exception $e) {
            $resp = ErrorHandler::jsonResponse($e, 'perfil.actualizarTema');
            echo json_encode($resp);
            exit;
        }
    }

    if ($accion === 'change_password') {
        try {
            $resultado = UserService::cambiarPassword(
                (int) $id_usuario,
                $_POST['current_password'] ?? '',
                $_POST['new_password'] ?? '',
                $_POST['confirm_password'] ?? ''
            );

            if (!($resultado['exito'] ?? false)) {
                http_response_code((int) ($resultado['status'] ?? 400));
            }

            unset($resultado['status']);
            echo json_encode($resultado);
            exit;
        } catch (PDOException $e) {
            $resp = ErrorHandler::jsonResponse($e, 'perfil.cambiarPassword');
            echo json_encode($resp);
            exit;
        } catch (Exception $e) {
            $resp = ErrorHandler::jsonResponse($e, 'perfil.cambiarPassword');
            echo json_encode($resp);
            exit;
        }
    }

    echo json_encode(['exito' => false, 'mensaje' => 'Acción no reconocida.']);
    exit;
}

try {
    $perfil = UserService::obtenerDatosPerfil((int) $id_usuario);

    if (!$perfil) {
        AuthHelper::clearAuthCookie();
        header('Location: ' . BASE_URL . 'login');
        exit;
    }
} catch (PDOException $e) {
    ErrorHandler::handle($e, 'perfil.obtenerDatosPerfil');
    throw $e;
} catch (Exception $e) {
    ErrorHandler::handle($e, 'perfil.obtenerDatosPerfil');
    throw $e;
}

$nombre_usuario = $perfil['nombre_usuario'];
$apellido_usuario = $perfil['apellido_usuario'];
$nombre_completo = $perfil['nombre_completo'];
$email_usuario = $perfil['email_usuario'];
$foto_usuario = $perfil['foto_usuario'];
$fecha_registro = $perfil['fecha_registro'];
$fecha_formateada = $perfil['fecha_formateada'];
$inicial_usuario = $perfil['inicial_usuario'];
$themePreference = $perfil['theme_preference'];

$pedidos = UserService::obtenerPedidos((int) $id_usuario);
$menu_ids_usuario = UserService::obtenerMenuIdsUsuario((int) $id_usuario);
$es_productor = UserService::esProductor((int) $id_usuario);

// Cargar favoritos para renderizar con card_producto.php
$favoritos = [];
if (in_array(6, $menu_ids_usuario)) {
    try {
        require_once __DIR__ . '/../functions/database.php';
        $db = Database::getInstance();
        $stmt = $db->ejecutar('obtenerFavoritosUsuario', [':id_user' => (int)$id_usuario]);
        $favoritos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $favoritos = [];
    }
}

require_once __DIR__ . '/../views/perfil.view.php';
