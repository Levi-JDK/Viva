# 📖 Glosario de Conceptos - Sistema Redis Worker

> Explicación detallada de términos técnicos usados en el sistema

---

## Introducción

Este glosario está diseñado para alguien que **no sabe nada** del tema. Cada término se explica con:
1. Qué es
2. Para qué sirve
3. Ejemplo práctico

---

## Conceptos Básicos

### 🗄️ Redis

**Qué es**: Una base de datos que guarda todo en la **memoria RAM** (la memoria rápida de la computadora), no en el disco duro.

**Para qué sirve**: Guardar datos temporalmente de forma muy rápida. Típicamente tardamos milisegundos, mientras que PostgreSQL puede tardar segundos.

**Ejemplo**: 
- Redis = Memorias short-term (recuerdas lo que comiste hoy)
- PostgreSQL = Discos duros (recuerdos permanentes)

---

### 🗄️ PostgreSQL

**Qué es**: Una base de datos relacional tradicional, guarda todo en el disco duro.

**Para qué sirve**: Almacenar datos de forma permanente y segura. Soporta transacciones, relaciones entre tablas, y es muy robusto.

**Ejemplo**: Donde se guardan todos los datos "para siempre" del sistema.

---

### ⚙️ Worker (Proceso en Segundo Plano)

**Qué es**: Un programa que corre continuamente, esperando tareas para hacer.

**Para qué sirve**: Procesar tareas pesadas sin que el usuario tenga que esperar.

**Ejemplo**: 
```
Usuario: "Regístrame"
API: "Listo, ya está" (en 0.1 segundos)
Worker: (en background) "Yo guardo esto en la base de datos" (2 segundos después)
```

El usuario no espera esos 2 segundos porque el worker lo hace en "background".

---

### 📬 Cola (Queue)

**Qué es**: Una lista de tareas pendientes, como una fila de Supermercado.

**Para qué sirve**: Organizar tareas para que se procesen en orden.

**Ejemplo**:
```
Cola de registros: [usuario1, usuario2, usuario3, ...]
                            ↑
                    El worker toma el primero
```

---

### 💾 Caché (Cache)

**Qué es**: Una copia rápida de datos que ya consultamos antes.

**Para qué sirve**: No ir a la base de datos cada vez, ir primero a un lugar más rápido.

**Ejemplo**:
```
Usuario consulta su perfil:
1. ¿Ya lo tengo en caché (Redis)? → Devolver inmediatamente
2. ¿No está? → Buscar en PostgreSQL → Guardar en Redis → Devolver
```

---

### 🔄 AOF (Append Only File)

**Qué es**: Un método de guardar datos de Redis en el disco duro.

**Para qué sirve**: Si se apaga la computadora, no perdemos los datos de Redis.

**Analogía**: Es como un diario que se escribe solo. Si se corta la luz,我们可以 leer el diario después.

---

### 📮 DLQ (Dead Letter Queue)

**Qué es**: Una cola especial para trabajos que fallaron y no se pudieron completar.

**Para qué sirve**: No perder trabajos fallidos, revisarlos después manualmente.

**Ejemplo**:
```
Worker intenta guardar usuario 3 veces → falla
Worker: "Lo mando a la cola de muertos"
Administrador después: "Veo que falló, lo resuelvo manualmente"
```

---

## Patrones de Arquitectura

### Write-Behind (Escritura Diferida)

**Qué es**: Escribir en Redis primero (rápido), después en PostgreSQL (lento).

**Para qué sirve**: El usuario no espera a que se guarde en la base de datos final.

**Flujo**:
```
Usuario envía datos → Redis (rápido) → "OK" al usuario → Worker → PostgreSQL
```

---

### Read-Through (Lectura Inteligente)

**Qué es**: Cuando alguien pide datos, primero buscar en Redis (rápido). Si no están, buscar en PostgreSQL y guardar una copia en Redis.

**Para qué sirve**: Las siguientes consultas serán más rápidas.

**Flujo**:
```
Usuario pide datos → ¿Están en Redis? 
  → Sí: Devolver rápido
  → No: Buscar en PostgreSQL → Guardar en Redis → Devolver
```

---

### Write-Your-Writes (Escribir-Lo-Que-Lees)

**Qué es**: Después de escribir en la base de datos, también guardar en caché.

**Para qué sirve**: Immediately después de escribir, si el usuario consulta, que vea sus propios datos.

---

### Exponential Backoff (Espera Exponencial)

**Qué es**: Cuando algo falla, esperar 1 segundo, luego 5, luego 30, luego 60.

**Para qué sirve**: No saturar el sistema de reintentos, dar tiempo a que se recupere.

