<?php

require_once __DIR__ . '/../functions/auth_helper.php';
require_once __DIR__ . '/../functions/database.php';

class CheckoutService
{
    private static function getRequiredEnv(string $key): string
    {
        if (!isset($_ENV[$key]) || trim((string) $_ENV[$key]) === '') {
            throw new RuntimeException($key . ' no está configurada.');
        }

        return trim((string) $_ENV[$key]);
    }

    public static function procesarRespuestaPago(?string $refPayco): array
    {
        $transaccion = null;
        $error = null;

        if (!$refPayco) {
            return [
                'transaccion' => null,
                'error' => 'No se recibió ninguna referencia de pago.',
            ];
        }

        try {
            $transaccion = self::validarReferenciaEpayco($refPayco);

            if (!$transaccion) {
                return [
                    'transaccion' => null,
                    'error' => 'Fallo de conexión con el servidor de pagos.',
                ];
            }

            if (($transaccion['success'] ?? false) !== true || empty($transaccion['data'])) {
                return [
                    'transaccion' => null,
                    'error' => 'No se pudo validar la referencia con ePayco.',
                ];
            }

            $transaccion = $transaccion['data'];
            $idUserRecuperado = self::obtenerIdUsuarioDesdeFactura($transaccion['x_id_invoice'] ?? '');

            self::autenticarUsuarioRecuperado($idUserRecuperado);

            if ((int) ($transaccion['x_cod_response'] ?? 0) === 1) {
                self::procesarFacturacionExitosa($transaccion, $idUserRecuperado);
            }
        } catch (Exception $e) {
            throw $e;
        }

        return [
            'transaccion' => $transaccion,
            'error' => $error,
        ];
    }

    public static function obtenerDatosCheckout(int $idUser): array
    {
        $db = Database::getInstance();
        $carrito = self::obtenerCarrito($db, $idUser);

        if (($carrito['exito'] ?? false) !== true || empty($carrito['carrito'])) {
            return [
                'redirect' => BASE_URL . 'catalogo',
            ];
        }

        $departamentos = $db->ejecutar('obtenerDepartamentos')->fetchAll(PDO::FETCH_ASSOC);
        $clienteEnvio = self::obtenerDireccionCliente($db, $idUser);
        $epaycoPublicKey = self::getRequiredEnv('EPAYCO_PUBLIC_KEY');

        return [
            'carrito_items' => $carrito['carrito'],
            'resumen' => $carrito['resumen'],
            'departamentos' => $departamentos,
            'cliente_envio' => $clienteEnvio,
            'direccion_guardada' => !empty($clienteEnvio),
            'epayco_public_key' => $epaycoPublicKey,
            'referencia_pago' => 'VIVA-' . time() . '-' . $idUser,
        ];
    }

    private static function validarReferenciaEpayco(string $refPayco): ?array
    {
        $url = 'https://secure.epayco.co/validation/v1/reference/' . $refPayco;

        $options = [
            'http' => [
                'method' => 'GET',
                'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)\r\n",
                'ignore_errors' => true,
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ];

        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);

        if (!$response) {
            return null;
        }

        return json_decode($response, true);
    }

    private static function obtenerIdUsuarioDesdeFactura(string $invoiceId): ?string
    {
        $partesFactura = explode('-', $invoiceId);

        return $partesFactura[2] ?? null;
    }

    private static function autenticarUsuarioRecuperado(?string $idUserRecuperado): void
    {
        if (!$idUserRecuperado || AuthHelper::verifyToken()) {
            return;
        }

        $token = AuthHelper::generateToken(['id_user' => $idUserRecuperado]);
        AuthHelper::setAuthCookie($token);
        $_COOKIE['access_token'] = $token;
    }

