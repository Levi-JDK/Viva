<?php

require_once __DIR__ . '/../functions/database.php';
require_once __DIR__ . '/PuntoEnvioService.php';

class ShippingOrchestrator
{
    private static function getRequiredEnv(string $key): string
    {
        if (!isset($_ENV[$key]) || trim((string) $_ENV[$key]) === '') {
            throw new RuntimeException($key . ' no está configurada.');
        }

        return trim((string) $_ENV[$key]);
    }

    public static function crearEnvio(int $idFactura, int $idUserPago, array $transaccion, array $datosFactura): ?string
    {
        $db = Database::getInstance();

        try {
            $cliente = $db->ejecutar('obtenerClienteConDireccion', [':id_user' => $idUserPago])->fetch(PDO::FETCH_ASSOC);
            if (!$cliente) {
                throw new RuntimeException('No se encontraron datos del cliente para factura=' . $idFactura);
            }

            $sender = self::getOrCreatePerson([
                'nombre' => 'VIVA Marketplace',
                'tipo_documento' => 'NIT',
                'documento' => self::getRequiredEnv('VIVA_NIT'),
                'email' => self::getRequiredEnv('MAIL_FROM_ADDRESS'),
                'telefono' => $_ENV['VIVA_PHONE'] ?? '',
                'direccion' => self::getRequiredEnv('VIVA_ADDRESS'),
                'id_departamento' => 2,
                'id_ciudad' => 1,
            ]);

            $recipient = self::getOrCreatePerson([
                'nombre' => $cliente['nom_client'] ?? '',
                'tipo_documento' => self::mapTipoDocumento($cliente['id_tipo_doc'] ?? null),
                'documento' => self::sanitizeDocument($cliente['nro_doc'] ?? '', $idUserPago),
                'email' => $cliente['mail_client'] ?? '',
                'telefono' => self::sanitizePhone($cliente['tel_client'] ?? '', $idUserPago),
                'direccion' => $datosFactura['dir_envio_fact'] ?? $cliente['dir_envio'] ?? '',
                'id_departamento' => $datosFactura['id_dpto_fact'] ?? $cliente['id_departamento'] ?? null,
                'id_ciudad' => $datosFactura['id_ciudad_fact'] ?? $cliente['id_ciudad'] ?? null,
            ]);

            $senderId = self::extractPuntoEnvioId($sender, ['id_person', 'id', 'person_id']);
            $recipientId = self::extractPuntoEnvioId($recipient, ['id_person', 'id', 'person_id']);

            if ($senderId === null || $recipientId === null) {
                throw new RuntimeException('PuntoEnvio no retornó IDs de remitente/destinatario.');
            }

            $package = PuntoEnvioService::createPackage([
                'id_remitente' => $senderId,
                'id_destinatario' => $recipientId,
                'id_orden_ecommerce' => $idFactura,
                'origin_state_id' => '2',
                'origin_city_id' => '1',
                'origin_street' => self::getRequiredEnv('VIVA_ADDRESS'),
                'id_departamento' => $datosFactura['id_dpto_fact'],
                'id_ciudad' => $datosFactura['id_ciudad_fact'],
                'direccion_destino' => $datosFactura['dir_envio_fact'],
            ]);

            $numGuia = self::extractPuntoEnvioId($package, ['id_package', 'num_guia', 'id_envio', 'id']);
            if ($numGuia === null) {
                throw new RuntimeException('PuntoEnvio no retornó id_package.');
            }

            $db->ejecutar('actualizarGuiaFactura', [
                ':num_guia' => (string) $numGuia,
                ':id_factura' => $idFactura,
            ]);

            return null;
        } catch (Throwable $e) {
            error_log('[ShippingOrchestrator] crearEnvio: ' . $e->getMessage());
            $db->ejecutar('actualizarEstadoEnvio', [
                ':id_fact' => $idFactura,
                ':estado' => 'ERROR_ENVIO',
            ]);

            return 'Tu pago fue aprobado, pero no pudimos generar la guía de envío. Nuestro equipo revisará el pedido.';
        }
    }

    public static function getCheckpoints(string $numGuia): array
    {
        return PuntoEnvioService::getCheckpoints($numGuia);
    }

    public static function confirmarEntrega(string $numGuia): bool
    {
        return PuntoEnvioService::confirmarEntrega($numGuia);
    }

    public static function extractPuntoEnvioId(?array $response, array $keys): ?string
    {
        if (!$response) {
            return null;
        }

        foreach ($keys as $key) {
            if (isset($response[$key]) && (string) $response[$key] !== '') {
                return (string) $response[$key];
            }
        }

        if (isset($response['data']) && is_array($response['data'])) {
            foreach ($keys as $key) {
                if (isset($response['data'][$key]) && (string) $response['data'][$key] !== '') {
                    return (string) $response['data'][$key];
                }
            }
        }

        return null;
    }

    public static function mapTipoDocumento($idTipoDoc): string
    {
        return [1 => 'CC', 2 => 'NIT', 3 => 'CE', 4 => 'PP'][(int) $idTipoDoc] ?? 'CC';
    }

    private static function getOrCreatePerson(array $personData): ?array
    {
        $personId = trim((string) ($personData['documento'] ?? $personData['id'] ?? ''));
        if ($personId === '') {
            return null;
        }

        $existingPerson = PuntoEnvioService::getPerson($personId);
        if ($existingPerson !== null) {
            return $existingPerson;
        }

        $createdPerson = PuntoEnvioService::createPerson($personData);
        if ($createdPerson !== null) {
            return $createdPerson;
        }

        return PuntoEnvioService::getPerson($personId);
    }

    private static function sanitizeDocument($document, int $idUserPago): string
    {
        $document = trim((string) $document);

        return ($document === '' || $document === '123' || strpos($document, '*') !== false)
            ? (string) $idUserPago
            : $document;
    }

    private static function sanitizePhone($phone, int $idUserPago): string
    {
        $phone = trim((string) $phone);

        return ($phone === '' || $phone === '123' || strpos($phone, '*') !== false)
            ? (string) (3000000000 + $idUserPago)
            : $phone;
    }
}
