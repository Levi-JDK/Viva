<?php
/**
 * Worker Principal para procesamiento asíncrono
 * Usa Predis (biblioteca PHP pura) para conectar a Redis
 * Compatible con Windows, Linux y cualquier servidor con PHP
 * 
 * Uso: php src/workers/Worker.php
 */

require_once __DIR__ . '/Config/RedisConfig.php';
require_once __DIR__ . '/Jobs/RegisterUserJob.php';
require_once __DIR__ . '/Jobs/ProcessCartJob.php';

use Predis\Client as Redis;

class Worker {
    private Redis $redis;
    private PDO $pdo;
    
    // Configuración de reintentos
    private int $maxRetries = 3;
    private array $backoff = [1, 5, 30, 60]; // Exponential backoff en segundos
    
    // Constantes de colas
    private const QUEUE_REGISTROS = 'viva:cola:registros';
    private const QUEUE_CARRITO = 'viva:cola:carrito';
    private const QUEUE_DLQ = 'viva:cola:deadletter'; // Dead Letter Queue
    
    public function __construct() {
        $this->redis = RedisConfig::getConnection();
        $this->pdo = $this->connectPostgres();
        
        $this->log("[*] Worker inicializado");
    }
    
    private function connectPostgres(): PDO {
        // Cargar variables de entorno estáticamente si no están en $_ENV
        if (!isset($_ENV['DB_HOST'])) {
            $envPath = dirname(__DIR__, 2) . '/.env';
            if (file_exists($envPath)) {
                $envVars = parse_ini_file($envPath);
                foreach ($envVars as $k => $v) {
                    $_ENV[$k] = $v;
                }
            }
        }
        
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $dbname = $_ENV['DB_NAME'] ?? 'db_viva';
        $user = $_ENV['DB_USERNAME'] ?? 'postgres';
        $pass = $_ENV['DB_PASSWORD'] ?? 'Gerson03#';
        
        try {
            // Eliminar apóstrofes de la variable de entorno de BD si las tiene ('db_viva' -> db_viva)
            $dbname = trim($dbname, "'\"");
            $user = trim($user, "'\"");
            $pass = trim($pass, "'\"");
            
            return new PDO(
                "pgsql:host=$host;dbname=$dbname",
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            $this->log("[ERROR] No se pudo conectar a PostgreSQL: " . $e->getMessage());
            throw $e;
        }
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
                        $mensajeInt = (int) $mensaje;
                        
                        match ($cola) {
                            self::QUEUE_REGISTROS => $this->procesarRegistro($mensajeInt),
                            self::QUEUE_CARRITO => $this->procesarCarrito($mensajeInt),
                            default => $this->log("[!] Cola desconocida: $cola")
                        };
                    } catch (Exception $e) {
                        $this->log("[ERROR] Excepción: " . $e->getMessage());
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
                
            } catch (PDOException $e) {
                $this->log("[!] Intento $intento/{$this->maxRetries} falló: " . $e->getMessage());
                echo $e->getMessage() . "\n";
                
                if ($intento < $this->maxRetries) {
                    $espera = $this->backoff[$intento - 1] ?? 60;
                    $this->log("[*] Esperando $espera segundos antes de reintentar...");
                    sleep($espera);
                }
            }
        }
        
        // Si todos los intentos fallan: mover a Dead Letter Queue
        $this->moverADLQ('registro', $userId, $userData);
    }
    
    /**
     * Procesar carrito de compras
     */
    private function procesarCarrito(int $cartId): void {
        $this->log("[*] Procesando carrito ID: $cartId");
        
        $prefix = RedisConfig::getPrefix();
        $cartData = $this->redis->hgetall($prefix . 'carrito:' . $cartId);
        
        if (empty($cartData)) {
            $this->log("[!] No se encontraron datos para carrito $cartId");
            return;
        }
        
        $items = json_decode($cartData['items'] ?? '[]', true);
        
        for ($intento = 1; $intento <= $this->maxRetries; $intento++) {
            try {
                $this->ejecutarInsertCarrito($cartData, $items);
                
                // Éxito
                $this->redis->del($prefix . 'carrito:' . $cartId);
                $this->log("[✓] Carrito $cartId procesado correctamente");
                
                return;
                
            } catch (PDOException $e) {
                $this->log("[!] Intento $intento falló: " . $e->getMessage());
                
                if ($intento < $this->maxRetries) {
                    sleep($this->backoff[$intento - 1]);
                }
            }
        }
        
        $this->moverADLQ('carrito', $cartId, $cartData);
    }
    
    /**
     * Ejecutar insert de usuario en PostgreSQL
     */
    private function ejecutarInsertUsuario(array $userData): void {
        $stmt = $this->pdo->prepare("SELECT fun_c_user(?, ?, ?, ?)");
        $stmt->execute([
            $userData['mail'],
            $userData['password'],
            $userData['nombre'],
            $userData['apellido']
        ]);
        
        $result = $stmt->fetch();
        $this->log("[*] Resultado: " . json_encode($result));
    }
    
    /**
     * Ejecutar insert de carrito en PostgreSQL
     */
    private function ejecutarInsertCarrito(array $cartData, array $items): void {
        // Aquí iría tu lógica para insertar el carrito
        // Ejemplo: llamar a tu función fun_c_carrito
        $stmt = $this->pdo->prepare("SELECT fun_c_carrito(?, ?, ?)");
        $stmt->execute([
            $cartData['usuario_id'],
            json_encode($items),
            $cartData['total'] ?? 0
        ]);
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