    private static function procesarFacturacionExitosa(array $transaccion, ?string $idUserRecuperado): void
    {
        $db = Database::getInstance();
        $userData = AuthHelper::verifyToken();
        $idUserPago = $userData ? $userData->id_user : $idUserRecuperado;

        if (!$idUserPago) {
            error_log('[CheckoutService] No se pudo resolver id_user para facturación.');

            return;
        }

        self::actualizarClienteEpayco($db, (int) $idUserPago, $transaccion);

        $datosFactura = self::obtenerDatosFactura($db, (int) $idUserPago);
        $carrito = self::obtenerCarrito($db, (int) $idUserPago);

        error_log('[Factura-DEBUG] id_user_pago=' . $idUserPago);
        error_log('[Factura-DEBUG] carrito_exito=' . (($carrito['exito'] ?? false) ? 'true' : 'false'));
        error_log('[Factura-DEBUG] carrito_items=' . count($carrito['carrito'] ?? []));

        if (($carrito['exito'] ?? false) !== true || empty($carrito['carrito'])) {
            error_log('[Factura-DEBUG] Carrito vacío o exito=false, no se factura');

            return;
        }

        self::facturarCarrito($db, (int) $idUserPago, $transaccion, $datosFactura, $carrito['carrito']);
    }

    private static function actualizarClienteEpayco(Database $db, int $idUserPago, array $transaccion): void
    {
        try {
            $tipoDocMap = ['CC' => 1, 'NIT' => 2, 'CE' => 3, 'PP' => 4];
            $db->ejecutar('actualizarClienteEpayco', [
                ':id_user' => $idUserPago,
                ':id_client' => $transaccion['x_customer_document'] ?? (string) $idUserPago,
                ':id_tipo_doc' => $tipoDocMap[$transaccion['x_customer_doctype'] ?? ''] ?? null,
                ':tel' => $transaccion['x_customer_phone'] ?? null,
                ':ref' => $transaccion['x_ref_payco'] ?? null,
                ':txn' => $transaccion['x_transaction_id'] ?? null,
                ':banco' => $transaccion['x_bank_name'] ?? null,
                ':cod_resp' => $transaccion['x_cod_response'] ?? null,
            ]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    private static function obtenerDatosFactura(Database $db, int $idUserPago): array
    {
        $datosFactura = [
            'dir_envio_fact' => null,
            'id_dpto_fact' => 11,
            'id_ciudad_fact' => 11001,
        ];

        try {
            $rowDir = $db->ejecutar('obtenerDireccionCliente', [':id_user' => $idUserPago])->fetch(PDO::FETCH_ASSOC);

            if (!$rowDir) {
                return $datosFactura;
            }

            $datosFactura['id_dpto_fact'] = $rowDir['id_departamento'];
            $datosFactura['id_ciudad_fact'] = $rowDir['id_ciudad'];
            $datosFactura['dir_envio_fact'] = trim(($rowDir['dir_envio'] ?? '') . ' ' . ($rowDir['barrio_envio'] ?? ''));
        } catch (Exception $e) {
            throw $e;
        }

        return $datosFactura;
    }

    private static function obtenerCarrito(Database $db, int $idUser): array
    {
        $stmt = $db->ejecutar('gestionarCarrito', [
            ':id_user' => $idUser,
            ':accion' => 'obtener',
            ':id_producto' => null,
            ':cantidad' => null,
        ]);

        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        return json_decode($fila['fun_carrito'] ?? $fila[array_key_first($fila)], true);
    }

    private static function obtenerDireccionCliente(Database $db, int $idUser): ?array
    {
        try {
            $stmtCliente = $db->ejecutar('obtenerDireccionCliente', [':id_user' => $idUser]);

            return $stmtCliente->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            throw $e;
        }
    }

    private static function facturarCarrito(Database $db, int $idUserPago, array $transaccion, array $datosFactura, array $itemsCarrito): void
    {
        $idsProd = [];
        $cantidades = [];

        foreach ($itemsCarrito as $item) {
            $idsProd[] = (int) $item['id_producto'];
            $cantidades[] = (int) $item['cantidad'];
        }

        error_log('[Factura-DEBUG] id_user=' . $idUserPago);
        error_log('[Factura-DEBUG] ids_prod=' . implode(',', $idsProd));
        error_log('[Factura-DEBUG] dpto=' . $datosFactura['id_dpto_fact'] . ' ciudad=' . $datosFactura['id_ciudad_fact']);

        try {
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
            error_log('[Factura-DEBUG] fun_facturar resultado=' . var_export($idFactura, true));

            if (!$idFactura) {
                error_log('[CheckoutResponse] fun_facturar devolvio NULL');

                return;
            }

            $db->ejecutar('gestionarCarrito', [
                ':id_user' => $idUserPago,
                ':accion' => 'limpiar',
                ':id_producto' => null,
                ':cantidad' => null,
            ]);
        } catch (PDOException $e) {
            throw $e;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