**Ejemplo**:
```
Intento 1: Falló → Esperar 1 segundo
Intento 2: Falló → Esperar 5 segundos  
Intento 3: Falló → Esperar 30 segundos
```

---

## Conceptos de Seguridad

### XSS (Cross-Site Scripting)

**Qué es**: Un atacante intenta meter código JavaScript malicioso en un formulario.

**Ejemplo**:
```
Usuario registra su nombre como: <script>robartCookie()</script>
Sin sanitizar: Cuando alguien vea el nombre, se ejecutará el script
Con sanitizar: Se guarda como &lt;script&gt; (texto plano, no código)
```

---

### SQL Injection

**Qué es**: Un atacante escribe código SQL en un campo para manipular la base de datos.

**Ejemplo**:
```
Usuario pone en password: ' OR '1'='1
Sin prepared statements: SELECT * WHERE password = '' OR '1'='1' (entra a todo el mundo)
Con prepared statements: SELECT * WHERE password = ? (trata ' OR como texto literal)
```

---

### Sanitización

**Qué es**: Limpiar los datos de caracteres peligrosos antes de guardarlos.

**Para qué sirve**: Evitar que código malicioso se ejecute.

---

### Validación

**Qué es**: Revisar que los datos tengan el formato correcto.

**Para qué sirve**: Asegurar que cumplen las reglas de negocio.

**Ejemplo**:
```
- Email tiene @ y dominio
- Contraseña tiene al menos 8 caracteres
- Nombre no está vacío
```

---

## Términos Técnicos

### Singleton (Instancia Única)

**Qué es**: Un patrón que asegura que solo existe una instancia de una clase.

**Para qué sirve**: Una sola conexión a Redis para todo el sistema.

---

### Hash (Tipo de dato Redis)

**Qué es**: Un tipo de dato en Redis que guarda varios campos y valores.

**Ejemplo**:
```
Clave: viva:user:1
Campo: nombre = "Juan"
Campo: email = "juan@mail.com"
Campo: password = "$2y$10$..."
```

Es como una mini-tabla dentro de Redis.

---

### BRPOP (Bloquing Pop)

**Qué es**: Un comando que espera hasta que haya algo en la lista, luego lo toma.

**Para qué sirve**: El worker se queda esperando nuevos trabajos sin consumir CPU.

**Diferencia**:
- `LPOP`: Toma inmediatamente (si no hay nada, devuelve null)
- `BRPOP`: Espera hasta que haya algo (como un guardia esperando)

---

### TTL (Time To Live)

**Qué es**: Tiempo de vida de un dato en Redis.

**Para qué sirve**: Datos que expiran automáticamente.

**Ejemplo**:
```
Cache de usuario: TTL = 3600 segundos (1 hora)
Después de 1 hora: Se borra automáticamente
```

---

### Prepared Statements

**Qué es**: Una forma de ejecutar SQL donde los valores se envían separados del query.

**Para qué sirve**: Prevenir SQL Injection.

**Ejemplo**:
```php
// Malo (puede ser hackeado)
$stmt = $pdo->query("SELECT * FROM users WHERE email = '$email'");

// Bueno (seguro)
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
```

---

### PASSWORD_ARGON2ID

**Qué es**: Un algoritmo moderno para hashear contraseñas.

**Para qué sirve**: Guardar contraseñas de forma segura. Ni siquiera vos podés ver la contraseña real.

**Ejemplo**:
```
Usuario registra: "micontraseña"
Guardado: "$2y$10$8K1p/a0d..."
```

Aunque alguien robe la base de datos, no puede ver las contraseñas.

---

## Resumen Visual

```
┌─────────────────────────────────────────────────────────────────┐
│                    FLUJO COMPLETO                               │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  FRONTEND ──POST──▶ PHP API ──Redis──▶ Usuario (OK!)          │
│                            │                                    │
│                            ▼ Encolar                           │
│                       REDIS (cola)                              │
│                            │                                    │
│                            ▼ BRPOP (espera)                    │
│                       WORKER                                    │
│                            │                                    │
│                            ▼                                    │
│                 ┌─────────┴─────────┐                          │
│                 │                   │                          │
│            ÉXITO               FALLA                          │
│                 │                   │                          │
│            PostgreSQL         REINTENTAR (1s,5s,30s)          │
│                 │                   │                          │
│            + Cache           ┌────┴────┐                       │
│                 │            │         │                        │
│            +Cache         3 intentos  DLQ                      │
│                           restantes  (cola de muertos)        │
└─────────────────────────────────────────────────────────────────┘
```

---

## Más Información

- [README Principal](./README.md)
- [Skill](./../../.atl/skills/redis-async-worker/SKILL.md)
- [Código](./Worker.php)

---

*Glosario generado automáticamente - 2026-03-15*
