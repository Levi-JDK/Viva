# 📖 Glosario de Funciones y Métodos

> Explicación de cada función y método del sistema, con ejemplos de uso

---

## Tabla de Contenidos

1. [RedisConfig](#redisconfig)
2. [ValidationService](#validationservice)
3. [RedisCacheService](#rediscacheservice)
4. [Worker](#worker)
5. [Métodos de Redis](#métodos-de-redis)

---

## RedisConfig

Ubicación: `src/workers/Config/RedisConfig.php`

### `RedisConfig::getConnection()`

**Qué hace**: Crea y retorna una conexión a Redis. Si ya existe, retorna la existente (Singleton).

**Para qué sirve**: Tener una sola conexión para todo el proyecto.

```php
// Uso
$redis = RedisConfig::getConnection();

// Ejemplo completo
require_once 'src/workers/Config/RedisConfig.php';

$redis = RedisConfig::getConnection();
$redis->set('miClave', 'miValor');
echo $redis->get('miClave'); // "miValor"
```

---

### `RedisConfig::getPrefix()`

**Qué hace**: Retorna el prefijo del proyecto (`viva:`).

**Para qué sirve**: Mantener consistencia en los nombres de claves.

```php
// Uso
$prefijo = RedisConfig::getPrefix(); // "viva:"

// Ejemplo
$clave = $prefijo . 'user:1'; // "viva:user:1"
```

---

### `RedisConfig::cola(string $nombre)`

**Qué hace**: Genera el nombre de una cola.

```php
// Uso
$cola = RedisConfig::cola('registros'); // "viva:cola:registros"
$cola = RedisConfig::cola('carrito');   // "viva:cola:carrito"
```

---

### `RedisConfig::user(int $id)`

**Qué hace**: Genera la clave para un usuario en Redis.

```php
// Uso
$clave = RedisConfig::user(1);  // "viva:user:1"
$clave = RedisConfig::user(15); // "viva:user:15"
```

---

### `RedisConfig::cache(string $entidad, int $id)`

**Qué hace**: Genera la clave para caché de una entidad.

```php
// Uso
$cache = RedisConfig::cache('user', 1);      // "viva:cache:user:1"
$cache = RedisConfig::cache('producto', 5);   // "viva:cache:producto:5"
```

---

### `RedisConfig::lock(string $recurso)`

**Qué hace**: Genera una clave de lock (para evitar duplicados).

```php
// Uso
$lock = RedisConfig::lock('email:pepe@mail.com'); // "viva:lock:email:pepe@mail.com"

// Se usa para evitar que se registre el mismo email dos veces
$redis->set(RedisConfig::lock('email:pepe@mail.com'), '1', ['nx', 'ex' => 3600]);
```

---

### `RedisConfig::contador(string $entidad)`

**Qué hace**: Genera la clave para un contador.

```php
// Uso
$contador = RedisConfig::contador('usuarios'); // "viva:contador:usuarios"

// Incrementar
$id = $redis->incr($contador); // 1, 2, 3...
```

---

## ValidationService

Ubicación: `src/workers/Services/ValidationService.php`

### `ValidationService::validarRegistro(array $data)`

**Qué hace**: Valida que los datos de registro cumplan las reglas.

**Retorna**: `['valido' => bool, 'errores' => array]`

```php
// Uso
$datos = [
    'nombre' => 'Juan',
    'apellido' => 'Pérez',
    'email' => 'juan@mail.com',
    'password' => 'Password123#'
];

$resultado = ValidationService::validarRegistro($datos);

if ($resultado['valido']) {
    echo "Todo bien";
} else {
    print_r($resultado['errores']);
    // ["El nombre debe tener al menos 2 caracteres", ...]
}
```

**Validaciones que hace**:
- Nombre: 2-100 caracteres
- Apellido: 2-100 caracteres
- Email: formato válido
- Password: mínimo 8 caracteres, mayúscula, número, especial

---

### `ValidationService::validarCarrito(array $data)`

**Qué hace**: Valida los datos del carrito de compras.

```php
$datos = [
    'usuario_id' => 1,
    'items' => [
        ['producto_id' => 5, 'cantidad' => 2, 'precio' => 10000],
        ['producto_id' => 8, 'cantidad' => 1, 'precio' => 25000]
    ]
];

$resultado = ValidationService::validarCarrito($datos);
```

---

### `ValidationService::sanitizar(array $data)`

**Qué hace**: Limpia los datos de caracteres peligrosos (XSS).

**Importante**: Ejecutar DESPUÉS de validar.

```php
// Uso
$datosSucios = [
    'nombre' => '  <script>alert("hack")</script>Juan  ',
    'email' => '  JUAN@MAIL.COM  '
];

$datosLimpios = ValidationService::sanitizar($datosSucios);

// Resultado:
// ['nombre' => '&lt;script&gt;alert(&quot;hack&quot;)&lt;/script&gt;Juan', 'email' => 'juan@mail.com']
```

---

### `ValidationService::sanitizarEmail(string $email)`

**Qué hace**: Limpia y normaliza un email.

```php
// Uso
$email = '  JUAN@MAIL.COM  ';
$emailLimpio = ValidationService::sanitizarEmail($email);

// Resultado: "juan@mail.com"
```

---

### `ValidationService::validarFortalezaPassword(string $password)`

**Qué hace**: Verifica que la contraseña sea segura.

**Retorna**: `true` o `false`

```php
// Uso
ValidationService::validarFortalezaPassword('password123'); // false
ValidationService::validarFortalezaPassword('Password123#'); // true

// Requiere:
// - Mínimo 8 caracteres
// - Al menos 1 mayúscula
// - Al menos 1 minúscula
// - Al menos 1 número
// - Al menos 1 carácter especial
```

---

### `ValidationService::validarId($id)`

**Qué hace**: Verifica que un ID sea numérico y positivo.

```php
// Uso
ValidationService::validarId(1);     // true
ValidationService::validarId("5");    // true (convierte a número)
ValidationService::validarId(0);      // false
ValidationService::validarId(-1);    // false
ValidationService::validarId("abc");  // false
```

---

## RedisCacheService

Ubicación: `src/workers/Services/RedisCacheService.php`

### `RedisCacheService::getUsuario(int $id)`

**Qué hace**: Obtiene un usuario. Busca primero en Redis cache, si no está va a PostgreSQL.

**Patrón**: Read-Through

```php
// Uso
$cache = new RedisCacheService();
$usuario = $cache->getUsuario(1);

if ($usuario) {
    echo "Hola " . $usuario['nombre'];
} else {
    echo "Usuario no encontrado";
}

// Flujo interno:
// 1. Busca en Redis: viva:cache:user:1
// 2. Si existe → lo retorna (rápido)
// 3. Si NO existe → busca en PostgreSQL → guarda en Redis → retorna
```

---

### `RedisCacheService::setUsuario(int $id, array $data, int $ttl = 3600)`

**Qué hace**: Guarda un usuario en caché.

```php
// Uso
$cache = new RedisCacheService();
$cache->setUsuario(1, [
    'nombre' => 'Juan',
    'email' => 'juan@mail.com'
], 3600); // TTL de 1 hora

// Guarda en: viva:cache:user:1
// Expira en: 3600 segundos (1 hora)
```

---

### `RedisCacheService::invalidateUsuario(int $id)`

**Qué hace**: Elimina el caché de un usuario (cuando se actualiza).

```php
// Uso
$cache = new RedisCacheService();
$cache->invalidateUsuario(1); // Elimina viva:cache:user:1

// Cuándo usarlo:
// - Cuando un usuario actualiza su perfil
// - Cuando cambia su información
// Así la próxima lectura obtiene datos frescos de PostgreSQL
```

---

### `RedisCacheService::getProducto(int $id)`

**Qué hace**: Obtiene un producto con caché (igual que usuario).

```php
// Uso
$cache = new RedisCacheService();
$producto = $cache->getProducto(5);

// TTL por defecto: 1800 segundos (30 minutos)
```

---

### `RedisCacheService::exists(string $entidad, int $id)`

**Qué hace**: Verifica si existe algo en caché.

```php
// Uso
$cache = new RedisCacheService();

if ($cache->exists('user', 1)) {
    echo "Está en caché";
} else {
    echo "No está en caché";
}
```

---

## Worker

Ubicación: `src/workers/Worker.php`

### `Worker::run()`

**Qué hace**: Inicia el worker y comienza a escuchar colas.

```php
// Uso (en terminal)
php src/workers/Worker.php

// El worker queda escuchando:
// - viva:cola:registros
// - viva:cola:carrito

// Presionar Ctrl+C para detener
```

---

### `Worker::procesarRegistro(int $userId)`

**Qué hace**: Procesa un registro de usuario desde la cola.

```php
// Flujo interno:
// 1. Obtiene datos de Redis: viva:user:$userId
// 2. Intenta insertar en PostgreSQL (fun_c_user)
// 3. Si falla: reintenta (1s, 5s, 30s)
// 4. Si éxito: elimina de Redis
// 5. Si falla 3 veces: mueve a DLQ
```

---

### `Worker::procesarCarrito(int $cartId)`

**Qué hace**: Procesa un carrito de compras.

```php
// Flujo:
// 1. Obtiene datos de: viva:carrito:$cartId
// 2. Decodifica items del JSON
// 3. Inserta cada item en PostgreSQL
// 4. Actualiza estado del carrito
// 5. Limpia Redis
```

---

## Métodos de Redis

> Funciones nativas de phpredis. Se usan con `$redis = RedisConfig::getConnection()`

### `$redis->set('clave', 'valor')`

Guarda un valor simple.

```php
$redis->set('nombre', 'Juan');
$redis->get('nombre'); // "Juan"
```

---

### `$redis->set('clave', 'valor', ['nx' => true, 'ex' => 3600])`

Guarda SOLO SI NO EXISTE (Nx) con expiración (Ex).

```php
// Guardar lock de email (si no existe)
$redis->set('viva:lock:email:test@mail.com', '1', ['nx', 'ex' => 3600]);

// Retorna:
// - true si se guardó (no existía)
// - false si ya existía
```

---

### `$redis->hMSet('hash', [campo => valor, ...])`

Guarda varios campos en un hash.

```php
$redis->hMSet('viva:user:1', [
    'nombre' => 'Juan',
    'email' => 'juan@mail.com',
    'password' => '$2y$10$...'
]);
```

---

### `$redis->hGetAll('hash')`

Obtiene todos los campos de un hash.

```php
$datos = $redis->hGetAll('viva:user:1');
// Resultado: ['nombre' => 'Juan', 'email' => 'juan@mail.com', ...]
```

---

### `$redis->lpush('lista', 'valor')`

Agrega un valor al inicio de una lista (encolar).

```php
$redis->lpush('viva:cola:registros', '1');
$redis->lpush('viva:cola:registros', '2');
// Lista ahora: [2, 1] (el 2 está al inicio)
```

---

### `$redis->brpop(['cola1', 'cola2'], 0)`

Espera y obtiene un valor de una lista (bloqueante).

```php
// Espera hasta que llegue algo
$resultado = $redis->brpop(['viva:cola:registros', 'viva:cola:carrito'], 0);

// Resultado: ['viva:cola:registros', '1']
// o: ['viva:cola:carrito', '5']

// El 0 significa: esperar infinitamente
// Podría ser 30 para esperar máximo 30 segundos
```

---

### `$redis->incr('contador')`

Incrementa un número.

```php
$redis->set('viva:contador:usuarios', 0);
$redis->incr('viva:contador:usuarios'); // 1
$redis->incr('viva:contador:usuarios'); // 2
$redis->incr('viva:contador:usuarios'); // 3
```

---

### `$redis->del('clave')`

Elimina una clave.

```php
$redis->del('viva:user:1'); // Elimina el hash completo
```

---

### `$redis->expire('clave', 3600)`

Define tiempo de vida (TTL).

```php
$redis->set('temp:dato', 'valor');
$redis->expire('temp:dato', 60); // Expira en 60 segundos
```

---

### `$redis->exists('clave')`

Verifica si existe una clave.

```php
$redis->exists('viva:user:1'); // true o false
```

---

### `$redis->config('parametro', 'valor')`

Cambia configuración de Redis.

```php
// Activar AOF (persistencia)
$redis->config('appendonly', 'yes');
$redis->config('appendfsync', 'everysec');
```

---

## Resumen de Uso

| Necesidad | Función a usar |
|----------|----------------|
| Conectar a Redis | `RedisConfig::getConnection()` |
| Validar datos | `ValidationService::validarRegistro()` |
| Limpiar datos | `ValidationService::sanitizar()` |
| Guardar con caché | `RedisCacheService::setUsuario()` |
| Leer con caché | `RedisCacheService::getUsuario()` |
| Invalidar caché | `RedisCacheService::invalidateUsuario()` |
| Iniciar worker | `php src/workers/Worker.php` |
| Encolar tarea | `$redis->lpush('viva:cola:registros', $id)` |
| Escuchar cola | `$redis->brpop(['cola'], 0)` |

---

*Glosario de funciones generado automáticamente - 2026-03-15*
