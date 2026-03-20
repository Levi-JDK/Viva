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
        $db = Database::getInstance();
        // Por cada item, procesar el INSERT/UPDATE/DELETE
        foreach ($this->items as $item) {
            $db->ejecutar('registrarCarritoItem', [
                ':usuario_id'  => (int)$this->cartData['usuario_id'],
                ':producto_id' => (int)$item['producto_id'],
                ':cantidad'    => (int)$item['cantidad'],
                ':precio'      => (float)($item['precio'] ?? 0)
            ]);
        }
        
        // Actualizar estado del carrito
        $db->ejecutar('cambiarEstadoCarrito', [
            ':status' => 'procesado',
            ':id'     => $this->cartId
        ]);
        
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
