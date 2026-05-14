<?php
/**
 * Worker dedicado para procesamiento asíncrono de registros.
 * Usa Predis (biblioteca PHP pura) para conectar a Redis
 * Compatible con Windows, Linux y cualquier servidor con PHP
 * 
 * Uso: php src/workers/Worker.php
 */

require_once __DIR__ . '/../functions/database.php';
require_once __DIR__ . '/../services/RegisterService.php';
require_once __DIR__ . '/Config/RedisConfig.php';

use Predis\Client as Redis;

class Worker {
    private Redis $redis;
    
    private int $maxRetries = 3;
    private array $backoff = [1, 5, 30, 60];
    
    private const QUEUE_REGISTRO = 'viva:cola:registro';
    private const QUEUE_DLQ = 'viva:cola:deadletter';
    
    public function __construct() {
        $this->redis = RedisConfig::getConnection();
        $this->log("[*] Worker inicializado");
    }
    
    public function run(): void {
        $this->log("[*] Worker de registros iniciado, esperando mensajes...");
        $this->log("[*] Presiona Ctrl+C para detener");
        
        $colas = [self::QUEUE_REGISTRO];
        
        while (true) {
            $result = $this->redis->brpop($colas, 0);
            
            if ($result) {
                $cola = is_array($result) ? reset($result) : $result[0] ?? $result;
                $mensaje = is_array($result) ? end($result) : $result[1] ?? null;
                
                if ($mensaje === null && is_array($result)) {
                    $mensaje = array_values($result)[1] ?? null;
                }
                
                if ($mensaje) {
                    $this->log("[*] Mensaje recibido de: $cola");
                    
                    try {
                        match ($cola) {
                            self::QUEUE_REGISTRO => $this->procesarRegistro((int) $mensaje),
                            default => $this->log("[!] Cola desconocida: $cola")
                        };
                    } catch (Exception $e) {
                        $this->log("[ERROR] Excepción: " . $e->getMessage());
                        throw $e;
                    }
                }
            }
        }
    }
    
    private function procesarRegistro(int $userId): void {
        $this->log("[*] Procesando usuario ID: $userId");
        
        $userData = $this->redis->hgetall(RedisConfig::user($userId));
        
        if (empty($userData)) {
            $this->log("[!] No se encontraron datos para usuario $userId");
            return;
        }
        
        for ($intento = 1; $intento <= $this->maxRetries; $intento++) {
            try {
                $this->ejecutarInsertUsuario($userData);
                $this->redis->del(RedisConfig::user($userId));
                $this->log("[✓] Usuario $userId insertado correctamente");
                return;
            } catch (\Throwable $e) {
                $this->log("[!] Intento $intento fallido para usuario $userId: " . $e->getMessage());
                if ($intento < $this->maxRetries) {
                    sleep($this->backoff[$intento] ?? 1);
                }
            }
        }
        
        $this->moverADLQ('registro', $userId, $userData);
    }
    
    private function ejecutarInsertUsuario(array $userData): void {
        $db = Database::getInstance();
        \RegisterService::registrarUsuarioEnBaseDatos(
            $db,
            $userData['nombre'],
            $userData['apellido'],
            $userData['email'],
            $userData['password']
        );
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
        if ($tipo === 'registro') {
            try {
                $prefix = RedisConfig::getPrefix();
                $this->redis->setex($prefix . 'jwt_revoked:' . $id, 86400, 1);
                if (isset($data['email'])) {
                    $this->redis->srem($prefix . 'emails:registrados', $data['email']);
                    $this->redis->hdel($prefix . 'email_to_id', $data['email']);
                    $this->log("[*] Compensación: Liberado email " . $data['email'] . " y JWT revocado.");
                }
            } catch (\Exception $e) {
                throw $e;
            }
        }
    }
    
    private function log(string $mensaje): void {
        echo date('Y-m-d H:i:s') . ' ' . $mensaje . PHP_EOL;
    }
}

if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($argv[0] ?? '')) {
    $worker = new Worker();
    $worker->run();
}
