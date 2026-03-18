<?php
/**
 * API: Recuperación de contraseña con OTP
 *
 * POST accion=solicitar  → Genera y envía el código OTP al email
 * POST accion=confirmar  → Valida el código y actualiza la contraseña
 */
require_once __DIR__ . '/../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->safeLoad();

require_once __DIR__ . '/../functions/database.php';
require_once __DIR__ . '/../functions/mail_service.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido']);
    exit;
}

try {
    $db = Database::getInstance();
} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => 'Error de base de datos']);
    exit;
}

$accion = $_POST['accion'] ?? '';

// ══════════════════════════════════════════════════════════
// PASO 1: Solicitar código OTP
// ══════════════════════════════════════════════════════════
if ($accion === 'solicitar') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['exito' => false, 'mensaje' => 'Correo electrónico inválido']);
        exit;
    }

    try {
        $minutos = (int)($_ENV['RESET_TOKEN_EXP_MINUTES'] ?? 15);
        $stmt = $db->ejecutar('crearResetToken', [
            ':mail_user' => $email,
            ':minutos'   => $minutos,
        ]);
        $token = $stmt->fetchColumn();

        if (!$token) {
            // No revelar si el correo existe o no (seguridad)
            echo json_encode(['exito' => true, 'mensaje' => 'Si el correo existe, recibirás el código en breve.']);
            exit;
        }

        // Obtener nombre del usuario para el correo
        $stmtUser = $db->connection->prepare("SELECT nom_user FROM tab_users WHERE mail_user = :email AND is_deleted = FALSE LIMIT 1");
        $stmtUser->execute([':email' => $email]);
        $nombre = $stmtUser->fetchColumn() ?: 'Usuario';

        // Enviar correo
        $mail = MailService::getInstance();
        $mail->sendPasswordRecoveryEmail($email, $nombre, $token);

        echo json_encode(['exito' => true, 'mensaje' => 'Si el correo existe, recibirás el código en breve.']);

    } catch (Exception $e) {
        error_log('[Recuperar] Error al solicitar OTP: ' . $e->getMessage());
        echo json_encode(['exito' => false, 'mensaje' => 'Error interno. Inténtalo de nuevo.']);
    }

// ══════════════════════════════════════════════════════════
// PASO 2: Confirmar código y establecer nueva contraseña
// ══════════════════════════════════════════════════════════
} elseif ($accion === 'confirmar') {
    $email      = trim($_POST['email']      ?? '');
    $token      = trim($_POST['token']      ?? '');
    $pass_nueva = $_POST['pass_nueva']      ?? '';
    $pass_conf  = $_POST['pass_confirmacion'] ?? '';

    if (empty($email) || empty($token) || empty($pass_nueva)) {
        echo json_encode(['exito' => false, 'mensaje' => 'Todos los campos son obligatorios']);
        exit;
    }

    if ($pass_nueva !== $pass_conf) {
        echo json_encode(['exito' => false, 'mensaje' => 'Las contraseñas no coinciden']);
        exit;
    }

    if (strlen($pass_nueva) < 8) {
        echo json_encode(['exito' => false, 'mensaje' => 'La contraseña debe tener al menos 8 caracteres']);
        exit;
    }

    try {
        // Validar el token
        $stmt = $db->ejecutar('validarResetToken', [
            ':mail_user'  => $email,
            ':token_reset' => $token,
        ]);
        $id_user = $stmt->fetchColumn();

        if (!$id_user) {
            echo json_encode(['exito' => false, 'mensaje' => 'Código inválido o expirado']);
            exit;
        }

        // Actualizar contraseña
        $hash = password_hash($pass_nueva, PASSWORD_ARGON2ID);
        $db->ejecutar('actualizarPassword', [
            ':id_user'   => (int)$id_user,
            ':pass_user' => $hash,
        ]);

        echo json_encode(['exito' => true, 'mensaje' => '¡Contraseña actualizada! Ya puedes iniciar sesión.']);

    } catch (Exception $e) {
        error_log('[Recuperar] Error al confirmar OTP: ' . $e->getMessage());
        echo json_encode(['exito' => false, 'mensaje' => 'Error interno. Inténtalo de nuevo.']);
    }

} else {
    echo json_encode(['exito' => false, 'mensaje' => 'Acción no válida']);
}
