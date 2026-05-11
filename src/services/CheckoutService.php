<?php

require_once __DIR__ . '/../functions/auth_helper.php';
require_once __DIR__ . '/../functions/database.php';
require_once __DIR__ . '/../functions/error_handler.php';
require_once __DIR__ . '/EpaycoService.php';
require_once __DIR__ . '/InvoiceService.php';
require_once __DIR__ . '/ShippingOrchestrator.php';

class CheckoutService
{
    public static function procesarRespuestaPago(?string $refPayco): array
    {
        if (!$refPayco) return ['transaccion' => null, 'error' => 'No se recibió ninguna referencia de pago.'];

        try {
            $validacion = EpaycoService::validarReferencia($refPayco);
            if (!$validacion) return ['transaccion' => null, 'error' => 'Fallo de conexión con el servidor de pagos.'];
            if (($validacion['success'] ?? false) !== true || empty($validacion['data'])) {
                return ['transaccion' => null, 'error' => 'No se pudo validar la referencia con ePayco.'];
            }

            $transaccion = $validacion['data'];
            $idUserRecuperado = EpaycoService::obtenerIdUsuarioDesdeFactura($transaccion['x_id_invoice'] ?? '');
            EpaycoService::autenticarUsuarioRecuperado($idUserRecuperado);

            $envioError = (int) ($transaccion['x_cod_response'] ?? 0) === 1
                ? self::procesarFacturacionExitosa($transaccion, $idUserRecuperado)
                : null;

            return ['transaccion' => self::agregarDatosEnvio($transaccion, $envioError), 'error' => null];
        } catch (Throwable $e) {
            ErrorHandler::handle($e, 'checkout.procesarRespuestaPago');
            throw $e;
        }
    }

    public static function obtenerDatosCheckout(int $idUser): array
    {
        return InvoiceService::obtenerDatosVistaCheckout($idUser);
    }

    private static function procesarFacturacionExitosa(array $transaccion, ?string $idUserRecuperado): ?string
    {
        $userData = AuthHelper::verifyToken();
        $idUserPago = $userData ? $userData->id_user : $idUserRecuperado;
        if (!$idUserPago) {
            error_log('[CheckoutService] No se pudo resolver id_user para facturación.');
            return null;
        }

        InvoiceService::actualizarClienteEpayco((int) $idUserPago, $transaccion);
        $datosFactura = InvoiceService::obtenerDatosFactura((int) $idUserPago);
        $carrito = InvoiceService::obtenerCarrito((int) $idUserPago);
        if (($carrito['exito'] ?? false) !== true || empty($carrito['carrito'])) {
            error_log('[CheckoutService] Carrito vacío o exito=false, no se factura.');
            return null;
        }

        $idFactura = InvoiceService::facturarCarrito((int) $idUserPago, $transaccion, $datosFactura, $carrito['carrito']);
        return $idFactura === null ? null : ShippingOrchestrator::crearEnvio($idFactura, (int) $idUserPago, $transaccion, $datosFactura);
    }

    private static function agregarDatosEnvio(array $transaccion, ?string $envioError): array
    {
        $stmt = Database::getInstance()->ejecutar('obtenerEnvioPorReferencia', [':ref' => $transaccion['x_ref_payco'] ?? '']);
        $envio = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        if ($envio && !empty($envio['num_guia'])) $transaccion['num_guia'] = $envio['num_guia'];
        if ($envio && !empty($envio['envio_estado'])) $transaccion['envio_estado'] = $envio['envio_estado'];
        if ($envioError !== null || (($transaccion['envio_estado'] ?? null) === 'ERROR_ENVIO')) {
            $transaccion['envio_error'] = $envioError ?? 'Tu pago fue aprobado, pero no pudimos generar la guía de envío. Nuestro equipo revisará el pedido.';
        }
        return $transaccion;
    }
}
