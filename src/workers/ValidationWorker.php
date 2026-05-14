<?php
/**
 * Worker dedicado para validación asíncrona de productos con IA.
 *
 * Uso: php src/workers/ValidationWorker.php
 */

require_once __DIR__ . '/../functions/database.php';
require_once __DIR__ . '/../exceptions/AIProviderException.php';
require_once __DIR__ . '/Config/RedisConfig.php';
require_once __DIR__ . '/Jobs/ProductValidationJob.php';

use Predis\Client as Redis;

class ValidationWorker {
    private Redis $redis;

    private int $maxRetries = 3;
    private array $backoff = [1, 5, 30, 60];

    private const QUEUE_VALIDACION = 'viva:cola:validacion';
    private const QUEUE_DLQ = 'viva:cola:deadletter';

    public function __construct() {
        $this->redis = RedisConfig::getConnection();
        $this->log("[*] ValidationWorker inicializado");
    }

    public function run(): void {
        $this->log("[*] ValidationWorker iniciado, esperando validaciones...");
        $this->log("[*] Presiona Ctrl+C para detener");

        while (true) {
            try {
                $result = $this->redis->brpop([self::QUEUE_VALIDACION], 0);

                if ($result) {
                    $cola = is_array($result) ? reset($result) : $result[0] ?? $result;
                    $mensaje = is_array($result) ? end($result) : $result[1] ?? null;

                    if ($mensaje === null && is_array($result)) {
                        $mensaje = array_values($result)[1] ?? null;
                    }

                    if ($mensaje) {
                        $this->log("[*] Mensaje recibido de: $cola");
                        $this->procesarValidacion((string) $mensaje);
                    }
                }
            } catch (\Throwable $e) {
                $this->log("[X] Error en loop principal: " . $e->getMessage());
                // Pequeña pausa para evitar spin loop si Redis está caído
                sleep(5);
            }
        }
    }

    private function procesarValidacion(string $mensaje): void {
        try {
            $payload = json_decode($mensaje, true);
            if (!is_array($payload)) {
                $this->log("[X] Mensaje inválido (no es JSON): " . substr($mensaje, 0, 100));
                return;
            }

            $productId = (int) ($payload['product_id'] ?? 0);
            $this->log("[*] Procesando validación producto_id: $productId");
            $job = ProductValidationJob::fromRedis($payload);

            for ($intento = 1; $intento <= $this->maxRetries; $intento++) {
                try {
                    $job->handle();
                    $this->log("[✓] Validación producto_id $productId procesada correctamente");
                    return;
                } catch (AIProviderException $e) {
                    $this->log("[!] Validación IA pendiente para producto $productId: " . $e->getMessage());
                    return;
                } catch (\Throwable $e) {
                    $this->log("[!] Intento $intento fallido para validación producto_id $productId: " . $e->getMessage());
                    if ($intento < $this->maxRetries) {
                        sleep($this->backoff[$intento] ?? 1);
                    }
                }
            }

            $this->moverADLQ('validacion', $productId, $payload);
        } catch (\Throwable $e) {
            $this->log("[X] Error inesperado en procesarValidacion: " . $e->getMessage());
        }
    }

    private function moverADLQ(string $tipo, int $id, array $data): void {
        $payload = json_encode([
            'tipo' => $tipo,
            'id' => $id,
            'data' => $data,
            'fallido_en' => date('Y-m-d H:i:s'),
            'intentos' => $this->maxRetries
        ]);
        $this->redis->lpush(self::QUEUE_DLQ, $payload);
        $this->log("[X] Movido a DLQ: $tipo:$id");
    }

    private function log(string $mensaje): void {
        echo date('Y-m-d H:i:s') . ' ' . $mensaje . PHP_EOL;
    }
}

if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($argv[0] ?? '')) {
    $worker = new ValidationWorker();
    $worker->run();
}
