---
name: viva-js
description: >
  Viva project JavaScript conventions: Vanilla Clean Architecture with ES6 Modules, EventRouter, and Controllers/Services/Domain layers. Trigger: When writing or modifying JS code in Viva.
license: Apache-2.0
metadata:
  author: gentleman-programming
  version: "1.0"
---

## When to Use

- Writing new JS files (controllers, services, domain)
- Modifying existing JS in `src/scripts/`
- Adding event handlers to views
- Making API/fetch calls from frontend
- Any frontend work in the Viva project

## Critical Patterns

### 1. Architecture: 3 Layers (Services / Domain / Controllers)

```
src/scripts/
├── services/     # API calls, Redis, external data (read/write)
├── domain/       # Business logic, transformations, validation
├── controllers/  # UI logic, DOM manipulation (thin, delegate to services/domain)
├── utils/        # Utilities (EventRouter, helpers)
└── main.js       # Entry point, wires everything via EventRouter
```

### 2. EventRouter (DOM Event Delegation) — MANDATORY

Events go through EventRouter. **NEVER use inline JS** (`onclick=`, `onchange=`, etc.).

```html
<!-- WRONG ❌ -->
<button onclick="myFunc()">Click me</button>

<!-- RIGHT ✅ -->
<button data-action="my-action">Click me</button>
```

Register in `main.js`:
```javascript
import { eventRouter } from './utils/EventRouter.js';
import { MyController } from './controllers/MyController.js';

const controller = new MyController();
eventRouter.register('my-action', () => controller.handleAction());
```

**data-action** uses smart defaults per tag:
- `<form>` → listens to `submit`
- `<button>`, `<a>`, `<div>` → listens to `click`
- `<select>`, `<input type="checkbox|radio|file">` → listens to `change`
- `<input type="text">`, `<textarea>` → listens to `input`

Override with explicit event type:
```html
<button data-event="submit:login-form">Login</button>
```

### 3. BASE_URL — MANDATORY for Fetch

All API calls MUST use `BASE_URL`. Never use relative paths.

```javascript
// WRONG ❌
fetch('src/api/auth/login.php', ...)

// RIGHT ✅
fetch(BASE_URL + 'api/auth/login.php', ...)
```

For standalone files (like `admin_crud.js`):
```javascript
const baseUrl = window.BASE_URL || '/';
fetch(baseUrl + 'api/admin_crud.php', ...)
```

### 4. Controllers: Thin, Delegate to Services/Domain

```javascript
// controllers/MyController.js
export class MyController {
    handleAction() {
        // 1. Get data from DOM if needed
        // 2. Call Service (API/Redis)
        // 3. Call Domain for logic/transformation
        // 4. Update UI
    }
}
```

Controllers should NOT:
- ❌ Make direct `fetch` calls (delegate to Service)
- ❌ Have business logic (delegate to Domain)
- ❌ Add `addEventListener` directly to DOM elements (use EventRouter)

### 5. Services: API/Redis Access Only

```javascript
// services/MyService.js
export class MyService {
    async getItems() {
        const res = await fetch(BASE_URL + 'api/items.php');
        return res.json();
    }

    async createItem(data) {
        const res = await fetch(BASE_URL + 'api/items.php', {
            method: 'POST',
            body: JSONFormData(data)
        });
        return res.json();
    }
}
```

### 6. Toast Notifications

Use `window.showToast()` for user feedback:

```javascript
window.showToast('Operación exitosa', 'success');
window.showToast('Hubo un error', 'error');
window.showToast('Warning message', 'warning');
```

### 7. No console.log in Production

Remove all `console.log` statements. Only use `error_log()` in PHP or browser DevTools for debugging.

## File Naming

| Type | Pattern | Example |
|------|---------|---------|
| Controller | `{Name}Controller.js` | `CartController.js` |
| Service | `{Name}Service.js` | `AuthService.js` |
| Domain | `{Name}Domain.js` | `CartDomain.js` |
| Utility | PascalCase or `{name}.js` | `EventRouter.js`, `formatters.js` |
| Entry | `main.js` | `src/scripts/main.js` |

## View HTML Rules

- **NO inline JS** (`onclick`, `onchange`, `oninput`)
- Use `data-action="action-name"` for simple click handlers
- Use `data-event="event-type:action-name"` for specific events
- All interactive elements must have a corresponding entry in `main.js`

## PHP Views (.view.php)

When including JS in PHP views, use ES6 modules:

```php
<script type="module" src="<?= $BASE_URL ?>src/scripts/main.js"></script>
```

## Commands

```bash
# Project entry point
src/scripts/main.js
```

## Common Patterns Reference

### Form Submit (EventRouter)
```javascript
// HTML
<form data-action="login">
    <button type="submit">Login</button>
</form>

// Controller
handleLogin(e) {
    e.preventDefault(); // ← MANDATORY on submit handlers
    const formData = new FormData(e.target);
    // ...
}
```

### Profile Controller receives element as param (EventRouter)
```javascript
export class ProfileController {
    toggleEdit(element) {
        // element is passed by EventRouter
        // NOT: document.getElementById(...)
    }
}
```

### Redis fallback for async registration (Ghost State)
- In `navbar_usuario.php`, if JWT is valid but user not in Postgres, query Redis.
- Dropdown stays empty (only "Cerrar Sesión").
- Use `RedisConfig::getConnection()` to access Redis.

## Anti-Patterns to Avoid

- ❌ `document.getElementById('id').addEventListener(...)` in controllers
- ❌ `onclick="..."` in HTML
- ❌ `fetch('/api/...')` without BASE_URL
- ❌ Business logic in controllers
- ❌ Direct API calls in controllers
- ❌ Hardcoded menu items or photos in PHP
