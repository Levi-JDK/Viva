<?php

require_once __DIR__ . '/../functions/database.php';
require_once __DIR__ . '/../functions/error_handler.php';
require_once __DIR__ . '/../workers/Config/RedisConfig.php';

class UserService
{
    public static function guardarDireccionEnvio(int $userId, int $idDepartamento, int $idCiudad, string $direccion, ?string $barrio = null): void
    {
        $db = Database::getInstance();
        $stmtUsuario = $db->ejecutar('obtenerUsuarioPorId', [':id' => $userId]);
        $usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            throw new RuntimeException('Usuario no encontrado.');
        }

        $db->ejecutar('guardarCliente', [
            ':id_user' => $userId,
            ':id_client' => (string) $userId,
            ':nom' => trim(($usuario['nom_user'] ?? '') . ' ' . ($usuario['ape_user'] ?? '')),
            ':mail' => $usuario['mail_user'] ?? '',
            ':dpto' => $idDepartamento,
            ':ciudad' => $idCiudad,
            ':dir' => $direccion,
            ':barrio' => $barrio,
        ]);
    }

    public static function actualizarPerfil(int $userId, string $nombre, string $apellido): void
    {
        $db = Database::getInstance();
        $db->ejecutar('actualizarPerfil', [
            ':nombre' => $nombre,
            ':apellido' => $apellido,
            ':id' => $userId,
        ]);
    }

    public static function guardarPreferenciaTema(int $userId, string $theme): void
    {
        if (!in_array($theme, ['light', 'dark'], true)) {
            throw new InvalidArgumentException('Valor inválido');
        }

        $db = Database::getInstance();
        $db->ejecutar('actualizarTemaUsuario', [
            ':theme' => $theme,
            ':id' => $userId,
        ]);
    }

    public static function cambiarPassword(int $userId, string $currentPassword, string $newPassword, string $confirmPassword): array
    {
        $currentPassword = trim($currentPassword);
        $newPassword = trim($newPassword);
        $confirmPassword = trim($confirmPassword);

        if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            return ['exito' => false, 'mensaje' => 'Todos los campos son obligatorios.'];
        }

        if ($newPassword !== $confirmPassword) {
            return ['exito' => false, 'mensaje' => 'Las contraseñas no coinciden.'];
        }

        if ($currentPassword === $newPassword) {
            return ['exito' => false, 'mensaje' => 'La nueva contraseña debe ser diferente a la actual.'];
        }

        $errorPassword = self::validarPassword($newPassword);
        if ($errorPassword !== '') {
            return ['exito' => false, 'mensaje' => $errorPassword];
        }

        $db = Database::getInstance();
        $stmt = $db->ejecutar('obtenerHashPassword', [':id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || empty($row['pass_user'])) {
            return ['exito' => false, 'mensaje' => 'Usuario no encontrado.'];
        }

        if (!password_verify($currentPassword, $row['pass_user'])) {
            return ['exito' => false, 'mensaje' => 'La contraseña actual es incorrecta.', 'status' => 401];
        }

        $hash = password_hash($newPassword, PASSWORD_ARGON2ID);
        $db->ejecutar('actualizarPassword', [
            ':id_user' => $userId,
            ':pass_user' => $hash,
        ]);

        return ['exito' => true, 'mensaje' => 'Contraseña actualizada.'];
    }

    public static function esProductor(int $userId): bool
    {
        $db = Database::getInstance();
        $stmt = $db->ejecutar('obtenerIdProductor', [':id_user' => $userId]);

        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function obtenerDatosPerfil(int $userId): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->ejecutar('obtenerUsuarioPorId', [':id' => $userId]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            return null;
        }

        $nombreUsuario = $usuario['nom_user'] ?? 'Usuario';
        $apellidoUsuario = $usuario['ape_user'] ?? '';

        return [
            'usuario' => $usuario,
            'nombre_usuario' => $nombreUsuario,
            'apellido_usuario' => $apellidoUsuario,
            'nombre_completo' => trim($nombreUsuario . ' ' . $apellidoUsuario),
            'email_usuario' => $usuario['mail_user'] ?? '',
            'foto_usuario' => $usuario['foto_user'] ?? 'images/profiles/default.webp',
            'fecha_registro' => $usuario['created_at'] ?? null,
            'fecha_formateada' => self::formatearFechaRegistro($usuario['created_at'] ?? null),
            'inicial_usuario' => self::obtenerInicial($nombreUsuario),
            'theme_preference' => in_array(($usuario['theme_preference'] ?? 'light'), ['light', 'dark'], true)
                ? $usuario['theme_preference']
                : 'light',
        ];
    }

    public static function obtenerPedidos(int $userId): array
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->ejecutar('obtenerPedidosCliente', [':id_user' => $userId]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            ErrorHandler::handle($e, 'user.obtenerPedidos');
            throw $e;
        }
    }

    public static function obtenerMenuIdsUsuario(int $userId): array
    {
        try {
            $redis = RedisConfig::getConnection();
            $cacheKey = RedisConfig::getPrefix() . "user:{$userId}:menus";
            
            $cached = $redis->get($cacheKey);
            if ($cached) {
                return json_decode($cached, true);
            }
        } catch (Exception $e) {
            // Fail-safe: Si Redis falla, continuamos a la DB
            error_log("Redis Cache Error in UserService: " . $e->getMessage());
        }

        try {
            $db = Database::getInstance();
            $stmt = $db->ejecutar('obtenerNavegacionUsuario', [':id_user' => $userId]);
            $menuIds = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'id_menu');
            $menusLimpio = array_map('intval', $menuIds);

            try {
                if (isset($redis) && isset($cacheKey)) {
                    $redis->setex($cacheKey, 86400, json_encode($menusLimpio));
                }
            } catch (Exception $e) {} // Ignorar si falla al escribir
            
            return $menusLimpio;
        } catch (PDOException $e) {
            ErrorHandler::handle($e, 'user.obtenerMenuIdsUsuario');
            throw $e;
        }
    }

    private static function formatearFechaRegistro(?string $fechaRegistro): string
    {
        if (!$fechaRegistro) {
            return 'Fecha desconocida';
        }

        $fecha = new DateTime($fechaRegistro);
        $fechaFormateada = $fecha->format('F Y');
        $meses = [
            'January' => 'Enero',
            'February' => 'Febrero',
            'March' => 'Marzo',
            'April' => 'Abril',
            'May' => 'Mayo',
            'June' => 'Junio',
            'July' => 'Julio',
            'August' => 'Agosto',
            'September' => 'Septiembre',
            'October' => 'Octubre',
            'November' => 'Noviembre',
            'December' => 'Diciembre',
        ];

        return str_replace(array_keys($meses), array_values($meses), $fechaFormateada);
    }

    private static function obtenerInicial(string $nombreUsuario): string
    {
        return strtoupper(substr($nombreUsuario, 0, 1));
    }

    private static function validarPassword(string $password): string
    {
        if (strlen($password) < 8) {
            return 'Mínimo 8 caracteres.';
        }

        if (!preg_match('/[A-Z]/', $password)) {
            return 'Debe incluir al menos una mayúscula.';
        }

        if (!preg_match('/[a-z]/', $password)) {
            return 'Debe incluir al menos una minúscula.';
        }

        if (!preg_match('/\d/', $password)) {
            return 'Debe incluir al menos un número.';
        }

        if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            return 'Debe incluir al menos un símbolo.';
        }

        return '';
    }
}
