<?php
require_once __DIR__ . '/../functions/auth_helper.php';
require_once __DIR__ . '/../services/UserService.php';

$usuarioData = AuthHelper::protectRoute();
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
            throw $e;
        } catch (Exception $e) {
            throw $e;
        }
    }

    echo json_encode(['clase' => 'mensaje-error', 'mensaje' => 'Acción no reconocida.']);
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
    throw $e;
} catch (Exception $e) {
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

$pedidos = UserService::obtenerPedidos((int) $id_usuario);
$menu_ids_usuario = UserService::obtenerMenuIdsUsuario((int) $id_usuario);

require_once __DIR__ . '/../views/perfil.view.php';
