<?php
/**
 * Worker dedicado para procesamiento asíncrono de carritos.
 *
 * Uso: php src/workers/CartWorker.php
 */

require_once __DIR__ . '/../functions/database.php';
require_once __DIR__ . '/Config/RedisConfig.php';
require_once __DIR__ . '/Jobs/ProcessCartJob.php';

use Predis\Client as Redis;

class CartWorker {
    private Redis $redis;

    private int $maxRetries = 3;
    private array $backoff = [1, 5, 30, 60];

    private const QUEUE_CARRITO = 'viva:cola:carrito';
    private const QUEUE_DLQ = 'viva:cola:deadletter';

    public function __construct() {
        $this->redis = RedisConfig::getConnection();
        $this->log("[*] CartWorker inicializado");
    }

    public function run(): void {
        $this->log("[*] CartWorker iniciado, esperando carritos...");
        $this->log("[*] Presiona Ctrl+C para detener");

        while (true) {
            $result = $this->redis->brpop([self::QUEUE_CARRITO], 0);

            if ($result) {
                $cola = is_array($result) ? reset($result) : $result[0] ?? $result;
                $mensaje = is_array($result) ? end($result) : $result[1] ?? null;

                if ($mensaje === null && is_array($result)) {
                    $mensaje = array_values($result)[1] ?? null;
                }

                if ($mensaje) {
                    $this->log("[*] Mensaje recibido de: $cola");
                    $this->procesarCarrito((string) $mensaje);
                }
            }
        }
    }

    private function procesarCarrito(string $mensaje): void {
        $payload = json_decode($mensaje, true);
        if (!is_array($payload)) {
            throw new InvalidArgumentException('Mensaje de cola de carrito inválido.');
        }

        $userId = (int) ($payload['user_id'] ?? 0);
        $this->log("[*] Procesando carrito user_id: $userId");
        $job = ProcessCartJob::fromRedis($payload);

        for ($intento = 1; $intento <= $this->maxRetries; $intento++) {
            try {
                $job->handle();
                $this->log("[✓] Carrito user_id $userId procesado correctamente");
                return;
            } catch (\Throwable $e) {
                $this->log("[!] Intento $intento fallido para carrito user_id $userId: " . $e->getMessage());
                if ($intento < $this->maxRetries) {
                    sleep($this->backoff[$intento] ?? 1);
                }
            }
        }

        $this->moverADLQ('carrito', $userId, $payload);
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
    $worker = new CartWorker();
    $worker->run();
}
