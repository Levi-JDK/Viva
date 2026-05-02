<?php

class PuntoEnvioService
{
    private const TIMEOUT_SECONDS = 5;

    private static function getRequiredEnv(string $key): string
    {
        if (!isset($_ENV[$key]) || trim((string) $_ENV[$key]) === '') {
            throw new RuntimeException($key . ' no está configurada.');
        }

        return trim((string) $_ENV[$key]);
    }

    public static function createPerson(array $data): ?array
    {
        self::assertConfigured();

        try {
            $payload = self::normalizePersonPayload($data);
            $response = self::httpRequest('POST', '/persons', $payload);

            if ($response === null && $payload['id'] !== '') {
                return self::getPerson($payload['id']);
            }

            return $response;
        } catch (Throwable $e) {
            error_log('[PuntoEnvio] createPerson: ' . $e->getMessage());

            return null;
        }
    }

    public static function getPerson(string $id): ?array
    {
        self::assertConfigured();

        try {
            $personId = trim($id);
            if ($personId === '') {
                return null;
            }

            return self::httpRequest('GET', '/persons/' . rawurlencode($personId));
        } catch (Throwable $e) {
            error_log('[PuntoEnvio] getPerson: ' . $e->getMessage());

            return null;
        }
    }

    public static function personExists(string $id): bool
    {
        return self::getPerson($id) !== null;
    }

    public static function createPackage(array $data): ?array
    {
        self::assertConfigured();

        try {
            return self::httpRequest('POST', '/envios', self::normalizePackagePayload($data));
        } catch (Throwable $e) {
            error_log('[PuntoEnvio] createPackage: ' . $e->getMessage());

            return null;
        }
    }

    public static function getPackage(string $idPackage): ?array
    {
        self::assertConfigured();

        try {
            return self::httpRequest('GET', '/envios/' . rawurlencode($idPackage));
        } catch (Throwable $e) {
            error_log('[PuntoEnvio] getPackage: ' . $e->getMessage());

            return null;
        }
    }

    public static function getCheckpoints(string $idPackage): array
    {
        self::assertConfigured();

        try {
            $response = self::httpRequest('GET', '/envios/' . rawurlencode($idPackage) . '/checkpoints');

            if (!is_array($response)) {
                return [];
            }

            if (isset($response['checkpoints']) && is_array($response['checkpoints'])) {
                return $response['checkpoints'];
            }

            return array_is_list($response) ? $response : [];
        } catch (Throwable $e) {
            error_log('[PuntoEnvio] getCheckpoints: ' . $e->getMessage());

            return [];
        }
    }

    public static function advancePackageState(string $idPackage, string $state, string $idSucursal, string $obs): bool
    {
        self::assertConfigured();

        try {
            $packageId = trim($idPackage);
            $stateValue = trim($state);
            $sucursalId = trim($idSucursal);

            if ($packageId === '' || $stateValue === '' || $sucursalId === '') {
                return false;
            }

            $response = self::httpRequest('POST', '/packages/' . rawurlencode($packageId) . '/state', [
                'state' => $stateValue,
                'id_sucursal' => $sucursalId,
                'observations' => trim($obs),
            ]);

            return $response !== null;
        } catch (Throwable $e) {
            error_log('[PuntoEnvio] advancePackageState: ' . $e->getMessage());

            return false;
        }
    }

    public static function confirmarEntrega(string $idPackage): bool
    {
        return self::advancePackageState($idPackage, 'ENTREGADO', 'SISTEMA', 'Confirmado por cliente');
    }

    private static function assertConfigured(): void
    {
        self::getRequiredEnv('PUNTOENVIO_API_URL');
    }

    private static function normalizePersonPayload(array $data): array
    {
        $fullName = trim((string) ($data['nombre'] ?? $data['name'] ?? ''));
        $parts = preg_split('/\s+/', $fullName, 2) ?: [];

        return [
            'id' => (string) ($data['documento'] ?? $data['id'] ?? ''),
            'name' => (string) ($data['name'] ?? $parts[0] ?? $fullName),
            'lastname' => (string) ($data['lastname'] ?? $parts[1] ?? ''),
            'mail' => (string) ($data['email'] ?? $data['mail'] ?? ''),
            'tel' => (string) ($data['phone'] ?? $data['telefono'] ?? $data['tel'] ?? ''),
        ];
    }

    private static function normalizePackagePayload(array $data): array
    {
        $payload = [
            'sender_id' => (string) ($data['sender_id'] ?? $data['id_remitente'] ?? ''),
            'recipient_id' => (string) ($data['recipient_id'] ?? $data['id_destinatario'] ?? ''),
            'origin_state_id' => (string) ($data['origin_state_id'] ?? '2'),
            'origin_city_id' => (string) ($data['origin_city_id'] ?? '1'),
            'origin_street' => (string) ($data['origin_street'] ?? self::getRequiredEnv('VIVA_ADDRESS')),
            'dest_state_id' => (string) ($data['dest_state_id'] ?? $data['id_departamento'] ?? ''),
            'dest_city_id' => (string) ($data['dest_city_id'] ?? $data['id_ciudad'] ?? ''),
            'destination_street' => (string) ($data['destination_street'] ?? $data['direccion_destino'] ?? ''),
            'id_orden_ecommerce' => (string) ($data['id_orden_ecommerce'] ?? ''),
        ];

        if (array_key_exists('peso', $data) && $data['peso'] !== null && $data['peso'] !== '') {
            $payload['peso'] = (string) $data['peso'];
        }

        if (array_key_exists('dimensiones', $data) && $data['dimensiones'] !== null && $data['dimensiones'] !== '') {
            $payload['dimensiones'] = (string) $data['dimensiones'];
        }

        return $payload;
    }

    private static function httpRequest(string $method, string $path, ?array $body = null): ?array
    {
        $baseUrl = rtrim(self::getRequiredEnv('PUNTOENVIO_API_URL'), '/');
        $url = $baseUrl . $path;
        $ch = curl_init($url);

        if ($ch === false) {
            throw new RuntimeException('No se pudo inicializar cURL.');
        }

        $headers = ['Accept: application/json'];
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_TIMEOUT => self::TIMEOUT_SECONDS,
            CURLOPT_CONNECTTIMEOUT => self::TIMEOUT_SECONDS,
        ];

        if ($body !== null) {
            $jsonBody = json_encode($body, JSON_UNESCAPED_UNICODE);
            if ($jsonBody === false) {
                throw new RuntimeException('No se pudo serializar el cuerpo JSON.');
            }

            $headers[] = 'Content-Type: application/json';
            $options[CURLOPT_POSTFIELDS] = $jsonBody;
        }

        $options[CURLOPT_HTTPHEADER] = $headers;
        curl_setopt_array($ch, $options);

        $rawResponse = curl_exec($ch);
        $curlError = curl_error($ch);
        $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($rawResponse === false) {
            throw new RuntimeException($curlError ?: 'Error HTTP desconocido.');
        }

        if ($statusCode >= 400) {
            error_log('[PuntoEnvio] httpRequest: HTTP ' . $statusCode . ' en ' . $method . ' ' . $path);

            return null;
        }

        if (trim((string) $rawResponse) === '') {
            return [];
        }

        $decoded = json_decode((string) $rawResponse, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Respuesta JSON inválida.');
        }

        return $decoded;
    }
}
