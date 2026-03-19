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

class RedisConfig
{
    private static ?RedisClient $instance = null;

    public static function getConnection(): RedisClient
    {
        if (self::$instance === null) {
            // Fail Fast previo: Asegurar que las variables principales existan (Reglas del proyecto, sin usar operadores ??)
            if (empty($_ENV['REDIS_HOST']) || empty($_ENV['REDIS_PORT']) || !isset($_ENV['REDIS_DATABASE']) || $_ENV['REDIS_DATABASE'] === '') {
                throw new \RuntimeException("FALTA CONFIGURACION: REDIS_HOST, REDIS_PORT o REDIS_DATABASE no están definidos o están vacíos en el archivo .env.");
            }

            // Obtener configuración estrictamente desde variables de entorno
            $host = $_ENV['REDIS_HOST'];
            $port = $_ENV['REDIS_PORT'];
            $database = $_ENV['REDIS_DATABASE'];
            $password = isset($_ENV['REDIS_PASSWORD']) ? $_ENV['REDIS_PASSWORD'] : '';

            self::$instance = new RedisClient([
                'scheme' => 'tcp',
                'host' => $host,
                'port' => $port,
                'database' => $database,
                'read_write_timeout' => -1, // Sin timeout para brpop
                'persistent' => true, // Conexión persistente reutilizada en el mismo proceso para evitar latencia TCP
                'password' => $password,
            ]);

            // Ya no se usa self::$instance->ping(); para evitar que la app pague el costo de un ciclo de latencia RTT extra
            // (La conexión caerá lazily en el primer comando que lance auth_controller.php y su catch manejará el error a BD)
        }
        return self::$instance;
    }

    // Prefijo consistente para el proyecto y sin harcodeo (.env) 
    public static function getPrefix(): string {
        if (empty($_ENV['REDIS_PREFIX'])) {
            throw new \RuntimeException("FALTA CONFIGURACION: REDIS_PREFIX no está definido en el archivo .env.");
        }
        $prefix = $_ENV['REDIS_PREFIX'];
        return $prefix;
    }

    // Convenience methods para claves
    public static function cola(string $nombre): string
    {
        return self::getPrefix() . 'cola:' . $nombre;
    }

    public static function user(int $id): string
    {
        return self::getPrefix() . 'user:' . $id;
    }

    public static function cache(string $entidad, int $id): string
    {
        return self::getPrefix() . 'cache:' . $entidad . ':' . $id;
    }

    public static function lock(string $recurso): string
    {
        return self::getPrefix() . 'lock:' . $recurso;
    }

    public static function contador(string $entidad): string
    {
        return self::getPrefix() . 'contador:' . $entidad;
    }

    /**
     * Método de compatibilidad para códigos que usan la interfaz de phpredis
     * Predis tiene nombres de métodos similares, pero algunos difieren
     */
    public static function getClient(): RedisClient
    {
        return self::getConnection();
    }
}
