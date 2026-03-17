<?php
/**
 * Job para procesar carrito de compras
 * Se ejecuta desde el Worker
 */

use Predis\Client as Redis;

class ProcessCartJob {
    private int $cartId;
    private array $cartData;
    private array $items;
    
    public function __construct(int $cartId, array $cartData, array $items) {
        $this->cartId = $cartId;
        $this->cartData = $cartData;
        $this->items = $items;
    }
    
    public function handle(PDO $pdo): bool {
        // Por cada item, procesar el INSERT/UPDATE/DELETE
        foreach ($this->items as $item) {
            $stmt = $pdo->prepare("SELECT fun_c_carrito_item(?, ?, ?, ?)");
            $stmt->execute([
                $this->cartData['usuario_id'],
                $item['producto_id'],
                $item['cantidad'],
                $item['precio'] ?? 0
            ]);
        }
        
        // Actualizar estado del carrito
        $stmt = $pdo->prepare("UPDATE carrito SET status = 'procesado' WHERE id = ?");
        $stmt->execute([$this->cartId]);
        
        return true;
    }
    
    public static function fromRedis(Redis $redis, int $cartId): ?self {
        $key = 'viva:carrito:' . $cartId;
        
        $data = $redis->hGetAll($key);
        
        if (empty($data)) {
            return null;
        }
        
        $items = json_decode($data['items'] ?? '[]', true);
        
        return new self($cartId, $data, $items);
    }
}
