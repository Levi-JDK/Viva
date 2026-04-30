<?php
/**
 * Job para procesar carrito de compras
 * Se ejecuta desde el Worker
 */

use Predis\Client as Redis;

class ProcessCartJob {
    private int $userId;
    private array $acciones;
    private string $sessionKey;
    
    public function __construct(int $userId, array $acciones, string $sessionKey) {
        $this->userId = $userId;
        $this->acciones = $acciones;
        $this->sessionKey = $sessionKey;
    }
    
    public function handle(PDO $pdo): bool {
        $db = Database::getInstance();
        
        foreach ($this->acciones as $accionItem) {
            if (!is_array($accionItem) || empty($accionItem['accion'])) {
                throw new InvalidArgumentException('Payload de carrito inválido: falta accion.');
            }

            $idProducto = array_key_exists('id_producto', $accionItem) && $accionItem['id_producto'] !== null
                ? (int) $accionItem['id_producto']
                : null;

            $cantidad = array_key_exists('cantidad', $accionItem) && $accionItem['cantidad'] !== null
                ? (int) $accionItem['cantidad']
                : null;

            $db->ejecutar('gestionarCarrito', [
                ':id_user' => $this->userId,
                ':accion' => (string) $accionItem['accion'],
                ':id_producto' => $idProducto,
                ':cantidad' => $cantidad,
            ]);
        }

        return true;
    }

    public function getSessionKey(): string {
        return $this->sessionKey;
    }
    
    public static function fromRedis(Redis $redis, array $payload): ?self {
        if (empty($payload['user_id']) || empty($payload['session_key'])) {
            throw new InvalidArgumentException('Mensaje de cola de carrito inválido.');
        }

        $key = (string) $payload['session_key'];
        $accionesJson = $payload['acciones_json'] ?? null;

        if (!is_string($accionesJson) || trim($accionesJson) === '') {
            throw new RuntimeException('Mensaje de cola de carrito sin acciones_json.');
        }

        $acciones = json_decode($accionesJson, true);
        if (!is_array($acciones)) {
            throw new RuntimeException('acciones_json inválido en mensaje de cola.');
        }

        if (isset($payload['actions_hash']) && hash('sha256', $accionesJson) !== (string) $payload['actions_hash']) {
            throw new RuntimeException('Hash de acciones_json no coincide con el mensaje de cola.');
        }

        return new self((int) $payload['user_id'], $acciones, $key);
    }
}
