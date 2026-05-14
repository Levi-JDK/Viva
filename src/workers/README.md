# 🏗️ Sistema de Workers Asíncronos con Redis - Viva Project

> Documentación completa del pipeline Redis-PostgreSQL para procesamiento asíncrono

---

## 📋 Índice

1. [¿Qué es este sistema?](#qué-es-este-sistema)
2. [Arquitectura General](#arquitectura-general)
3. [Flujo de Trabajo](#flujo-de-trabajo)
4. [Estructura de Archivos](#estructura-de-archivos)
5. [Componentes](#componentes)
6. [Configuración](#configuración)
7. [Cómo usar](#cómo-usar)
8. [Comandos Útiles](#comandos-útiles)
9. [Glosario](#glosario)

---

## ¿Qué es este sistema?

Este sistema permite procesar transacciones del **front-end de forma asíncrona**, mejorando la velocidad y experiencia del usuario.

### 🆚 Predis vs phpredis

Este proyecto usa **Predis** en lugar de la extensión nativa **phpredis**:

| Característica | phpredis | **Predis** |
|----------------|----------|------------|
| Requiere extensión PHP | ✅ Sí | ❌ No |
| Compatible Windows | ❌ Limitado | ✅ Total |
| Instalación | `apt-get install php-redis` | `composer require predis/predis` |
| Dependencias | Extensión PECL | Biblioteca PHP pura |

**¿Por qué Predis?**
- ✅ **Cross-platform**: Funciona en Windows, Linux, macOS sin instalar extensiones
- ✅ **Sin dependencias del sistema**: No requiere compilar extensiones PECL
- ✅ **Fácil despliegue**: Se instala via Composer como cualquier paquete PHP
- ✅ **Compatible**: API muy similar a phpredis

> ⚠️ **Nota**: Si usabas la extensión `redis` de PHP, solo necesitas cambiar el import:
> ```php
> // Antes (phpredis)
> $redis = new Redis();
> 
> // Ahora (Predis)
> use Predis\Client as RedisClient;
> $redis = RedisConfig::getConnection();
> ```

---

### Problema original
Cuando un usuario se registra o compra algo, el servidor tardaba en responder porque esperaba a que se guardara en la base de datos.

### Solución
1. El usuario envía datos → se guardan en **Redis** (rápido) → respondemos "OK" inmediatamente
2. Un **worker** en segundo plano lee de Redis → guarda en **PostgreSQL**
3. El usuario no espera, la experiencia es instantánea

---

## Arquitectura General

```
┌─────────────────────────────────────────────────────────────────────────┐
│                              FRONTEND                                   │
│                    (Carrito, Registro, etc.)                           │
└──────────────────────────────┬──────────────────────────────────────────┘
                               │ HTTP POST
                               ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                           PHP API                                       │
│                 (Validar → Sanitizar → Redis)                          │
│                                                                          │
│   1. Validar datos (ValidationService)                                 │
│   2. Sanitizar datos (prevenir ataques)                               │
│   3. Guardar en Redis                                                  │
│   4. Responder al usuario "OK"                                        │
└──────────────────────────────┬──────────────────────────────────────────┘
                               │
                               ▼ Encolar mensaje
┌─────────────────────────────────────────────────────────────────────────┐
│                              REDIS                                      │
│                                                                          │
│   ┌──────────────┐   ┌──────────────┐   ┌──────────────┐             │
│   │ cola:registros│   │ cola:carrito │   │ cola:dlq    │             │
│   │   [1, 2, 3] │   │  [10, 11]    │   │ [fallidos]  │             │
│   └──────────────┘   └──────────────┘   └──────────────┘             │
│                                                                          │
│   ┌──────────────┐   ┌──────────────┐                                 │
│   │ user:1      │   │ cache:user:1 │                                 │
│   │ {datos}     │   │ {datos}      │                                 │
│   └──────────────┘   └──────────────┘                                 │
└──────────────────────────────┬──────────────────────────────────────────┘
                               │ BRPOP (escucha colas)
                               ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                           WORKER                                        │
│                     (Procesamiento en background)                       │
│                                                                          │
│   1. Espera mensaje en cola                                            │
│   2. Lee datos de Redis                                                │
│   3. Intenta insertar en PostgreSQL                                    │
│   4. Si falla → retry (1s, 5s, 30s)                                  │
│   5. Si éxito → eliminar de Redis                                      │
│   6. Si falla 3 veces → Dead Letter Queue                             │
└──────────────────────────────┬──────────────────────────────────────────┘
                               │
                               ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                         POSTGRESQL                                      │
│                    (Base de datos principal)                           │
│                                                                          │
│   Tablas: users, transacciones, carrito, etc.                         │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## Flujo de Trabajo

### 📝 Flujo 1: Registro de Usuario (Write-Behind)

```
1. Usuario llena formulario de registro
         │
         ▼
2. Frontend envía POST /api/registro
         │
         ▼
3. PHP API valida datos
   - ¿Email válido?
   - ¿Contraseña segura?
   - ¿Campos completos?
         │
         ▼
4. PHP API sanitiza datos
   - Limpia caracteres peligrosos
   - Previene XSS (inyección de código)
         │
         ▼
5. PHP API guarda en Redis:
   - Hash: viva:user:1 {nombre, email, password}
   - Cola: viva:cola:registros [1]
         │
         ▼
6. PHP API responde al usuario:
   {"success": true, "ticket": 1}
   ⚡ Respuesta instantánea (milésimas de segundo)
         │
         ▼
7. WORKER (en background):
   - Lee de cola: viva:cola:registros
   - Obtiene datos: viva:user:1
   - Inserta en PostgreSQL: fun_c_user(...)
   - Elimina de Redis: DEL viva:user:1
```

### 🛒 Flujo 2: Carrito de Compras

```
1. Usuario agrega productos al carrito
         │
         ▼
2. Antes de cerrar/pestaña:
   JavaScript envía datos con sendBeacon()
         │
         ▼
3. PHP guarda en Redis:
   - Hash: viva:carrito:10 {items, total, usuario_id}
   - Cola: viva:cola:carrito [10]
         │
         ▼
4. Respuesta: "Carrito guardado"
         │
         ▼
5. WORKER procesa:
   - Lee carrito de Redis
   - Inserta transacciones en PostgreSQL
   - Limpia Redis
```

### 🔍 Flujo 3: Lectura con Cache (Read-Through)

```
1. Usuario quiere ver su perfil
         │
         ▼
2. PHP busca en Redis Cache:
   GET viva:cache:user:1
         │
         ├──▶ Si existe → Devolver datos (rápido)
         │
         └──▶ Si NO existe → Buscar en PostgreSQL
                            │
                            └──▶ Guardar en Redis (cachear)
                                 Devolver datos
```

---

## Estructura de Archivos

```
src/workers/
├── README.md                      ← Este archivo
├── Worker.php                     ← Worker dedicado a registros
├── CartWorker.php                 ← Worker dedicado a carritos
├── ValidationWorker.php           ← Worker dedicado a validaciones IA
├── Config/
│   └── RedisConfig.php           ← Conexión a Redis
├── Services/
│   ├── ValidationService.php     ← Validación y sanitización
│   └── RedisCacheService.php    ← Cache read-through
└── Jobs/
    ├── RegisterUserJob.php       ← Job para usuarios
    └── ProcessCartJob.php       ← Job para carrito
```

---

## Componentes

### RedisConfig
- **Qué hace**: Crea conexión a Redis
- **Para qué**: Una sola conexión para todo el sistema
- **Ubicación**: `src/workers/Config/RedisConfig.php`

### ValidationService
- **Qué hace**: Valida y limpia datos
- **Para qué**: Seguridad - prevenir ataques
- **Ubicación**: `src/workers/Services/ValidationService.php`

### RedisCacheService
- **Qué hace**: Busca en Redis, si no está busca en PostgreSQL
- **Para qué**: Lectura rápida con caché
- **Ubicación**: `src/workers/Services/RedisCacheService.php`

### Worker
- **Qué hace**: Escucha la cola `viva:cola:registro` y procesa registros en background
- **Para qué**: Persistir usuarios sin bloquear al usuario
- **Ubicación**: `src/workers/Worker.php`

### CartWorker
- **Qué hace**: Escucha la cola `viva:cola:carrito` y procesa carritos en background
- **Para qué**: Persistir acciones de carrito sin bloquear al usuario
- **Ubicación**: `src/workers/CartWorker.php`

### ValidationWorker
- **Qué hace**: Escucha la cola `viva:cola:validacion` y procesa validaciones de producto con IA
- **Para qué**: Validar productos de forma asíncrona sin bloquear el alta o edición
- **Ubicación**: `src/workers/ValidationWorker.php`

---

## Configuración

### 1. Instalación de Dependencias

```bash
# Instalar Predis (no requiere extensión PHP)
composer require predis/predis
```

### 2. Variables de entorno (.env)

Crea o actualiza el archivo `.env` en la raíz del proyecto:

```env
# Redis Configuration
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_DATABASE=0
REDIS_PREFIX=viva:
```

| Variable | Descripción | Default |
|----------|-------------|---------|
| `REDIS_HOST` | Host de Redis | 127.0.0.1 |
| `REDIS_PORT` | Puerto de Redis | 6379 |
| `REDIS_DATABASE` | Número de DB (0-15) | 0 |
| `REDIS_PREFIX` | Prefijo para claves | viva: |

### 3. Verificar conexión

```bash
# Probar conexión a Redis
redis-cli ping
# Debe responder: PONG
```

---

## Cómo usar

### 1. Iniciar los Workers

```bash
# Registro
php src/workers/Worker.php

# Carrito
php src/workers/CartWorker.php

# Validación de productos
php src/workers/ValidationWorker.php
```

Cada worker se queda escuchando indefinidamente su propia cola. Para detenerlo, presioná `Ctrl+C`.

### Supervisor

Ejemplo de configuración para ejecutar los 3 workers como procesos supervisados:

```ini
[program:viva-worker-registro]
command=php /var/www/html/viva/src/workers/Worker.php
directory=/var/www/html/viva
autostart=true
autorestart=true
stderr_logfile=/var/log/viva-worker-registro.err.log
stdout_logfile=/var/log/viva-worker-registro.out.log

[program:viva-worker-carrito]
command=php /var/www/html/viva/src/workers/CartWorker.php
directory=/var/www/html/viva
autostart=true
autorestart=true
stderr_logfile=/var/log/viva-worker-carrito.err.log
stdout_logfile=/var/log/viva-worker-carrito.out.log

[program:viva-worker-validacion]
command=php /var/www/html/viva/src/workers/ValidationWorker.php
directory=/var/www/html/viva
autostart=true
autorestart=true
stderr_logfile=/var/log/viva-worker-validacion.err.log
stdout_logfile=/var/log/viva-worker-validacion.out.log
```

### 2. Registrar un usuario (desde API)

```php
require_once 'src/workers/Config/RedisConfig.php';
require_once 'src/workers/Services/ValidationService.php';

$datos = [
    'nombre' => 'Juan',
    'apellido' => 'Pérez',
    'email' => 'juan@mail.com',
    'password' => 'Password123#'
];

// 1. Validar
$validacion = ValidationService::validarRegistro($datos);
if (!$validacion['valido']) {
    return ['error' => $validacion['errores']];
}

// 2. Sanitizar
$datosLimpios = ValidationService::sanitizar($datos);

// 3. Guardar en Redis (usando Predis)
$redis = RedisConfig::getConnection();
$id = $redis->incr('viva:contador:usuarios');
$redis->hset('viva:user:' . $id, $datosLimpios);
$redis->lpush('viva:cola:registros', $id);

return ['success' => true, 'ticket' => $id];
```

> **Nota**: Se usa `hset()` que es el método moderno de Predis para hashes.
> El objeto retornado por `RedisConfig::getConnection()` es una instancia de `Predis\Client`.

### 3. Leer con caché

```php
require_once 'src/workers/Services/RedisCacheService.php';

$cache = new RedisCacheService();
$usuario = $cache->getUsuario(1);  // Busca en Redis, si no está va a PostgreSQL

echo $usuario['nombre'];  // "Juan Pérez"
```

---

## Comandos Útiles

### Redis

```bash
# Ver cola de registros
redis-cli LLEN viva:cola:registros

# Ver cola de carrito
redis-cli LLEN viva:cola:carrito

# Ver Dead Letter Queue (jobs fallidos)
redis-cli LRANGE viva:cola:deadletter 0 -1

# Ver todos los datos de un usuario
redis-cli HGETALL viva:user:1

# Ver cache de un usuario
redis-cli HGETALL viva:cache:user:1

# Monitorear Redis en tiempo real
redis-cli MONITOR

# Ver configuración AOF
redis-cli CONFIG GET appendonly

# Ver claves del proyecto
redis-cli KEYS "viva:*"
```

### Worker

```bash
# Iniciar worker de registros
php src/workers/Worker.php

# Iniciar worker de carrito
php src/workers/CartWorker.php

# Iniciar worker de validación
php src/workers/ValidationWorker.php

# Ver logs del worker
# (los logs aparecen en la terminal)
```

---

## Glosario

### Conceptos

| Término | Definición |
|---------|------------|
| **Redis** | Base de datos en memoria, muy rápida |
| **PostgreSQL** | Base de datos relacional (más lenta pero segura) |
| **Worker** | Proceso que corre en background, procesa tareas |
| **Cola (Queue)** | Lista donde se encolan tareas pendientes |
| **Cache** | Copia de datos en memoria rápida |
| **AOF** | Persistencia de Redis (Append Only File) |
| **DLQ** | Dead Letter Queue - cola de trabajos fallidos |
| **Write-Behind** | Escribir en Redis primero, luego en DB |
| **Read-Through** | Leer de cache, si no está buscar en DB |
| **XSS** | Cross-Site Scripting - inyección de código |
| **SQL Injection** | Inyección de código SQL malicioso |

### Métodos de Redis (Predis)

| Método | Qué hace |
|--------|----------|
| `set()` | Guardar un valor simple |
| `get()` | Obtener un valor |
| `hset()` | Guardar varios campos (hash) |
| `hgetall()` | Obtener todos los campos de un hash |
| `lpush()` | Agregar al inicio de una lista |
| `brpop()` | Esperar y obtener mensaje de una lista |
| `del()` | Eliminar una clave |
| `expire()` | Definir tiempo de vida |
| `exists()` | Verificar si existe una clave |
| `incr()` | Incrementar un número |
| `ping()` | Verificar conexión |

### Métodos del Worker

| Método | Qué hace |
|--------|----------|
| `brpop()` | Espera hasta que llegue un mensaje a la cola |
| `procesarRegistro()` | Procesa un registro de usuario |
| `procesarCarrito()` | Procesa un carrito de compras desde `CartWorker` |
| `procesarValidacion()` | Procesa una validación de producto desde `ValidationWorker` |
| `moverADLQ()` | Envía un job fallido a la cola de muertos |

---

## Próximos Pasos

1. ✅ Worker básico creado
2. ⏳ Conectar con el frontend (API)
3. ⏳ Implementar sendBeacon para el carrito
4. ⏳ Probar con datos reales

---

## Enlaces

- [Skill Principal](./.atl/skills/redis-async-worker/SKILL.md)
- [Código del Worker](./src/workers/Worker.php)
- [Validación](./src/workers/Services/ValidationService.php)
- [Cache](./src/workers/Services/RedisCacheService.php)

---

*Documento generado automáticamente - 2026-03-15*
