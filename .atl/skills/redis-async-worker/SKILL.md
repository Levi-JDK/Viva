---
name: redis-async-worker
description: >
  Crea workers asíncronos con Redis para procesar transacciones del front-end.
  Usa Predis (biblioteca PHP pura) para máxima compatibilidad entre servidores.
  Trigger: Cuando necesités crear un pipeline Redis-PostgreSQL, worker asíncrono, o sistema de colas.
license: Apache-2.0
metadata:
  author: viva
  version: "2.0"
  scope: [root]
  auto_invoke:
    - "Crear pipeline Redis-PostgreSQL"
    - "Crear worker asíncrono"
    - "Implementar sistema de colas con Redis"
allowed-tools: Read, Edit, Write, Glob, Grep, Bash
---

## Versión 2.0 - Predis + Multi-Plataforma

Esta versión usa **Predis** en lugar de phpredis para:
- Compatibilidad total entre Windows (desarrollo), Linux (producción)
- No requiere extensión PHP compilada
- Instalación simple con composer

---

## Cuándo Usar Esta Skill

Usá esta skill cuando:
- Necesités procesar transacciones del front-end de forma asíncrona
- Quierás escribir en Redis primero y luego en PostgreSQL (write-behind)
- Necesités caché con lectura rápida y consistencia (read-through)
- El worker se active por acciones del usuario (cerrar compra, cerrar pestaña)

---

## Estructura de Carpetas

```
src/
├── workers/                    ← Workers asíncronos
│   ├── Worker.php             # Worker principal (brpop)
│   ├── Jobs/                  # Clases de jobs
│   │   ├── RegisterUserJob.php
│   │   └── ProcessCartJob.php
│   ├── Services/              # Lógica de negocio
│   │   ├── ValidationService.php
│   │   └── RedisCacheService.php
│   └── Config/
│       └── RedisConfig.php    # Configuración Predis + .env
├── api/
├── controllers/
├── functions/
└── views/

.env                           # Variables de entorno (NO commitear)
composer.json                  # Dependencias del proyecto
```

---

## Instalación

### 1. Instalar dependencias con composer

```bash
composer require predis/predis
composer require vlucas/phpdotenv
```

### 2. Configurar archivo .env

Crear archivo `.env` en la raíz del proyecto (agregar a .gitignore):

```env
# ============================================
# REDIS CONFIGURATION
# ============================================
# Desarrollo (Windows/Linux local)
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_DATABASE=0
REDIS_PREFIX=viva:

# Producción (Linux servidor)
# REDIS_HOST=10.5.213.111
# REDIS_PORT=6379
# REDIS_DATABASE=0
# REDIS_PREFIX=viva:

# ============================================
# POSTGRESQL CONFIGURATION
# ============================================
# Desarrollo (Windows local)
DB_HOST=localhost
DB_NAME=db_levi
DB_USER=levi
DB_PASSWORD=Gerson03#

# Producción (Linux servidor)
# DB_HOST=10.5.213.111
# DB_NAME=db_levi
# DB_USER=levi
# DB_PASSWORD=tu_password_produccion
```

### 3. Verificar instalación de Redis

```bash
# En Linux
sudo systemctl start redis
redis-cli ping

# En Windows (usando WSL o Redis for Windows)
redis-server
```

---

## Configuración de Predis

### Archivo: src/workers/Config/RedisConfig.php

```php
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

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../.env';

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
            
            self::$instance = new RedisClient([
                'scheme' => 'tcp',
                'host' => $host,
                'port' => (int)$port,
                'database' => (int)$database,
                'read_write_timeout' => 0, // Sin timeout para brpop
                'persistent' => false,
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
}
```

---

## Naming Convention

| Recurso | Formato | Ejemplo |
|---------|---------|---------|
| Cola | `viva:cola:{nombre}` | `viva:cola:registros` |
| Hash | `viva:{entidad}:{id}` | `viva:user:123` |
| Contador | `viva:contador:{entidad}` | `viva:contador:usuarios` |
| Cache | `viva:cache:{entidad}:{id}` | `viva:cache:producto:456` |
| DLQ | `viva:cola:deadletter` | `viva:cola:deadletter` |

