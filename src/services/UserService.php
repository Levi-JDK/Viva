<?php

require_once __DIR__ . '/../functions/database.php';
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
        ];
    }

    public static function obtenerPedidos(int $userId): array
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->ejecutar('obtenerPedidosCliente', [':id_user' => $userId]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
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
}
