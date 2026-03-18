<?php
require_once __DIR__ . '/auth_helper.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/mail_service.php';

header('Content-Type: application/json');

try {
    $db = Database::getInstance();
} catch (Exception $e) {
    echo json_encode([
        "mensaje" => "Error al inicializar la base de datos: " . $e->getMessage(),
        "clase"   => "mensaje-error"
    ]);
    exit;
}


// ── GET: Verificar sesión actual via JWT ─────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $userData = AuthHelper::verifyToken();

    if ($userData) {
        echo json_encode([
            'loggedIn' => true,
            'nombre'   => $userData->nombre ?? '',
            'email'    => $userData->email  ?? ''
        ]);
    } else {
        echo json_encode(['loggedIn' => false]);
    }
    exit;
}


// ── POST: Registro, Login o Logout ───────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    // ── Registro ─────────────────────────────────────────────────────────────
    if ($accion === 'registro') {
        $nombre    = trim($_POST['nombre']    ?? '');
        $apellido  = trim($_POST['apellido']  ?? '');
        $email     = trim($_POST['email']     ?? '');
        $contrasena = $_POST['contrasena']    ?? '';

        if (empty($nombre) || empty($apellido) || empty($email) || empty($contrasena)) {
            echo json_encode(["mensaje" => "Todos los campos son obligatorios.", "clase" => "mensaje-error"]);
            exit;
        }

        if (preg_match('/[#*\-\'"]/', $nombre)) {
            echo json_encode(["mensaje" => "El nombre no puede contener los caracteres: # * - ' \"", "clase" => "mensaje-error"]);
            exit;
        }

        if (preg_match('/[\'\"]/', $apellido)) {
            echo json_encode(["mensaje" => "El apellido no puede contener comillas (' \")", "clase" => "mensaje-error"]);
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(["mensaje" => "El correo electrónico no es válido.", "clase" => "mensaje-error"]);
            exit;
        }

        $hash = password_hash($contrasena, PASSWORD_ARGON2ID);

        try {
            // Validar que el email no exista
            $stmtCheck = $db->ejecutar('validarEmail', [':email' => $email]);
            $isAvailable = $stmtCheck->fetchColumn();

            if (!$isAvailable) {
                echo json_encode(["mensaje" => "El correo ya está registrado.", "clase" => "mensaje-error"]);
                exit;
            }

            // Crear usuario
            $db->ejecutar('crearUsuario', [
                ':email'      => $email,
                ':contrasena' => $hash,
                ':nombre'     => $nombre,
                ':apellido'   => $apellido
            ]);

            // Enviar correo de bienvenida (en background, sin bloquear la respuesta)
            try {
                $mail = MailService::getInstance();
                $mail->sendWelcomeEmail($email, $nombre . ' ' . $apellido);
            } catch (Exception $e) {
                error_log('[Auth] Error enviando correo de bienvenida: ' . $e->getMessage());
            }

            echo json_encode(["mensaje" => "Usuario registrado correctamente.", "clase" => "mensaje-exito"]);

        } catch (PDOException $e) {
            echo json_encode(["mensaje" => "Error en el registro: " . $e->getMessage(), "clase" => "mensaje-error"]);
        }

    // ── Login ─────────────────────────────────────────────────────────────────
    } elseif ($accion === 'login') {
        $email      = trim($_POST['email']     ?? '');
        $contrasena = $_POST['contrasena']     ?? '';

        if (empty($email) || empty($contrasena)) {
            echo json_encode(["mensaje" => "Todos los campos son obligatorios.", "clase" => "mensaje-error"]);
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(["mensaje" => "El correo electrónico no es válido.", "clase" => "mensaje-error"]);
            exit;
        }

        try {
            $stmt = $db->ejecutar('obtenerHashLogin', [':email' => $email]);
            $hash = $stmt->fetchColumn();

            if ($hash && password_verify($contrasena, $hash)) {
                $stmtUsuario = $db->ejecutar('obtenerUsuarioPorEmail', [':email' => $email]);
                $usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);

                $token = AuthHelper::generateToken([
                    'id_user' => $usuario['id_user'],
                    'nombre'  => $usuario['nom_user'],
                    'email'   => $email
                ]);
                AuthHelper::setAuthCookie($token);

                $redirectTo = !empty($_POST['redirect']) ? $_POST['redirect'] : BASE_URL;
                echo json_encode(["mensaje" => "Inicio de sesión exitoso", "clase" => "mensaje-exito", "redirect" => $redirectTo]);
            } else {
                echo json_encode(["mensaje" => "❌ Correo o contraseña incorrectos", "clase" => "mensaje-error"]);
            }

        } catch (PDOException $e) {
            echo json_encode(["mensaje" => "Error en la base de datos: " . $e->getMessage(), "clase" => "mensaje-error"]);
        }

    // ── Logout ────────────────────────────────────────────────────────────────
    } elseif ($accion === 'logout') {
        AuthHelper::clearAuthCookie();
        echo json_encode(["mensaje" => "Sesión cerrada.", "clase" => "mensaje-exito"]);

    } else {
        echo json_encode(["mensaje" => "Acción no válida.", "clase" => "mensaje-error"]);
    }
}
?>
