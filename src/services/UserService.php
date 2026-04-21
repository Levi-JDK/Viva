<?php

require_once __DIR__ . '/../functions/database.php';

class UserService
{
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
            $db = Database::getInstance();
            $stmt = $db->ejecutar('obtenerNavegacionUsuario', [':id_user' => $userId]);
            $menuIds = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'id_menu');

            return array_map('intval', $menuIds);
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
