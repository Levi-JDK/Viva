<?php

require_once __DIR__ . '/../functions/database.php';
require_once __DIR__ . '/../functions/error_handler.php';
require_once __DIR__ . '/CartService.php';

class InvoiceService
{
    public static function facturarCarrito(int $idUserPago, array $transaccion, array $datosFactura, array $itemsCarrito): ?int
    {
        if (empty($itemsCarrito)) {
            return null;
        }

        $idsProd = [];
        $cantidades = [];

        foreach ($itemsCarrito as $item) {
            $idsProd[] = (int) $item['id_producto'];
            $cantidades[] = (int) $item['cantidad'];
        }

        try {
            $db = Database::getInstance();
            $stmtFac = $db->ejecutar('facturar', [
                ':id_user' => $idUserPago,
                ':id_pago' => 'EPAYCO',
                ':dpto' => $datosFactura['id_dpto_fact'],
                ':ciudad' => $datosFactura['id_ciudad_fact'],
                ':dir' => $datosFactura['dir_envio_fact'],
                ':epayco_ref' => $transaccion['x_ref_payco'] ?? null,
                ':epayco_txn' => $transaccion['x_transaction_id'] ?? null,
                ':epayco_estado' => $transaccion['x_response'] ?? null,
                ':ids_producto' => '{' . implode(',', $idsProd) . '}',
                ':cantidades' => '{' . implode(',', $cantidades) . '}',
            ]);

            $idFactura = $stmtFac->fetchColumn();
            if (!$idFactura) {
                error_log('[Invoice] fun_facturar devolvió NULL.');

                return null;
            }

            $db->ejecutar('gestionarCarrito', [
                ':id_user' => $idUserPago,
                ':accion' => 'limpiar',
                ':id_producto' => null,
                ':cantidad' => null,
            ]);

            return (int) $idFactura;
        } catch (Throwable $e) {
            ErrorHandler::handle($e, 'invoice.facturarCarrito');
            error_log('[Invoice] facturarCarrito: ' . $e->getMessage());
            throw $e;
        }
    }

    public static function obtenerDatosFactura(int $idUserPago): array
    {
        $datosFactura = [
            'dir_envio_fact' => null,
            'id_dpto_fact' => 11,
            'id_ciudad_fact' => 11001,
        ];

        try {
            $db = Database::getInstance();
            $rowDir = $db->ejecutar('obtenerDireccionCliente', [':id_user' => $idUserPago])->fetch(PDO::FETCH_ASSOC);

            if (!$rowDir) {
                return $datosFactura;
            }

            $datosFactura['id_dpto_fact'] = $rowDir['id_departamento'];
            $datosFactura['id_ciudad_fact'] = $rowDir['id_ciudad'];
            $datosFactura['dir_envio_fact'] = trim(($rowDir['dir_envio'] ?? '') . ' ' . ($rowDir['barrio_envio'] ?? ''));
        } catch (Throwable $e) {
            ErrorHandler::handle($e, 'invoice.obtenerDatosFactura');
            error_log('[Invoice] obtenerDatosFactura: ' . $e->getMessage());
            throw $e;
        }

        return $datosFactura;
    }

    public static function obtenerCarrito(int $idUser): array
    {
        $db = Database::getInstance();
        $stmt = $db->ejecutar('gestionarCarrito', [
            ':id_user' => $idUser,
            ':accion' => 'obtener',
            ':id_producto' => null,
            ':cantidad' => null,
        ]);

        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$fila) {
            return ['exito' => false, 'carrito' => []];
        }

        $decoded = json_decode($fila['fun_carrito'] ?? $fila[array_key_first($fila)], true);

        return is_array($decoded) ? $decoded : ['exito' => false, 'carrito' => []];
    }

    public static function actualizarClienteEpayco(int $idUserPago, array $transaccion): void
    {
        try {
            $tipoDocMap = ['CC' => 1, 'NIT' => 2, 'CE' => 3, 'PP' => 4];
            $docRecibido = trim((string) ($transaccion['x_customer_document'] ?? ''));
            if (strpos($docRecibido, '*') !== false) {
                $docRecibido = '';
            } elseif ($docRecibido === '') {
                $docRecibido = (string) $idUserPago;
            }

            $telRecibido = $transaccion['x_customer_phone'] ?? null;
            if (is_string($telRecibido) && strpos($telRecibido, '*') !== false) {
                $telRecibido = null;
            }

            Database::getInstance()->ejecutar('actualizarClienteEpayco', [
                ':id_user' => $idUserPago,
                ':id_client' => $docRecibido,
                ':id_tipo_doc' => $tipoDocMap[$transaccion['x_customer_doctype'] ?? ''] ?? null,
                ':tel' => $telRecibido,
                ':ref' => $transaccion['x_ref_payco'] ?? null,
                ':txn' => $transaccion['x_transaction_id'] ?? null,
                ':banco' => $transaccion['x_bank_name'] ?? null,
                ':cod_resp' => $transaccion['x_cod_response'] ?? null,
            ]);
        } catch (Throwable $e) {
            ErrorHandler::handle($e, 'invoice.actualizarClienteEpayco');
            error_log('[Invoice] actualizarClienteEpayco: ' . $e->getMessage());
            throw $e;
        }
    }

    public static function flushRedisCartBeforeCheckout(int $idUser): void
    {
        CartService::flushToPostgres($idUser, true);
    }

    public static function obtenerDatosVistaCheckout(int $idUser): array
    {
        $db = Database::getInstance();
        self::flushRedisCartBeforeCheckout($idUser);
        $carrito = self::obtenerCarrito($idUser);

        if (($carrito['exito'] ?? false) !== true || empty($carrito['carrito'])) {
            return ['redirect' => BASE_URL . 'catalogo'];
        }

        return [
            'carrito_items' => $carrito['carrito'],
            'resumen' => $carrito['resumen'],
            'departamentos' => $db->ejecutar('obtenerDepartamentos')->fetchAll(PDO::FETCH_ASSOC),
            'cliente' => self::obtenerClienteCheckout($idUser),
            'cliente_envio' => self::obtenerDireccionCliente($idUser),
            'direccion_guardada' => !empty(self::obtenerDireccionCliente($idUser)),
            'epayco_public_key' => self::getRequiredEnv('EPAYCO_PUBLIC_KEY'),
            'referencia_pago' => 'VIVA-' . time() . '-' . $idUser,
        ];
    }

    private static function obtenerDireccionCliente(int $idUser): ?array
    {
        $stmtCliente = Database::getInstance()->ejecutar('obtenerDireccionCliente', [':id_user' => $idUser]);

        return $stmtCliente->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private static function obtenerClienteCheckout(int $idUser): ?array
    {
        $stmtCliente = Database::getInstance()->ejecutar('obtenerClienteConDireccion', [':id_user' => $idUser]);

        return $stmtCliente->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private static function getRequiredEnv(string $key): string
    {
        if (!isset($_ENV[$key]) || trim((string) $_ENV[$key]) === '') {
            throw new RuntimeException($key . ' no está configurada.');
        }

        return trim((string) $_ENV[$key]);
    }
}
