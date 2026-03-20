<?php
/**
 * Servicio de caché con Read-Through pattern
 * Busca en Redis primero, si no existe va a PostgreSQL
 */

require_once __DIR__ . '/../Config/RedisConfig.php';

use Predis\Client as Redis;
use PDO;

class RedisCacheService {
    private Redis $redis;
    private PDO $pdo;
    private int $defaultTTL = 3600; // 1 hora
    
    public function __construct() {
        $this->redis = RedisConfig::getConnection();
        $this->pdo = $this->connectPostgres();
    }
    
    private function connectPostgres(): PDO {
        try {
            $host = $_ENV['DB_HOST'] ?? 'localhost';
            $dbname = $_ENV['DB_NAME'] ?? 'db_viva';
            $user = $_ENV['DB_USERNAME'] ?? '';
            $pass = $_ENV['DB_PASSWORD'] ?? '';

            return new PDO(
                "pgsql:host=$host;dbname=$dbname",
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (PDOException $e) {
            error_log("Error conectando a PostgreSQL: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Read-Through: busca en Redis, si no está busca en DB y cachea
     */
    public function getUsuario(int $id): ?array {
        $cacheKey = RedisConfig::cache('user', $id);
        
        // 1. Buscar en Redis
        $data = $this->redis->hGetAll($cacheKey);
        
        if (!empty($data)) {
            // Cache hit
            return $data;
        }
        
        // 2. Cache miss - buscar en PostgreSQL
        $user = $this->getUsuarioFromDB($id);
        
        if ($user) {
            // 3. Write-your-writes: guardar en Redis
            $this->setUsuario($id, $user, $this->defaultTTL);
            return $user;
        }
        
        return null;
    }
    
    /**
     * Obtener usuario de PostgreSQL
     */
    private function getUsuarioFromDB(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT id_user, nom_user, ape_user, mail_user, foto_user FROM tab_users WHERE id_user = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        
        return $user ?: null;
    }
    
    /**
     * Guardar usuario en caché (Redis)
     */
    public function setUsuario(int $id, array $data, int $ttl = 3600): bool {
        $cacheKey = RedisConfig::cache('user', $id);
        
        // Guardar como hash (sintaxis moderna Predis)
        $this->redis->hset($cacheKey, $data);
        $this->redis->expire($cacheKey, $ttl);
        
        return true;
    }
    
    /**
     * Invalidar caché de usuario (cuando se actualiza)
     */
    public function invalidateUsuario(int $id): bool {
        $cacheKey = RedisConfig::cache('user', $id);
        return (bool) $this->redis->del($cacheKey);
    }
    
    /**
     * Obtener producto con caché
     */
    public function getProducto(int $id): ?array {
        $cacheKey = RedisConfig::cache('producto', $id);
        
        $data = $this->redis->hGetAll($cacheKey);
        
        if (!empty($data)) {
            return $data;
        }
        
        $producto = $this->getProductoFromDB($id);
        
        if ($producto) {
            $this->setProducto($id, $producto, 1800); // 30 min para productos
            return $producto;
        }
        
        return null;
    }
    
    private function getProductoFromDB(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT id_producto, nom_producto, precio_producto, stock_productor FROM tab_productos WHERE id_producto = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
    
    public function setProducto(int $id, array $data, int $ttl = 1800): bool {
        $cacheKey = RedisConfig::cache('producto', $id);
        $this->redis->hMSet($cacheKey, $data);
        $this->redis->expire($cacheKey, $ttl);
        return true;
    }
    
    public function invalidateProducto(int $id): bool {
        $cacheKey = RedisConfig::cache('producto', $id);
        return (bool) $this->redis->del($cacheKey);
    }
    
    /**
     * Obtener múltiples usuarios de una vez (batch)
     */
    public function getUsuarios(array $ids): array {
        $result = [];
        
        foreach ($ids as $id) {
            $result[$id] = $this->getUsuario((int) $id);
        }
        
        return $result;
    }
    
    /**
     * Verificar si existe en caché
     */
    public function exists(string $entidad, int $id): bool {
        $cacheKey = RedisConfig::cache($entidad, $id);
        return (bool) $this->redis->exists($cacheKey);
    }
}
