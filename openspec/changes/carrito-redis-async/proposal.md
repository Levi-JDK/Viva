# Proposal: carrito-redis-async

## Intent
Migrate shopping cart from sync Postgres (`fun_carrito`) to async Redis queue. Fix lock/latency issues. Fast frontend feedback.

## Scope

### In Scope
- Frontend: Local state sync + `navigator.sendBeacon` on `beforeunload`/`visibilitychange`.
- Backend PHP API: Fast endpoints receive cart ping, push to Redis.
- Worker: PHP CLI (Predis) daemon reads Redis queue, calls `fun_carrito` in Postgres.
- Checkout: Sync flush constraint before payment.

### Out of Scope
- Complete cart redesign.
- Real-time websockets (unnecessary).

## Capabilities

### New Capabilities
- `cart-async-worker`: Background synchronization capability.
- `cart-flush`: Sync flush API capability for checkout.

### Modified Capabilities
- `shopping-cart`: Modifies storage mechanism and sync flow.

## Approach
1. Frontend updates local cart. Debounced API call or `sendBeacon` sends actions to `/api/cart/ping`.
2. `/api/cart/ping` uses Predis to `RPUSH` actions to `cart:queue`.
3. Worker `src/workers/cart_worker.php` runs loop (`BLPOP cart:queue`). Parses JSON, executes `fun_carrito(...)`.
4. `/api/checkout/init` forces flush of user's Redis actions and awaits `fun_carrito` sync before generating payment.

## Affected Areas

| Area | Impact | Description |
|------|--------|-------------|
| `src/scripts/cart.js` | Modified | Local state + Beacon flush. |
| `src/api/cart_ping.php` | New | Light endpoint, writes to Redis. |
| `src/api/checkout.php` | Modified | Adds flush barrier before processing. |
| `src/workers/cart_worker.php` | New | Predis daemon for Postgres insert. |

## Risks

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| Data loss on page close | Med | `navigator.sendBeacon` + visibility events. |
| Worker crash | Low | Supervisor to restart. DLQ for failed JSON parsing. |
| Checkout race condition | High | Lock/Flush sync constraint on checkout mandatory. |

## Rollback Plan
1. Revert JS to direct synchronous API calls.
2. Route API calls directly to `fun_carrito` instead of Redis queue.
3. Stop worker.

## Dependencies
- `predis/predis` (already in composer).
- Supervisor/Systemd for worker process.

## Success Criteria
- [ ] Cart item add < 50ms API response.
- [ ] Worker processes queue in background.
- [ ] Checkout successful with accurate database state.