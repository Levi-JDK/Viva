# Arquitectura del Carrito: Eventual Consistency + Server-Side Pre-flight Flush

## El Problema Anterior (Race Conditions)
El carrito sufría de pérdida de datos e inconsistencias entre tres capas:
1. Interfaz Local (JS Optimista)
2. Redis (Caché intermedia)
3. PostgreSQL (Fuente de verdad)

**Problemas detectados:**
- `loadCart()` sobrescribía los datos de la interfaz con datos viejos de PostgreSQL al abrir el carrito.
- El evento `visibilitychange` (cambiar de pestaña) forzaba guardados en segundo plano que borraban la sesión de Redis, corrompiendo el estado entre pestañas.
- Al ir al Checkout, PHP leía de PostgreSQL antes de que los procesos asíncronos en segundo plano (Worker) terminaran de guardar los últimos clics del usuario, resultando en cobros incorrectos o fallidos.

## Nueva Arquitectura (Híbrida)

### 1. Navegación Rápida (Consistencia Eventual)
- El usuario interactúa (agrega/elimina) y JS reacciona en 0ms.
- Se agrupan los cambios (Debounce 500ms) sin actividad.
- JS envía las acciones a PHP.
- PHP actualiza un **Redis Hash** e inyecta la orden en la cola `viva:cola:carrito`.
- Un **Async Worker** procesa la cola y sube los cambios a PostgreSQL en segundo plano.

### 2. Pago Seguro (Server-Side Pre-flight Flush)
- Cuando el usuario navega a `/checkout`, el controlador `CheckoutController` (PHP) intercepta la petición.
- **Freno Síncrono:** Revisa si el hash de Redis de ese usuario tiene cambios pendientes sin procesar.
- Si los hay, ignora la cola asíncrona y **fuerza el guardado directamente a PostgreSQL** en ese mismo instante.
- Luego, lee de PostgreSQL para renderizar la página.
- **Resultado:** Garantiza precisión transaccional absoluta al momento de cobrar, sacrificando sólo ~40ms de tiempo de carga en la página de checkout.

### 3. Ciclo de Vida de Pestañas
- Se elimina el destructivo `visibilitychange`.
- Solo se mantiene `beforeunload` para un último intento de rescate de datos si el usuario cierra la pestaña o navega fuera del sitio.
