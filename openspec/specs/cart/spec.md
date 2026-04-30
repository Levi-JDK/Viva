# Spec: Cart Bugfix Trio

> Three critical bugs in the Redis→PostgreSQL cart sync pipeline.
> Date: 2026-04-29 | Change: `cart-bugfix-trio`

---

## Bug 1: Pending Actions Never Cleared

### Problem
`CartService::redisUpdate()` returns a response without the processed `acciones` array. The JS `schedulePendingSync().then()` callback receives `result?.actions` which is always `undefined`, so `cartStore.clearSyncedPendingActions(undefined)` does nothing. The pending action queue grows unbounded and every page re-syncs all historical actions.

Additionally, even if the server returned actions, the key-matching in `CartStore.buildPendingActionKey()` includes `client_ts` — but server-normalized actions strip this field, so keys would never match.

### Root Cause
- **Server** (`CartService.php:70-76`): `redisUpdate()` returns `{success, message, hash_key, items_count, updated_at}` — no `actions` field.
- **Client** (`CartController.js:179-180`): `.then(result => { cartStore.clearSyncedPendingActions(result?.actions || []); ... })` — `result.actions` is always undefined.
- **Client** (`CartStore.js:172-178`): `buildPendingActionKey()` joins `accion|id_producto|cantidad|client_ts`. Server-normalized actions have no `client_ts`, so even if returned, keys won't match pending actions.

### Requirements

#### REQ-1.1: `redisUpdate()` returns processed actions
`CartService::redisUpdate()` MUST include the normalized `acciones` array in its JSON response:
```json
{
  "success": true,
  "message": "Carrito consolidado en Redis",
  "hash_key": "viva:carrito:user:1",
  "items_count": 3,
  "updated_at": "2026-04-29 10:30:00",
  "acciones": [
    {"accion": "agregar", "id_producto": 42, "cantidad": 2},
    {"accion": "eliminar", "id_producto": 7, "cantidad": null}
  ]
}
```

#### REQ-1.2: JS clears matching pending actions
`CartController.schedulePendingSync().then()` MUST pass the returned `acciones` to `cartStore.clearSyncedPendingActions()`. The existing code at line 180 already does this — it just needs the server to return the data.

#### REQ-1.3: Action key matching with `client_ts` echo
`redisUpdate()` MUST echo back the original `client_ts` for each action it received. Each normalized action in the response includes the `client_ts` from the request so the client can match exactly via `buildPendingActionKey()`.

#### REQ-1.4: `hasPendingActions()` accuracy
After successful sync and clearing, `cartStore.hasPendingActions()` MUST return `false` when all pending actions were synced. If new actions were enqueued during the async round-trip (user clicked "add" while sync was in-flight), those remain and `hasPendingActions()` returns `true`.

### Acceptance Criteria
- [ ] After a single "add to cart" action and successful debounced sync, `cartStore.getPendingActions()` returns `[]`.
- [ ] After rapid-fire adds (3 items in 200ms), only the last batch's actions remain pending until the next debounce fires.
- [ ] `hasPendingActions()` returns `true` only when there are genuinely unsynced actions.

---

## Bug 2: Double-Application Race on `beforeunload`

### Problem
When the user navigates away, `beforeunload` triggers `flushToPostgresOnClose()`. This calls `cancelPendingActionsDebounce()` which only clears the pending timer — it does NOT cancel an already-in-flight fetch request. If the debounced sync was already sent (fetch in progress), both that in-flight request AND the flush request will process the same actions, causing double-application in PostgreSQL.

The server has no deduplication mechanism to detect and reject duplicate action batches within a short time window.

### Root Cause
- **Client** (`CartService.js:127-135`): `cancelPendingActionsDebounce()` only calls `clearTimeout()` — cannot abort an already-sent `fetch()`.
- **Client** (`CartController.js:234-256`): `flushToPostgresOnClose()` cancels debounce, then sends ALL pending actions via `flushToPostgresKeepalive()`. If the debounced sync is in-flight, same actions go twice.
- **Server**: No deduplication key or `client_ts` window check on either `redisUpdate` or `flushToPostgres`.

### Requirements

#### REQ-2.1: Client-side in-flight exclusion
When `flushToPostgresOnClose()` fires, it MUST NOT send actions that are already in-flight via the debounced sync. 

Implementation: Track a `syncInFlightActions` Set of action keys that have been sent but not yet confirmed. `flushToPostgresOnClose()` filters out any pending actions whose keys are in this set.

#### REQ-2.2: Server-side deduplication via `client_ts`
Both `redisUpdate()` and `flushToPostgres()` MUST detect and reject duplicate action batches within a configurable time window (default: 5 seconds).

Implementation: Use a Redis-based deduplication key per user:
```
{prefix}cart_dedup:user:{user_id}:{batch_hash}
```
Where `batch_hash = sha256(client_ts_sorted_concatenation)`. If the key exists and is within the TTL window, return `{success: true, message: "Duplicate batch ignored", deduplicated: true}` without processing.

#### REQ-2.3: `client_ts` in action payloads
Every action sent to the server MUST include a `client_ts` field (milliseconds since epoch). The server uses this for deduplication. The JS `buildAction()` already generates `client_ts` (line 79 of CartService.js) but it is not used server-side.