---

## PATRÓN 1: WRITE-BEHIND (POST → Redis → Worker → PostgreSQL)

### Flujo

```
1. Frontend POST → PHP
2. PHP → Validar + Sanitizar → Redis (respuesta inmediata)
3. PHP → Devolver OK al frontend
4. Worker (background) → Lee Redis → Inserta en PostgreSQL
```

### Código: Receptor (API/Controller)

```php
// src/api/registro.php

require_once __DIR__ . '/../workers/Config/RedisConfig.php';
require_once __DIR__ . '/../workers/Services/ValidationService.php';

use Predis\Client as Redis;

class RegistroController {
    private Redis $redis;
    
    public function __construct() {
        $this->redis = RedisConfig::getConnection();
    }
    
    public function crearUsuario(array $data): array {
        // 1. VALIDAR datos
        $validation = ValidationService::validarRegistro($data);
        if (!$validation['valido']) {
            return ['error' => $validation['errores']];
        }
        
        // 2. SANITIZAR datos
        $dataSanitizada = ValidationService::sanitizar($data);
        
        // 3. Verificar si existe (ej: email duplicado)
        $prefix = RedisConfig::getPrefix();
        $exists = $this->redis->exists($prefix . 'lock:email:' . $dataSanitizada['email']);
        
        if ($exists) {
            return ['error' => 'El correo ya está registrado'];
        }
        
        // 4. Crear lock para evitar duplicados (NX = solo si no existe)
        $this->redis->set(
            $prefix . 'lock:email:' . $dataSanitizada['email'],
            '1',
            ['nx', 'ex' => 3600] // Expira en 1 hora
        );
        
        // 5. Generar ID único
        $id = $this->redis->incr($prefix . 'contador:usuarios');
        
        // 6. Guardar datos en Redis Hash
        $this->redis->hset($prefix . 'user:' . $id, [
            'nombre' => $dataSanitizada['nombre'],
            'apellido' => $dataSanitizada['apellido'],
            'email' => $dataSanitizada['email'],
            'password' => password_hash($dataSanitizada['password'], PASSWORD_ARGON2ID),
            'created_at' => date('Y-m-d H:i:s'),
            'status' => 'pending'
        ]);
        
        // 7. Encolar para procesamiento asíncrono
        $this->redis->lpush($prefix . 'cola:registros', $id);
        
        // 8. Responder inmediatamente
        return [
            'success' => true,
            'ticket' => $id,
            'mensaje' => 'Registro en proceso'
        ];
    }
}
```

### Código: Validación y Sanitización

```php
// src/workers/Services/ValidationService.php

class ValidationService {
    
    public static function validarRegistro(array $data): array {
        $errores = [];
        
        // Validaciones
        if (empty($data['nombre']) || strlen($data['nombre']) < 2) {
            $errores[] = 'El nombre debe tener al menos 2 caracteres';
        }
        
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'Email inválido';
        }
        
        if (empty($data['password']) || strlen($data['password']) < 8) {
            $errores[] = 'La contraseña debe tener al menos 8 caracteres';
        }
        
        return [
            'valido' => empty($errores),
            'errores' => $errores
        ];
    }
    
    public static function sanitizar(array $data): array {
        return [
            'nombre' => htmlspecialchars(trim($data['nombre']), ENT_QUOTES, 'UTF-8'),
            'apellido' => htmlspecialchars(trim($data['apellido']), ENT_QUOTES, 'UTF-8'),
            'email' => strtolower(filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL)),
            'password' => trim($data['password'])
        ];
    }
    
    // Prevenir SQL Injection en inputs
    public static function sanitizarSQL(string $input): string {
        // Usar prepared statements siempre, esto es solo capa adicional
        return preg_replace('/[^\w\-@.]/', '', $input);
    }
}
```

---

