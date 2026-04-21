<?php

require_once __DIR__ . '/../functions/database.php';

class OrderService
{
    public static function obtenerDetallePedido(int $idFactura, int $idUser): ?array
    {
        $db = Database::getInstance();

        $pedido = self::obtenerEncabezadoFactura($db, $idFactura, $idUser);
        if (!$pedido) {
            return null;
        }

        return [
            'pedido' => $pedido,
            'detalles' => self::obtenerLineasFactura($db, $idFactura),
        ];
    }

    private static function obtenerEncabezadoFactura(Database $db, int $idFactura, int $idUser): ?array
    {
        $stmt = $db->ejecutar('obtenerFacturaPorId', [
            ':id_factura' => $idFactura,
            ':id_user' => $idUser,
        ]);

        $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

        return $pedido ?: null;
    }

    private static function obtenerLineasFactura(Database $db, int $idFactura): array
    {
        $stmt = $db->ejecutar('obtenerDetallesFactura', [':id_factura' => $idFactura]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
