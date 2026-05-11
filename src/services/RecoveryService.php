<?php

require_once __DIR__ . '/../functions/database.php';
require_once __DIR__ . '/../functions/error_handler.php';
require_once __DIR__ . '/../functions/mail_service.php';

class RecoveryService
{
    private static function getRequiredEnvInt(string $key): int
    {
        if (!isset($_ENV[$key]) || trim((string) $_ENV[$key]) === '') {
            throw new \RuntimeException($key . ' debe estar configurado en .env');
        }

        return (int) trim((string) $_ENV[$key]);
    }

    public static function procesarSolicitud(array $input): array
    {
        if (!isset($_ENV['APP_ENV'])) {
            require_once __DIR__ . '/../../vendor/autoload.php';

            $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
            $dotenv->safeLoad();
        }

        try {
            $db = Database::getInstance();
        } catch (Exception $e) {
            ErrorHandler::handle($e, 'recovery.procesarSolicitud.database');
            throw $e;
        }

        $accion = $input['accion'] ?? '';

        if ($accion === 'solicitar') {
            return self::solicitarRecuperacion($db, $input);
        }

        if ($accion === 'confirmar') {
            return self::confirmarRecuperacion($db, $input);
        }

        return ['exito' => false, 'mensaje' => 'Acción no válida'];
    }

    private static function solicitarRecuperacion(Database $db, array $input): array
    {
        $email = trim($input['email'] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['exito' => false, 'mensaje' => 'Correo electrónico inválido'];
        }

        try {
            $minutos = self::getRequiredEnvInt('RESET_TOKEN_EXP_MINUTES');
            $stmt = $db->ejecutar('crearResetToken', [
                ':mail_user' => $email,
                ':minutos' => $minutos,
            ]);
            $token = $stmt->fetchColumn();

            if (!$token) {
                return ['exito' => true, 'mensaje' => 'Si el correo existe, recibirás el código en breve.'];
            }

            $stmtUser = $db->ejecutar('obtenerNombreUsuarioPorEmail', [':email' => $email]);
            $nombre = $stmtUser->fetchColumn() ?: 'Usuario';

            $mail = MailService::getInstance();
            $enviado = $mail->sendPasswordRecoveryEmail($email, $nombre, $token);

            if (!$enviado) {
                $ultimoError = method_exists($mail, 'getLastError') ? $mail->getLastError() : 'Error al enviar el correo';
                error_log('[RecoveryService] Fallo al enviar correo: ' . $ultimoError);

                return ['exito' => false, 'mensaje' => 'Error al enviar el correo. Por favor, intenta de nuevo más tarde.'];
            }

            return ['exito' => true, 'mensaje' => 'Si el correo existe, recibirás el código en breve.'];
        } catch (\Throwable $e) {
            ErrorHandler::handle($e, 'recovery.solicitarRecuperacion');
            throw $e;
        }
    }

    private static function confirmarRecuperacion(Database $db, array $input): array
    {
        $email = trim($input['email'] ?? '');
        $token = trim($input['token'] ?? '');
        $passNueva = $input['pass_nueva'] ?? '';
        $passConfirmacion = $input['pass_confirmacion'] ?? '';

        if (empty($email) || empty($token) || empty($passNueva)) {
            return ['exito' => false, 'mensaje' => 'Todos los campos son obligatorios'];
        }

        if ($passNueva !== $passConfirmacion) {
            return ['exito' => false, 'mensaje' => 'Las contraseñas no coinciden'];
        }

        if (strlen($passNueva) < 8) {
            return ['exito' => false, 'mensaje' => 'La contraseña debe tener al menos 8 caracteres'];
        }

        try {
            $stmt = $db->ejecutar('validarResetToken', [
                ':mail_user' => $email,
                ':token_reset' => $token,
            ]);
            $idUser = $stmt->fetchColumn();

            if (!$idUser) {
                return ['exito' => false, 'mensaje' => 'Código inválido o expirado'];
            }

            $hash = password_hash($passNueva, PASSWORD_ARGON2ID);
            $db->ejecutar('actualizarPassword', [
                ':id_user' => (int) $idUser,
                ':pass_user' => $hash,
            ]);

            return ['exito' => true, 'mensaje' => '¡Contraseña actualizada! Ya puedes iniciar sesión.'];
        } catch (\Throwable $e) {
            ErrorHandler::handle($e, 'recovery.confirmarRecuperacion');
            throw $e;
        }
    }
}