## PATRÓN 2: READ-THROUGH (GET → Redis → PostgreSQL → Redis)

### Flujo

```
1. Frontend GET → PHP
2. PHP → Buscar en Redis
3. Si existe en Redis → Devolver (rápido)
4. Si NO existe → Buscar en PostgreSQL → Guardar en Redis → Devolver
```

### Código: Lector

```php
// src/workers/Services/RedisCacheService.php

use Predis\Client as Redis;

class RedisCacheService {
    private Redis $redis;
    private PDO $pdo;
    
    public function __construct() {
        $this->redis = RedisConfig::getConnection();
        $this->pdo = Database::getConnection();
    }
    
    /**
     * Read-through cache: busca en Redis, si no está, busca en DB y cachea
     */
    public function getUsuario(int $id): ?array {
        $prefix = RedisConfig::getPrefix();
        $cacheKey = $prefix . 'cache:user:' . $id;
        
        // 1. Buscar en Redis
        $data = $this->redis->hgetall($cacheKey);
        
        if (!empty($data)) {
            // Cache hit - devolver directamente
            return $data;
        }
        
        // 2. Cache miss - buscar en PostgreSQL
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // 3. Write-your-writes: guardar en Redis para próximas lecturas
            $this->redis->hset($cacheKey, $user);
            $this->redis->expire($cacheKey, 3600); // TTL 1 hora
            
            return $user;
        }
        
        return null;
    }
    
    /**
     * Invalidar cache cuando se actualiza el usuario
     */
    public function invalidateUsuario(int $id): void {
        $prefix = RedisConfig::getPrefix();
        $this->redis->del($prefix . 'cache:user:' . $id);
    }
}
```

---

## PATRÓN 3: WORKER CON RETRY Y DLQ

### Código: Worker Principal

