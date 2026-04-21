<?php

require_once __DIR__ . '/../functions/database.php';
require_once __DIR__ . '/../functions/error_handler.php';
require_once __DIR__ . '/../functions/mail_service.php';
require_once __DIR__ . '/../workers/Config/RedisConfig.php';
require_once __DIR__ . '/../workers/Services/ValidationService.php';

class RegisterService
{
    public static function registrarUsuario(array $input): array
    {
        $nombre = trim($input['nombre'] ?? '');
        $apellido = trim($input['apellido'] ?? '');
        $email = ValidationService::sanitizarEmail($input['email'] ?? '');
        $contrasena = $input['contrasena'] ?? '';

        $datosRegistro = [
            'nombre' => $nombre,
            'apellido' => $apellido,
            'email' => $email,
            'password' => $contrasena,
        ];

        $validacion = ValidationService::validarRegistro($datosRegistro);
        if (!$validacion['valido']) {
            return [
                'mensaje' => implode("\n", $validacion['errores']),
                'clase' => 'mensaje-error',
            ];
        }

        try {
            $db = Database::getInstance();
        } catch (Exception $e) {
            throw $e;
        }

        $hash = password_hash($contrasena, PASSWORD_ARGON2ID);

        try {
            return self::registrarUsuarioEnRedis($nombre, $apellido, $email, $hash);
        } catch (Exception $redisEx) {
            throw $redisEx;
        }
    }

    public static function resolverRedirectSeguro(string $redirectRaw = ''): string
    {
        $redirectTo = BASE_URL;

        if ($redirectRaw === '') {
            return $redirectTo;
        }

        $hostActual = parse_url(BASE_URL, PHP_URL_HOST);
        $hostRedirect = parse_url($redirectRaw, PHP_URL_HOST);

        if ($hostRedirect === $hostActual) {
            $redirectTo = $redirectRaw;
        }

        return $redirectTo;
    }

    private static function registrarUsuarioEnRedis(string $nombre, string $apellido, string $email, string $hash): array
    {
        $redis = RedisConfig::getConnection();
        $prefix = RedisConfig::getPrefix();

        $isRegistered = $redis->sismember($prefix . 'emails:registrados', $email);
        if ($isRegistered) {
            return [
                'mensaje' => 'El correo ya está registrado.',
                'clase' => 'mensaje-error',
            ];
        }

        $redis->setnx($prefix . 'contador:usuarios', 900000000);
        $idWorker = $redis->incr($prefix . 'contador:usuarios');
        $lockKey = $prefix . 'lock:email:' . $email;

        $pipe = $redis->pipeline();
        $pipe->setex($lockKey, 3600, '1');
        $pipe->hset($prefix . 'user:' . $idWorker, 'nombre', $nombre, 'apellido', $apellido, 'email', $email, 'password', $hash, 'created_at', date('Y-m-d H:i:s'));
        $pipe->lpush($prefix . 'queue:users', $idWorker);
        $pipe->hset($prefix . 'email_to_id', $email, $idWorker);
        $pipe->sadd($prefix . 'emails:registrados', $email);
        $pipe->sismember($prefix . 'emails:registrados', $email);
        $pipe->execute();

        return [
            'mensaje' => 'Registro aceptado. Estamos procesando su solicitud...',
            'clase' => 'mensaje-exito',
        ];
    }

    private static function registrarUsuarioEnBaseDatos(Database $db, string $nombre, string $apellido, string $email, string $hash): array
    {
        $stmtCheck = $db->ejecutar('validarEmail', [':email' => $email]);
        $existeEmail = $stmtCheck->fetchColumn();

        if ($existeEmail) {
            return [
                'mensaje' => 'El correo ya está registrado.',
                'clase' => 'mensaje-error',
            ];
        }

        $db->ejecutar('crearUsuario', [
            ':email' => $email,
            ':contrasena' => $hash,
            ':nombre' => $nombre,
            ':apellido' => $apellido,
        ]);

        self::enviarCorreoBienvenida($email, $nombre . ' ' . $apellido);

        return [
            'mensaje' => 'Usuario registrado correctamente.',
            'clase' => 'mensaje-exito',
        ];
    }

    private static function enviarCorreoBienvenida(string $email, string $nombreCompleto): void
    {
        try {
            $mail = MailService::getInstance();
            $mail->sendWelcomeEmail($email, $nombreCompleto);
        } catch (Exception $e) {
            throw $e;
        }
    }
}
