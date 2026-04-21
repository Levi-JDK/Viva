<?php

require_once __DIR__ . '/../functions/auth_helper.php';
require_once __DIR__ . '/../functions/database.php';
require_once __DIR__ . '/../functions/error_handler.php';
require_once __DIR__ . '/../workers/Config/RedisConfig.php';

class LoginService
{
    public static function autenticarUsuario(array $input): array
    {
        $email = trim($input['email'] ?? '');
        $contrasena = $input['contrasena'] ?? '';

        if ($email === '' || $contrasena === '') {
            return [
                'mensaje' => 'Todos los campos son obligatorios.',
                'clase' => 'mensaje-error',
            ];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'mensaje' => 'El correo electrónico no es válido.',
                'clase' => 'mensaje-error',
            ];
        }

        try {
            $db = Database::getInstance();
        } catch (Exception $e) {
            throw $e;
        }

        try {
            $usuario = self::buscarUsuarioAutenticado($db, $email, $contrasena);

            if (!$usuario) {
                return [
                    'mensaje' => '❌ Correo o contraseña incorrectos',
                    'clase' => 'mensaje-error',
                ];
            }

            $token = AuthHelper::generateToken([
                'id_user' => $usuario['id_user'],
                'nombre' => $usuario['nom_user'],
                'email' => $email,
            ]);

            AuthHelper::setAuthCookie($token);

            return [
                'mensaje' => 'Inicio de sesión exitoso',
                'clase' => 'mensaje-exito',
                'redirect' => self::resolverRedirectSeguro($input['redirect'] ?? ''),
            ];
        } catch (PDOException $e) {
            throw $e;
        } catch (Exception $e) {
            throw $e;
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

    private static function buscarUsuarioAutenticado(Database $db, string $email, string $contrasena): ?array
    {
        $stmt = $db->ejecutar('obtenerHashLogin', [':email' => $email]);
        $hash = $stmt->fetchColumn();

        if ($hash && password_verify($contrasena, $hash)) {
            $stmtUsuario = $db->ejecutar('obtenerUsuarioPorEmail', [':email' => $email]);
            $usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);

            return $usuario ?: null;
        }

        return self::buscarUsuarioAutenticadoEnRedis($email, $contrasena);
    }

    private static function buscarUsuarioAutenticadoEnRedis(string $email, string $contrasena): ?array
    {
        try {
            $redis = RedisConfig::getConnection();
            $prefix = RedisConfig::getPrefix();
            $idTemporal = $redis->hget($prefix . 'email_to_id', $email);

            if (!$idTemporal) {
                return null;
            }

            $userHash = $redis->hgetall($prefix . 'user:' . $idTemporal);
            if (empty($userHash['password']) || !password_verify($contrasena, $userHash['password'])) {
                return null;
            }

            return [
                'id_user' => $idTemporal,
                'nom_user' => $userHash['nombre'] ?? '',
                'email' => $email,
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }
}
