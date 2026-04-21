# 06. Service Layer Simple: cuándo extraer lógica del controller

Te lo digo sin vueltas: un controller gigante es una alarma arquitectónica.

No porque “esté feo”. Porque mezcla responsabilidades distintas y después nadie sabe dónde tocar sin romper algo.

## El problema real

Antes, `auth_controller.php` hacía TODO junto:

1. leía `$_POST`,
2. validaba email y password,
3. consultaba PostgreSQL,
4. hablaba con Redis,
5. generaba hashes,
6. emitía cookies JWT,
7. resolvía fallbacks,
8. devolvía JSON.

Eso es como querer que el mismo operario atienda al cliente, haga la mezcla del cemento, maneje la grúa y además firme los planos. Funciona... hasta que deja de funcionar.

## Entonces, ¿qué es una Service Layer simple?

Es una capa intermedia MUY concreta:

- recibe datos ya extraídos del request,
- ejecuta reglas de negocio,
- usa helpers/DB/Redis directamente,
- devuelve un resultado claro al controller.

Nada más.

## OJO: simple no significa amateur

Simple significa que no agregamos complejidad falsa.

En este refactor **NO** metimos:

- objetos de dominio,
- interfaces,
- repositories,
- factories,
- contenedores extraños.

¿Y sabés por qué? Porque el problema NO lo necesitaba.

Si tu caso se resuelve con funciones claras en `src/services/auth_service.php`, meter 8 capas sería humo arquitectónico. Y el humo, hermano, no sostiene producción.

## ¿Por qué extraemos código del controller?

### 1. Porque el controller debería hablar HTTP, no negocio

El controller tiene que ocuparse de cosas como:

- método `GET` o `POST`,
- leer `$_POST`,
- devolver `json_encode(...)`,
- capturar excepciones.

No debería decidir cómo autenticar, cómo registrar o cómo hacer fallback seguro.

### 2. Porque las reglas viven mejor juntas

El login híbrido DB → Redis y el registro Redis → DB son reglas de autenticación.
Si esas reglas quedan repartidas por el controller, después cambiarlas es un infierno.

En cambio, en un service están agrupadas por intención.

### 3. Porque seguridad y fallback tienen que ser explícitos

Acá hay un aprendizaje CLAVE: cuando Redis falla, el sistema no puede “degradar lindo” haciendo cualquier cosa.

El fallback solo es válido si:

1. revalidás el payload,
2. rechecás unicidad del email en DB,
3. podés demostrar que el alta sigue siendo segura.

Si no podés probar eso, rechazás la operación.

Y esto es IMPORTANTÍSIMO: un fallback inseguro no es resiliencia. Es una vulnerabilidad con marketing.

### 4. Porque el código queda más fácil de mantener

Ahora el flujo queda así:

- `auth_controller.php` = capa HTTP
- `auth_service.php` = reglas de auth
- `AuthHelper`, `Database`, `RedisConfig` = infraestructura reutilizable

Eso hace que cuando tengas que tocar auth, sepas EXACTAMENTE dónde mirar.

## Regla práctica para tu cabeza

Preguntate esto:

> “¿Este archivo está tomando decisiones de negocio o solo coordinando entrada/salida?”

Si está tomando decisiones de negocio, probablemente ya pide un service.

## Conclusión

Extraer lógica del controller no es moda. Es orden mental traducido a código.

Primero separás responsabilidades.
Después entendés mejor el sistema.
Y recién ahí podés escalar sin convertir cada cambio en una ruleta rusa.

Es así de simple.
