# Proposal: invert-register-email-validation

## Intent
Fix inverted email validation in `RegisterService::registrarUsuarioEnBaseDatos`. New users rejected. Existing users pass through.

## Root Cause

**`fun_val_mail(email)`** (Postgres function):
- Returns `TRUE` → email is valid / NOT in DB (new user)
- Returns `FALSE` → email is taken / EXISTS in DB

**PHP code** at `RegisterService.php:106-113`:
```php
$stmtCheck = $db->ejecutar('validarEmail', [':email' => $email]);
$existeEmail = $stmtCheck->fetchColumn();
if ($existeEmail) {  // BUG: TRUE means NEW, not "exists"
    return ['mensaje' => 'El correo ya está registrado.', ...];
}
```

Variable name `$existeEmail` implies TRUE = exists. But `fun_val_mail` returns TRUE = valid/new.

**Impact**: Worker calls `registrarUsuarioEnBaseDatos`, gets `TRUE` for new emails → returns early → worker deletes Redis hash → user never inserted into Postgres. Registration silently dead.

## Scope

### In Scope
- Fix condition in `src/services/RegisterService.php` line 108.

### Out of Scope
- `fun_c_user` (SQL) — correct, separately validates.
- `fun_val_log` (SQL) — correct, used for login.
- Redis worker logic — bug is in PHP condition, not worker.

## Capabilities

### Modified Capabilities
- `user-registration`: Fixes inverted email validation gate.

## Approach

**Fix A** (minimal): `if (!$existeEmail)` — invert condition only.
**Fix B** (clean): Rename variable to `$isEmailValid`, use `if (!$isEmailValid)` — clarifies semantics.

Recommend **Fix B** — prevents future confusion. One-line semantic change.

```php
// Current (broken):
$existeEmail = $stmtCheck->fetchColumn();
if ($existeEmail) {

// Fixed:
$isEmailValid = $stmtCheck->fetchColumn();
if (!$isEmailValid) {
```

## Affected Areas

| Area | Impact | Description |
|------|--------|-------------|
| `src/services/RegisterService.php:103-128` | Modified | Single condition in `registrarUsuarioEnBaseDatos`. |

## Risks

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| Worker must be restarted after deploy | High | Document in deploy notes. |
| `fun_c_user` double-validation catches any edge case regardless | Low | SQL function independently validates email — safe redundancy. |

## Rollback Plan
Revert one line in `RegisterService.php`.

## Dependencies
None.

## Success Criteria
- [ ] New user email passes PHP validation → inserted into Postgres via worker.
- [ ] Existing user email fails PHP validation → returns "El correo ya está registrado."
- [ ] `fun_c_user` SQL function behaves unchanged (it already checks correctly).
