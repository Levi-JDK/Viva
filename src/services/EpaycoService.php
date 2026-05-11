<?php

require_once __DIR__ . '/../functions/auth_helper.php';
require_once __DIR__ . '/../functions/database.php';
require_once __DIR__ . '/../functions/error_handler.php';

class EpaycoService
{
    private static function getRequiredEnv(string $key): string
    {
        if (!isset($_ENV[$key]) || trim((string) $_ENV[$key]) === '') {
            throw new RuntimeException($key . ' no está configurada.');
        }

        return trim((string) $_ENV[$key]);
    }

    public static function validarReferencia(?string $refPayco): ?array
    {
        $refPayco = trim((string) $refPayco);
        if ($refPayco === '') {
            return null;
        }

        $url = 'https://secure.epayco.co/validation/v1/reference/' . rawurlencode($refPayco);

        $options = [
            'http' => [
                'method' => 'GET',
                'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)\r\n",
                'ignore_errors' => true,
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ];

        try {
            $response = file_get_contents($url, false, stream_context_create($options));
            if (!$response) {
                return null;
            }

            $decoded = json_decode($response, true);
            return is_array($decoded) ? $decoded : null;
        } catch (Throwable $e) {
            error_log('[ePayco] validarReferencia: ' . $e->getMessage());

            return null;
        }
    }

    public static function obtenerIdUsuarioDesdeFactura(string $invoiceId): ?string
    {
        $invoiceId = trim($invoiceId);
        if (!preg_match('/^VIVA-\d+-(\d+)$/', $invoiceId, $matches)) {
            return null;
        }

        return $matches[1];
    }

    public static function autenticarUsuarioRecuperado(?string $idUserRecuperado): void
    {
        if (!$idUserRecuperado || AuthHelper::verifyToken()) {
            return;
        }

        $token = AuthHelper::generateToken(['id_user' => $idUserRecuperado]);
        AuthHelper::setAuthCookie($token);
        $_COOKIE['access_token'] = $token;
    }

    public static function procesarWebhook(array $payload, array $server = []): array
    {
        if (!self::validarFirmaWebhook($payload, $server)) {
            error_log('[ePayco] Webhook rechazado: firma inválida o ausente.');

            return [
                'success' => false,
                'status' => 400,
                'message' => 'Firma inválida.',
            ];
        }

        $refPayco = trim((string) ($payload['x_ref_payco'] ?? ''));
        $transactionId = trim((string) ($payload['x_transaction_id'] ?? ''));
        $response = trim((string) ($payload['x_response'] ?? ''));
        $codResponse = (int) ($payload['x_cod_response'] ?? 0);

        if ($refPayco === '' && $transactionId === '') {
            error_log('[ePayco] Webhook sin referencia ni transacción.');

            return [
                'success' => false,
                'status' => 400,
                'message' => 'Referencia inválida.',
            ];
        }

        try {
            $db = Database::getInstance();
            $estado = $response !== '' ? $response : ($codResponse === 1 ? 'Aceptada' : 'Rechazada');

            $stmt = $db->connection->prepare(
                "UPDATE tab_enc_fact
                 SET epayco_estado = :estado
                 WHERE epayco_ref = :ref OR epayco_txn = :txn"
            );
            $stmt->execute([
                ':estado' => $estado,
                ':ref' => $refPayco,
                ':txn' => $transactionId,
            ]);

            return [
                'success' => true,
                'status' => 200,
                'message' => $codResponse === 1 ? 'Pago aprobado.' : 'Pago no aprobado.',
                'updated' => $stmt->rowCount(),
            ];
        } catch (Throwable $e) {
            ErrorHandler::handle($e, 'epayco.webhook');
            error_log('[ePayco] procesarWebhook: ' . $e->getMessage());

            return [
                'success' => false,
                'status' => 500,
                'message' => 'No se pudo procesar el webhook.',
            ];
        }
    }

    private static function validarFirmaWebhook(array $payload, array $server): bool
    {
        $signature = trim((string) ($payload['x_signature'] ?? $server['HTTP_X_SIGNATURE'] ?? ''));
        if ($signature === '') {
            return false;
        }

        try {
            $privateKey = self::getRequiredEnv('EPAYCO_PRIVATE_KEY');
            $custId = self::getRequiredEnv('EPAYCO_CUST_ID');
        } catch (Throwable $e) {
            error_log('[ePayco] validarFirmaWebhook: ' . $e->getMessage());

            return false;
        }

        $raw = $custId
            . $privateKey
            . (string) ($payload['x_ref_payco'] ?? '')
            . (string) ($payload['x_transaction_id'] ?? '')
            . (string) ($payload['x_amount'] ?? '')
            . (string) ($payload['x_currency_code'] ?? '');

        return hash_equals(hash('sha256', $raw), $signature);
    }
}
