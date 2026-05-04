<?php
/**
 * Worker Principal para procesamiento asíncrono
 * Usa Predis (biblioteca PHP pura) para conectar a Redis
 * Compatible con Windows, Linux y cualquier servidor con PHP
 * 
 * Uso: php src/workers/Worker.php
 */

require_once __DIR__ . '/../functions/database.php';
require_once __DIR__ . '/../services/RegisterService.php';
require_once __DIR__ . '/Config/RedisConfig.php';
require_once __DIR__ . '/Jobs/ProcessCartJob.php';

use Predis\Client as Redis;

class Worker {
    private Redis $redis;
    
    // Configuración de reintentos
    private int $maxRetries = 3;
    private array $backoff = [1, 5, 30, 60]; // Exponential backoff en segundos
    
    // Constantes de colas
    private const QUEUE_REGISTROS = 'viva:queue:users';
    private const QUEUE_CARRITO = 'viva:cola:carrito';
    private const QUEUE_DLQ = 'viva:cola:deadletter'; // Dead Letter Queue
    
    public function __construct() {
        $this->redis = RedisConfig::getConnection();
        
        $this->log("[*] Worker inicializado");
    }
    
    /**
     * Iniciar el worker - escucha múltiples colas
     */
    public function run(): void {
        $this->log("[*] Worker iniciado, esperando mensajes...");
        $this->log("[*] Presiona Ctrl+C para detener");
        
        // Array de colas a escuchar
        $colas = [self::QUEUE_REGISTROS, self::QUEUE_CARRITO];
        
        while (true) {
            // Usar brpop en múltiples colas (Predis compatible)
            // Predis devuelve: [0 => 'cola: nombre', 1 => 'mensaje']
            $result = $this->redis->brpop($colas, 0);
            
            if ($result) {
                // Predis devuelve un array asociativo o indexado según la versión
                // Normalizar el resultado
                $cola = is_array($result) ? reset($result) : $result[0] ?? $result;
                $mensaje = is_array($result) ? end($result) : $result[1] ?? null;
                
                if ($mensaje === null && is_array($result)) {
                    //另一種格式
                    $mensaje = array_values($result)[1] ?? null;
                }
                
                if ($mensaje) {
                    $this->log("[*] Mensaje recibido de: $cola");
                    
                    try {
                        match ($cola) {
                            self::QUEUE_REGISTROS => $this->procesarRegistro((int) $mensaje),
                            self::QUEUE_CARRITO => $this->procesarCarrito((string) $mensaje),
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
    
    /**
     * Procesar registro de usuario
     */
    private function procesarRegistro(int $userId): void {
        $this->log("[*] Procesando usuario ID: $userId");
        
        // Obtener datos de Redis (Predis usa el mismo método)
        $userData = $this->redis->hgetall(RedisConfig::user($userId));
        
        if (empty($userData)) {
            $this->log("[!] No se encontraron datos para usuario $userId");
            return;
        }
        
        // Intentar con reintentos
        for ($intento = 1; $intento <= $this->maxRetries; $intento++) {
            try {
                $this->ejecutarInsertUsuario($userData);
                
                // Éxito: limpiar Redis
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
        
        // Si todos los intentos fallan: mover a Dead Letter Queue
        $this->moverADLQ('registro', $userId, $userData);
    }
    
    /**
     * Procesar carrito de compras
     */
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
    
    /**
     * Ejecutar insert de usuario en PostgreSQL
     */
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
    
    /**
     * Mover mensaje fallido a Dead Letter Queue
     */
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

        // Compensación de Fallos para registros fallidos (Patovica)
        if ($tipo === 'registro') {
            try {
                $prefix = RedisConfig::getPrefix();
                // Bloquear temporalmente el login de este ID
                $this->redis->setex($prefix . 'jwt_revoked:' . $id, 86400, 1);
                
                // Liberar el email para que el usuario pueda intentar registrarse nuevamente
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
    
    /**
     * Logging con timestamp
     */
    private function log(string $mensaje): void {
        echo date('Y-m-d H:i:s') . ' ' . $mensaje . PHP_EOL;
    }
}

// Ejecutar worker si se llama directamente
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($argv[0] ?? '')) {
    $worker = new Worker();
    $worker->run();
}
