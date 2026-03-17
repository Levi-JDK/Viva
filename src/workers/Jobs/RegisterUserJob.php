<?php
/**
 * Job para procesar registro de usuario
 * Se ejecuta desde el Worker
 */

use Predis\Client as Redis;

class RegisterUserJob {
    private int $userId;
    private array $userData;
    
    public function __construct(int $userId, array $userData) {
        $this->userId = $userId;
        $this->userData = $userData;
    }
    
    public function handle(PDO $pdo): bool {
        // Ejecutar el stored procedure
        $stmt = $pdo->prepare("SELECT fun_c_user(?, ?, ?, ?)");
        $stmt->execute([
            $this->userData['email'],
            $this->userData['password'],
            $this->userData['nombre'],
            $this->userData['apellido']
        ]);
        
        return true;
    }
    
    public static function fromRedis(Redis $redis, int $userId): ?self {
        $prefix = 'viva:';
        $key = $prefix . 'user:' . $userId;
        
        $data = $redis->hGetAll($key);
        
        if (empty($data)) {
            return null;
        }
        
        return new self($userId, $data);
    }
}
