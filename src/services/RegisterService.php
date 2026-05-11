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
        $hash = password_hash($contrasena, PASSWORD_ARGON2ID);
        try {
            return self::registrarUsuarioEnRedis($nombre, $apellido, $email, $hash);
        } catch (Exception $redisEx) {
            ErrorHandler::handle($redisEx, 'register.registrarUsuario');
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
        $pipe->execute();

        // Verificar que los datos se guardaron en Redis
        // (cola puede estar vacía si worker ya lo consumió - eso es OK)
        $userKeyExists = $redis->exists($prefix . 'user:' . $idWorker);

        if (!$userKeyExists) {
            $redis->del($prefix . 'user:' . $idWorker);
            throw new RuntimeException('Error al guardar usuario - intentá de nuevo');
        }

        // Solo si llegó a la cola, marcar como registrado
        $pipe2 = $redis->pipeline();
        $pipe2->hset($prefix . 'email_to_id', $email, $idWorker);
        $pipe2->sadd($prefix . 'emails:registrados', $email);
        $pipe2->execute();

        return [
            'mensaje' => 'Registro aceptado. Estamos procesando su solicitud...',
            'clase' => 'mensaje-exito',
        ];
    }

    public static function registrarUsuarioEnBaseDatos(Database $db, string $nombre, string $apellido, string $email, string $hash): array
    {
        $stmtCheck = $db->ejecutar('validarEmail', [':email' => $email]);
        $isEmailValid = $stmtCheck->fetchColumn();

        if (!$isEmailValid) {
            return [
                'mensaje' => 'El correo ya está registrado.',
                'clase' => 'mensaje-error',
            ];
        }

        $stmtCreate = $db->ejecutar('crearUsuario', [
            ':email' => $email,
            ':contrasena' => $hash,
            ':nombre' => $nombre,
            ':apellido' => $apellido,
        ]);
        $creado = $stmtCreate->fetchColumn();
        if (!$creado) {
            throw new Exception("Error interno al crear el usuario en la base de datos.");
        }

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
            ErrorHandler::handle($e, 'register.enviarCorreoBienvenida');
            throw $e;
        }
    }
}