```php
// src/workers/Worker.php

require_once __DIR__ . '/Config/RedisConfig.php';
require_once __DIR__ . '/Jobs/RegisterUserJob.php';

use Predis\Client as Redis;

class Worker {
    private Redis $redis;
    private PDO $pdo;
    private int $maxRetries = 3;
    private array $backoff = [1, 5, 30, 60]; // Exponential backoff
    
    private const PREFIX = 'viva:';
    private const QUEUE_REGISTROS = 'viva:cola:registros';
    private const QUEUE_CARRITO = 'viva:cola:carrito';
    private const QUEUE_DLQ = 'viva:cola:deadletter'; // Dead Letter Queue
    
    public function __construct() {
        $this->redis = RedisConfig::getConnection();
        $this->pdo = $this->connectPostgres();
    }
    
    private function connectPostgres(): PDO {
        // Cargar variables de entorno
        require_once __DIR__ . '/../../.env';
        
        $host = getenv('DB_HOST') ?: '10.5.213.111';
        $dbname = getenv('DB_NAME') ?: 'db_levi';
        $user = getenv('DB_USER') ?: 'levi';
        $pass = getenv('DB_PASSWORD') ?: 'Gerson03#';
        
        try {
            return new PDO(
                "pgsql:host=$host;dbname=$dbname",
                $user,
                $pass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            $this->log("ERROR: No se pudo conectar a PostgreSQL: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function run(): void {
        $this->log("[*] Worker iniciado, esperando mensajes...");
        
        $colas = [self::QUEUE_REGISTROS, self::QUEUE_CARRITO];
        
        while (true) {
            // Bloquear hasta que llegue un mensaje (0 = sin timeout)
            $result = $this->redis->brpop($colas, 0);
            
            if ($result) {
                // Normalizar resultado de Predis
                $cola = is_array($result) ? reset($result) : $result[0];
                $mensaje = is_array($result) ? end($result) : $result[1];
                
                if ($mensaje) {
                    $this->log("[*] Mensaje recibido de: $cola");
                    
                    $mensajeInt = (int) $mensaje;
                    
                    match ($cola) {
                        self::QUEUE_REGISTROS => $this->procesarRegistro($mensajeInt),
                        self::QUEUE_CARRITO => $this->procesarCarrito($mensajeInt),
                        default => $this->log("[!] Cola desconocida: $cola")
                    };
                }
            }
        }
    }
    
    private function procesarRegistro(int $userId): void {
        $this->log("[*] Procesando usuario ID: $userId");
        
        $userData = $this->redis->hgetall(self::PREFIX . 'user:' . $userId);
        
        if (empty($userData)) {
            $this->log("[!] No se encontraron datos para usuario $userId");
            return;
        }
        
        // Intentar con reintentos
        for ($intento = 1; $intento <= $this->maxRetries; $intento++) {
            try {
                $this->ejecutarInsert($userData);
                
                // Éxito: limpiar Redis y salir
                $this->redis->del(self::PREFIX . 'user:' . $userId);
                $this->log("[✓] Usuario $userId insertado correctamente");
                
                return;
                
            } catch (PDOException $e) {
                $this->log("[!] Intento $intento/$this->maxRetries falló: " . $e->getMessage());
                
                if ($intento < $this->maxRetries) {
                    sleep($this->backoff[$intento - 1]);
                }
            }
        }
        
        // Si todos los intentos fallan: mover a Dead Letter Queue
        $this->moverADLQ('registro', $userId, $userData);
    }
    
    private function procesarCarrito(int $cartId): void {
        $this->log("[*] Procesando carrito ID: $cartId");
        
        $cartData = $this->redis->hgetall(self::PREFIX . 'carrito:' . $cartId);
        
        if (empty($cartData)) {
            $this->log("[!] No se encontraron datos para carrito $cartId");
            return;
        }
        
        for ($intento = 1; $intento <= $this->maxRetries; $intento++) {
            try {
                $items = json_decode($cartData['items'] ?? '[]', true);
                $this->ejecutarInsertCarrito($cartData, $items);
                
                $this->redis->del(self::PREFIX . 'carrito:' . $cartId);
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
    
    private function ejecutarInsert(array $userData): void {
        $stmt = $this->pdo->prepare("SELECT fun_c_user(?, ?, ?, ?)");
        $stmt->execute([
            $userData['email'],
            $userData['password'],
            $userData['nombre'],
            $userData['apellido']
        ]);
    }
    
    private function ejecutarInsertCarrito(array $cartData, array $items): void {
        $stmt = $this->pdo->prepare("SELECT fun_c_carrito(?, ?, ?)");
        $stmt->execute([
            $cartData['usuario_id'],
            json_encode($items),
            $cartData['total'] ?? 0
        ]);
    }
    
    /**
     * Mover mensaje fallido a Dead Letter Queue (DLQ)
     * 
     * ¿Qué es la DLQ?
     * Es una cola "de mensajes muertos" donde van los datos que no pudieron
     * procesarse después de todos los reintentos. Quedan ahí para que un
     * humano los revise y procese manualmente.
     * 
     * Ejemplo de contenido en DLQ:
     * {
     *   "tipo": "registro",
     *   "id": 42,
     *   "data": {...},
     *   "fallido_en": "2026-03-15 10:30:00",
     *   "intentos": 3
     * }
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
        $this->log("[X] Movido a DLQ: $tipo:$id - Revisa viva:cola:deadletter");
    }
    
    private function log(string $mensaje): void {
        echo date('Y-m-d H:i:s') . ' ' . $mensaje . PHP_EOL;
    }
}

// Ejecutar worker
$worker = new Worker();
$worker->run();
```

---

## Activación del Worker desde el Frontend

### Opción 1: Botón "Cerrar Compra"

```javascript
// En tu JavaScript del carrito
function cerrarCompra() {
    fetch('/api/carrito/finalizar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            items: cartItems,
            usuario_id: usuarioActual.id
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            localStorage.removeItem('carrito');
            window.location.href = '/gracias.html';
        }
    });
}
```

### Opción 2: Antes de cerrar la pestaña (navigator.sendBeacon)

