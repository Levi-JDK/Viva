# Tasks: carrito-redis-async

## Phase 1: Backend Redis cart contract

- [x] 1.1 Interceptar `action=redis_update` en `src/controllers/carrito.controller.php` para validar sesión, recibir acciones acumuladas y delegar en `CartService` sin llamar directo a `fun_carrito`.
- [x] 1.2 Extender `src/services/CartService.php` con método para encolar acciones de carrito en Redis, normalizar shape de mensaje y guardar snapshot rápido por usuario para lectura/flush.
- [x] 1.3 Ajustar `src/workers/Worker.php` y/o `src/workers/Jobs/ProcessCartJob.php` para consumir mensajes JSON de `viva:cola:carrito`, mapear acciones soportadas y ejecutar `fun_carrito(...)` en PostgreSQL con retries/DLQ.

## Phase 2: Frontend beacon wiring

- [x] 2.1 Modificar `src/scripts/services/CartService.js` para separar lectura síncrona (`getCart`) de escritura async (`add/remove/update/clear`) contra nuevo endpoint `api/cart_ping`, incluyendo helper `sendBeacon` con fallback `fetch(..., { keepalive: true })`.
- [x] 2.2 Modificar `src/scripts/controllers/CartController.js` para mantener estado local optimista, registrar acciones pendientes y disparar flush por `visibilitychange`/`beforeunload` sólo si usuario logueado y hay cola local.
- [x] 2.3 Extender `src/scripts/domain/CartStore.js` con metadata mínima de sincronización (`pendingActions`, `lastSyncedAt`, flag de flush`) para evitar perder cambios o duplicar beacon.
- [ ] 2.4 Ajustar `src/scripts/main.js` para inicializar hooks globales de flush del carrito una sola vez durante `DOMContentLoaded`.

## Phase 3: Checkout flush barrier

- [x] 3.1 Interceptar `action=flush_to_postgres` en `src/controllers/carrito.controller.php` para flushear snapshot Redis de usuario autenticado y confirmar persistencia antes de pago.
- [x] 3.2 Extender `src/services/CartService.php` con `flushToPostgres()` para consolidar hash Redis por usuario, persistir vía `fun_carrito` y limpiar hash al finalizar.
- [x] 3.3 Modificar `src/views/checkout.view.php` para que botón `#btn-pagar` primero invoque flush endpoint, deshabilite UI mientras espera, muestre error si flush falla y recién después abra ePayco.
- [ ] 3.4 Ajustar `src/controllers/checkout.controller.php` para usar carrito ya flusheado al render inicial o redirigir si flush deja carrito vacío.

## Phase 4: Verification

- [ ] 4.1 Probar flujo manual: agregar, actualizar, eliminar y limpiar desde drawer; cerrar pestaña/ocultar página; verificar que worker persiste cambios correctos en `fun_carrito`.
- [ ] 4.2 Probar checkout con backlog pendiente: flush previo debe dejar totales e ítems exactos antes de `handler.open(data)` y evitar pago con carrito desfasado.
- [ ] 4.3 Probar fallas controladas de Redis/worker: endpoint debe responder JSON consistente, no inventar fallbacks inseguros y checkout debe bloquearse si flush no confirma persistencia.
