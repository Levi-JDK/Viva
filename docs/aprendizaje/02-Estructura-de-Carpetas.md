# 02. Estructura de Carpetas en Laravel (Para nuestro caso)

Laravel viene por defecto con una estructura pensada para aplicaciones estándar (MVC). Nosotros **NO** vamos a usar la forma estándar, porque decidimos hacer **Hexagonal Adaptada**.

A continuación te explico qué es cada cosa que te da Laravel, y dónde vamos a poner *nuestro* código.

## Lo que Laravel te da (y no tocamos)

- `bootstrap/` -> Arranca el framework (no se toca).
- `config/` -> Archivos de configuración (conexiones a BD, Redis, variables).
- `public/` -> El único lugar accesible desde el navegador. Acá está el `index.php` que recibe TODO. Acá va a ir tu CSS (Tailwind) y tu JS Vanilla.
- `routes/` -> Acá definimos las URLs (`api.php` y `web.php`).
- `database/` -> Acá van las migraciones de BD (nosotros casi no lo usaremos porque la BD ya existe).
- `resources/views/` -> Acá van tus archivos HTML/Blade (tus actuales vistas PHP irán acá).

## La Carpeta `app/` (Donde ocurre la magia)

Laravel por defecto te da cosas como `app/Http/Controllers` y `app/Models`.
Nosotros vamos a agregar nuestras carpetas **Hexagonales**:

### 1. `app/Domain/` (El corazón)
Acá van las cosas que NO SABEN que están en una web, ni que usan Postgres.
- `Entities/`: Clases planas de PHP (structs) con propiedades. Ej: `User`, `Order`.
- `Ports/`: Interfaces. Ej: `OrderRepositoryInterface` (Solo dicen *qué* se puede hacer, no *cómo*).

### 2. `app/Application/` (Los casos de uso)
Acá orquestamos.
- `UseCases/`: Clases con un solo método `execute()`. Ej: `CreateOrderUseCase`. Le dice al repository "guardá esto".
- `Commands/` o `DTOs/`: Clases para pasar datos del Controller al UseCase.

### 3. `app/Infrastructure/` (La conexión con el mundo real)
Acá va el código "sucio", el que sí sabe de Postgres o de Redis.
- `PostgreSQL/`: Tus implementaciones. Ej: `PostgresOrderRepository` (esta clase hace el `DB::select('SELECT create_order(...)')`).
- `Redis/`: Cosas de colas.

### 4. `app/Http/` (La Presentación)
Esta ya viene con Laravel.
- `Controllers/`: Son **flacos**. Reciben el Request de Laravel, llaman a un Use Case de `app/Application/`, y devuelven un JSON o una Vista.
- `Middleware/`: (Ej: tu Patovica actual). Frenan el Request antes de llegar al controller si el JWT es inválido.

## Ejemplo de un flujo completo:

1. Usuario hace un POST a `/api/orders`
2. Pega en `routes/api.php`
3. Se ejecuta `app/Http/Middleware/JwtAuth` (Patovica). Si pasa:
4. Llega a `app/Http/Controllers/OrderController`
5. El Controller llama a `app/Application/UseCases/CreateOrderUseCase`
6. El UseCase llama a `app/Domain/Ports/OrderRepositoryInterface`
7. Mágicamente (Service Container), termina ejecutándose `app/Infrastructure/PostgreSQL/PostgresOrderRepository`
8. El Repository ejecuta la función PL/pgSQL en la Base de Datos.
9. Se devuelve todo hacia atrás.

Esto se ve complejo al principio, pero asegura que **nada se mezcle** y que puedas testear cada cajita por separado.