```javascript
window.addEventListener('beforeunload', function(e) {
    const carrito = localStorage.getItem('carrito');
    
    if (carrito && tieneItemsSinProcesar(carrito)) {
        const datos = {
            action: 'guardar_carrito',
            items: JSON.parse(carrito),
            timestamp: Date.now()
        };
        
        navigator.sendBeacon('/api/carrito/autosave', JSON.stringify(datos));
    }
});
```

### Endpoint para autosave

```php
// src/api/carrito/autosave.php

require_once __DIR__ . '/../workers/Config/RedisConfig.php';

$input = json_decode(file_get_contents('php://input'), true);
$redis = RedisConfig::getConnection();

// Guardar en Redis con TTL extendido (24 horas)
$redis->setex(
    'viva:carrito:autosave:' . $input['usuario_id'],
    86400,
    json_encode($input)
);

echo json_encode(['success' => true]);
```

---

## Comandos Útiles

```bash
# ============================================
# INSTALACIÓN
# ============================================

# Instalar dependencias (primera vez o tras cambios)
composer install

# ============================================
# REDIS
# ============================================

# Ver cola de registros en Redis (debug)
redis-cli LLEN viva:cola:registros

# Ver contenido de un usuario en Redis (debug)
redis-cli HGETALL viva:user:1

# Ver Dead Letter Queue
redis-cli LRANGE viva:cola:deadletter 0 -1

# Monitorear Redis en tiempo real
redis-cli MONITOR

# Ver configuración AOF
redis-cli CONFIG GET appendonly

# Forzar rewrite AOF
redis-cli BGREWRITEAOF

# ============================================
# WORKER
# ============================================

# Iniciar el worker
php src/workers/Worker.php

# Detener worker (Ctrl+C)

# Ver logs del worker en tiempo real
php src/workers/Worker.php 2>&1 | tee worker.log
```

---

## Diferencias entre phpredis y Predis

| Característica | phpredis | Predis |
|----------------|----------|--------|
| **Instalación** | `apt-get install php-redis` | `composer require predis/predis` |
| **Extensión PHP** | Requiere extensión compilada | Solo PHP + Composer |
| **Rendimiento** | Ligeramente más rápido | Marginalmente más lento |
| **Windows** | Difícil de instalar | Funciona perfectamente |
| **Linux** | Requiere php-redis | Funciona perfectamente |
| **Código** | `new Redis()` | `new Predis\Client()` |

**Recomendación**: Usar Predis para compatibilidad, phpredis solo si necesitas máximo rendimiento en producción.

---

## Configuración Multi-Entorno

### Desarrollo (Windows)

```env
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_DATABASE=0
REDIS_PREFIX=viva:

DB_HOST=localhost
DB_NAME=db_levi
DB_USER=levi
DB_PASSWORD=Gerson03#
```

### Producción (Linux)

```env
REDIS_HOST=10.5.213.111
REDIS_PORT=6379
REDIS_DATABASE=0
REDIS_PREFIX=viva:

DB_HOST=10.5.213.111
DB_NAME=db_levi
DB_USER=levi
DB_PASSWORD=password_seguro_produccion
```

---

## Checklist de Seguridad

- [ ] Usar prepared statements para PostgreSQL (NUNCA concatenar strings)
- [ ] Sanitizar TODOS los inputs del usuario
- [ ] Usar `password_hash()` con ARGON2ID para contraseñas
- [ ] Usar TLS/SSL si Redis está en producción
- [ ] No exponer credenciales en código (usar .env)
- [ ] Validar tipos de datos antes de guardar en Redis
- [ ] Implementar rate limiting para evitar abuso
- [ ] NO commitear archivos .env (agregar a .gitignore)
- [ ] Usar claves diferentes para desarrollo y producción

---

## Recursos

- **Documentación Predis**: https://github.com/predis/predis
- **Documentación Redis**: https://redis.io/docs/
- **Composer**: https://getcomposer.org/
- **phpdotenv**: https://github.com/vlucas/phpdotenv