#### REQ-2.4: `flushToPostgres` idempotency
`CartService::flushToPostgres()` MUST apply the same deduplication logic as `redisUpdate()`. If a batch was already processed by `redisUpdate()` within the dedup window, `flushToPostgres()` returns success without re-applying.

### Acceptance Criteria
- [ ] Rapid page navigation after adding an item does NOT double the quantity in PostgreSQL.
- [ ] If debounced sync is in-flight and `beforeunload` fires, the flush either skips in-flight actions OR the server detects the duplicate and ignores it.
- [ ] Server returns `deduplicated: true` when a duplicate batch is detected within the time window.
- [ ] Deduplication keys auto-expire (TTL 5s) so they don't accumulate in Redis.

---

## Bug 3: Worker Reads Stale Redis Hash

### Problem
`pushCartQueueJob()` pushes a job to the Redis list with only metadata (`user_id`, `session_key`, `actions_hash`, `queued_at`). When the worker picks up the job, `ProcessCartJob::fromRedis()` re-reads the Redis hash to get `acciones_json`. This is a race condition: if another `redisUpdate()` call happens between queueing and processing, the hash contains NEWER data, and the worker processes the wrong actions.

Additionally, `Worker::procesarCarrito()` deletes the Redis hash after processing (`$this->redis->del($job->getSessionKey())`). This means any pending actions that arrived after the job was queued but before the worker processed them are permanently lost.

### Root Cause
- **Server** (`CartService.php:240-259`): `pushCartQueueJob()` only pushes metadata, not the actual actions or snapshot.
- **Worker** (`ProcessCartJob.php:51-77`): `fromRedis()` calls `$redis->hGetAll($key)` to read actions from the hash — this is the LIVE hash, not the snapshot at queue time.
- **Worker** (`Worker.php:191`): `$this->redis->del($job->getSessionKey())` deletes the hash after processing, losing any subsequent actions.

### Requirements

#### REQ-3.1: Job payload contains complete snapshot
`pushCartQueueJob()` MUST include the COMPLETE snapshot data (all product IDs and quantities) in the job payload at the moment of queueing:
```json
{
  "user_id": 1,
  "session_key": "viva:carrito:user:1",
  "actions_hash": "abc123...",
  "queued_at": "2026-04-29 10:30:00",
  "snapshot": {
    "42": 2,
    "7": 1,
    "15": 3
  },
  "acciones": [
    {"accion": "agregar", "id_producto": 42, "cantidad": 2, "client_ts": 1714392600000}
  ]
}
```

The `snapshot` field contains the full cart state AFTER applying all actions. The `acciones` field contains the normalized actions that produced this snapshot.

#### REQ-3.2: Worker uses payload data directly
`ProcessCartJob::handle()` MUST use the snapshot/actions from the job payload directly, NOT re-read from Redis. The `fromRedis()` factory method MUST be replaced with a `fromPayload()` method that constructs the job from the JSON payload alone.

The job's `handle()` method will:
1. Clear existing cart items for the user in PostgreSQL (`limpiar`).
2. Re-add each item from the snapshot (`agregar` with full quantity).
3. This is equivalent to the current `flushToPostgres` approach but uses the queued snapshot.

#### REQ-3.3: Worker does NOT delete Redis hash
`Worker::procesarCarrito()` MUST NOT delete the Redis hash after processing. The hash remains as the live state for subsequent `redisUpdate()` calls. Only the queue message is consumed (via `BRPOP`).

The hash will be cleaned up by:
- Natural TTL expiration (currently 86400s / 24h set in `writeRedisCartSnapshot`).
- Explicit deletion ONLY when `flushToPostgres()` completes successfully (existing behavior, unchanged).

#### REQ-3.4: Idempotency preserved
The existing idempotency mechanism (`claimCartJob` / `markCartJobProcessed`) MUST continue to work. The `actions_hash` in the payload is used for deduplication — it must be computed from the `acciones` array in the payload, not from the live hash.

### Acceptance Criteria
- [ ] Worker processes the exact snapshot that existed at queue time, regardless of subsequent `redisUpdate()` calls.
- [ ] After worker processes a job, the Redis hash still exists and contains the latest cart state.
- [ ] A second `redisUpdate()` call after queueing but before processing creates a NEW job with the updated snapshot (not lost).
- [ ] Duplicate queue messages (same `actions_hash`) are detected and skipped by the existing idempotency guard.

---

## Cross-Cutting Concerns

### Security
- All deduplication keys MUST use user-scoped Redis keys (include `user_id`).
- `client_ts` values MUST be validated server-side (reject timestamps > 60s in the future or > 24h in the past).
- No credentials or sensitive data in job payloads.

### Performance
- Job payload size: snapshot + actions for typical cart (< 50 items) is < 5KB — well within Redis list limits.
- Deduplication TTL of 5s is sufficient for the `beforeunload` race window without accumulating stale keys.

### Backward Compatibility
- The `acciones` field added to `redisUpdate()` response is additive — existing clients that don't use it are unaffected.
- `client_ts` in action payloads is additive — existing actions without it are handled gracefully (treated as `null` for dedup).
