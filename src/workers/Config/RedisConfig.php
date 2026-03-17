<?php
/**
 * Configuración de Redis para el proyecto Viva
 * Usa Predis (biblioteca PHP pura, compatible con cualquier servidor)
 * 
 * Ventajas de Predis:
 * - No requiere extensión PHP (funciona en Windows y Linux)
 * - Se instala con composer: composer require predis/predis
 * - Configuración centralizada en .env
 */

require_once __DIR__ . '/../../../vendor/autoload.php';

// Cargar variables de entorno si no están cargadas
if (!isset($_ENV['REDIS_HOST'])) {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 3));
    $dotenv->safeLoad();
}

use Predis\Client as RedisClient;

class RedisConfig {
    private static ?RedisClient $instance = null;
    
    // Configuración con valores por defecto
    private const DEFAULT_HOST = '127.0.0.1';
    private const DEFAULT_PORT = 6379;
    private const DEFAULT_DATABASE = 0;
    
    public static function getConnection(): RedisClient {
        if (self::$instance === null) {
            // Obtener configuración desde variables de entorno
            $host = getenv('REDIS_HOST') ?: self::DEFAULT_HOST;
            $port = getenv('REDIS_PORT') ?: self::DEFAULT_PORT;
            $database = getenv('REDIS_DATABASE') ?: self::DEFAULT_DATABASE;
            
            // Detectar si es Windows o Linux por el host
            $isLocalhost = in_array($host, ['127.0.0.1', 'localhost', '::1']);
            
            self::$instance = new RedisClient([
                'scheme' => 'tcp',
                'host' => $host,
                'port' => (int)$port,
                'database' => (int)$database,
                'read_write_timeout' => 0, // Sin timeout para brpop
                'persistent' => false,      // Conexión nueva en cada request
            ]);
            
            // Verificar conexión
            try {
                self::$instance->ping();
            } catch (\Exception $e) {
                throw new \RuntimeException(
                    "No se pudo conectar a Redis en $host:$port. " .
                    "Verifica que Redis esté ejecutándose y que .env tenga los valores correctos."
                );
            }
        }
        return self::$instance;
    }
    
    // Prefijo consistente para el proyecto
    public static function getPrefix(): string {
        return getenv('REDIS_PREFIX') ?: 'viva:';
    }
    
    // Convenience methods para claves
    public static function cola(string $nombre): string {
        return self::getPrefix() . 'cola:' . $nombre;
    }
    
    public static function user(int $id): string {
        return self::getPrefix() . 'user:' . $id;
    }
    
    public static function cache(string $entidad, int $id): string {
        return self::getPrefix() . 'cache:' . $entidad . ':' . $id;
    }
    
    public static function lock(string $recurso): string {
        return self::getPrefix() . 'lock:' . $recurso;
    }
    
    public static function contador(string $entidad): string {
        return self::getPrefix() . 'contador:' . $entidad;
    }
    
    /**
     * Método de compatibilidad para códigos que usan la interfaz de phpredis
     * Predis tiene nombres de métodos similares, pero algunos difieren
     */
    public static function getClient(): RedisClient {
        return self::getConnection();
    }
}
