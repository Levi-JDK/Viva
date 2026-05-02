<?php

require_once __DIR__ . '/../functions/database.php';
require_once __DIR__ . '/PuntoEnvioService.php';

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

    public static function obtenerCheckpoints(int $idFactura, int $idUser): array
    {
        $db = Database::getInstance();
        $stmt = $db->ejecutar('obtenerGuiaPorFactura', [
            ':id_factura' => $idFactura,
            ':id_user' => $idUser,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return [
                'success' => false,
                'message' => 'No se encontró el pedido solicitado.',
                'checkpoints' => [],
            ];
        }

        $numGuia = trim((string) ($row['num_guia'] ?? ''));
        if ($numGuia === '') {
            return [
                'success' => true,
                'checkpoints' => [],
            ];
        }

        return [
            'success' => true,
            'checkpoints' => PuntoEnvioService::getCheckpoints($numGuia),
        ];
    }

    public static function confirmarEntrega(int $idFactura, int $idUser): array
    {
        $db = Database::getInstance();
        $stmt = $db->ejecutar('obtenerGuiaPorFactura', [
            ':id_factura' => $idFactura,
            ':id_user' => $idUser,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return [
                'success' => false,
                'message' => 'No se encontró el pedido solicitado.',
            ];
        }

        $numGuia = trim((string) ($row['num_guia'] ?? ''));
        if ($numGuia === '') {
            return [
                'success' => false,
                'message' => 'El pedido no tiene guía de envío.',
            ];
        }

        if (!PuntoEnvioService::confirmarEntrega($numGuia)) {
            return [
                'success' => false,
                'message' => 'No se pudo confirmar la recepción del paquete.',
            ];
        }

        return [
            'success' => true,
            'message' => 'Recepción del paquete confirmada.',
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
