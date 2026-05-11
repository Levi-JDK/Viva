# Skill Registry - Viva Project

**Delegator use only.** Any agent that launches sub-agents reads this registry to resolve compact rules, then injects them directly into sub-agent prompts. Sub-agents do NOT read this registry or individual SKILL.md files.

Generated: 2026-05-07

## User Skills

| Trigger | Skill | Path |
|---------|-------|------|
| "iniciar sdd" | sdd-init | ~/.config/opencode/skills/sdd-init/SKILL.md |
| "explorar" | sdd-explore | ~/.config/opencode/skills/sdd-explore/SKILL.md |
| "proponer" | sdd-propose | ~/.config/opencode/skills/sdd-propose/SKILL.md |
| "especificar" | sdd-spec | ~/.config/opencode/skills/sdd-spec/SKILL.md |
| "diseñar" | sdd-design | ~/.config/opencode/skills/sdd-design/SKILL.md |
| "tareas" | sdd-tasks | ~/.config/opencode/skills/sdd-tasks/SKILL.md |
| "implementar" | sdd-apply | ~/.config/opencode/skills/sdd-apply/SKILL.md |
| "verificar" | sdd-verify | ~/.config/opencode/skills/sdd-verify/SKILL.md |
| "archivar" | sdd-archive | ~/.config/opencode/skills/sdd-archive/SKILL.md |
| "modelado db", "sql model", "crear tabla", "ddl" | modelado-db | ~/.config/opencode/skills/modelado-db/SKILL.md |
| "pull request", "PR", "crear PR" | branch-pr | ~/.config/opencode/skills/branch-pr/SKILL.md |
| "ui design", "ux", "diseño" | ui-ux-pro-max | ~/.config/opencode/skills/ui-ux-pro-max/SKILL.md |
| "judgment day", "dual review", "juzgar" | judgment-day | ~/.config/opencode/skills/judgment-day/SKILL.md |
| "crear issue", "reportar bug" | issue-creation | ~/.config/opencode/skills/issue-creation/SKILL.md |
| "crear skill", "agent instructions" | skill-creator | ~/.config/opencode/skills/skill-creator/SKILL.md |

## Project-Level Skills

| Trigger | Skill | Path |
|---------|-------|------|
| Vanilla JS, ES6 Modules, EventRouter | viva-js | .atl/skills/viva-js/SKILL.md |
| "revisar seguridad", "security audit" | security-audit | .atl/skills/security-audit/SKILL.md |
| "crear api", "new api", "generar endpoint" | create-api | .atl/skills/create-api/SKILL.md |
| "crear helper", "new helper" | create-helper | .atl/skills/create-helper/SKILL.md |
| "Crear pipeline Redis-PostgreSQL", "Crear worker asíncrono" | redis-async-worker | .atl/skills/redis-async-worker/SKILL.md |
| "crear funcion sql", "generar funcion bd" | create-sql-function | .atl/skills/create-sql-function.md |
| "sincronizar requerimientos", "actualizar reqs desde código", "documentar lo implementado", "req-sync" | viva-req-sync | .agents/skills/viva-req-sync/SKILL.md |

## Compact Rules

Pre-digested rules per skill. Delegators copy matching blocks into sub-agent prompts as `## Project Standards (auto-resolved)`.

### viva-js
- Architecture: 3 layers — Services (API calls), Domain (pure logic), Controllers (UI/DOM)
- Services: fetch/API calls only, return data, NO DOM manipulation
- Domain: pure functions, transformations, validation, NO side effects or imports from services/controllers
- Controllers: thin, delegate to Services + Domain, handle data-action events
- EventRouter: single listener on document.body, register via `eventRouter.register('action-name', callback)`
- API calls: use ApiService or custom Service, never raw fetch in controller
- Error responses: `{exito: bool, mensaje: string}` — Controllers show Toast on error
- All controllers export singleton instance, imported in main.js

### redis-async-worker
- Use **Predis** (pure PHP via composer), NOT phpredis extension
- Queue keys: `viva:queue:{name}` for lists, `viva:cola:{name}` for Spanish-named queues
- DLQ key: `viva:cola:deadletter` — failed jobs land here after max retries
- Retry: exponential backoff [1, 5, 30, 60] seconds, max 3 retries
- Worker: PHP CLI daemon using BLPOP, needs supervisor/systemd
- Job payload MUST include complete snapshot data (not references to live Redis state)
- Always validate and sanitize BEFORE Redis insert — NO blind fallbacks on catch
- Jobs in src/workers/Jobs/, Config in src/workers/Config/
- Worker deletes job from queue after processing (BRPOP auto-consumes)

### security-audit
- Verify .env is in .gitignore — FAIL if tracked
- Scan for hardcoded credentials, JWT secrets, API keys in code
- Check ALL API endpoints sanitize inputs and validate JWT/signatures
- Verify error responses don't expose DB schema, stack traces, or internals
- Check Redis fallbacks replicate ALL business validation (no blind inserts)
- Verify all PDO uses parameterized queries (ejecutar() with named params)
- Check session config: httponly=true, samesite=Lax/Strict, secure=true in prod
- Verify no null coalescing (??) with hardcoded credential defaults

### create-api
- File path: src/api/{name}.php
- Header: `header('Content-Type: application/json; charset=utf-8')`
- Method: POST for mutations, GET for reads
- Input: `json_decode(file_get_contents('php://input'), true)` with validation
- Response: `{exito: bool, mensaje: string, data?: any}` — consistent format
- Auth: `AuthHelper::protectRoute()` for protected endpoints
- Sanitize ALL inputs before processing
- DB: `Database::getInstance()->ejecutar('queryName', [:param => $value])`
- Handle errors with try/catch, return JSON with exito: false

### create-helper
- File path: src/functions/{name}.php
- Function names: snake_case
- Document with PHPDoc: @param, @return, @throws
- Avoid global state, prefer parameters for inputs
- Return consistent types, throw Exceptions for errors
- Use $_ENV for config values, never hardcode
- One helper file = one logical concern

### create-sql-function
- Prefix: fun_c_ (create), fun_u_ (update), fun_d_ (delete), fun_val_ (validate)
- Parameters: use `tabla.columna%TYPE`, NO `::` casting anywhere
- Numeric PKs: auto-generate via `COALESCE(MAX(columna_id), 0) + 1`
- Soft delete: `is_deleted BOOLEAN DEFAULT FALSE` with audit trigger
- All functions go in `scripts/funciones_db/`
- Favor set-based operations over row-by-row (no loops in SQL)

### modelado-db
- Tables: prefix `tab_` + snake_case, always `CREATE TABLE IF NOT EXISTS`
- PK: `id_{table_singular}` (e.g., `id_user`, `id_product`)
- FK: `id_{referenced_table}` (e.g., `id_user`, `id_productor`)
- Timestamps: `created_at`, `updated_at` (with trigger)
- Audit: `created_by`, `updated_by` (set via trigger with current_user)
- Soft delete: `is_deleted BOOLEAN DEFAULT FALSE`
- Use VARCHAR(n) with reasonable limits, not TEXT by default
- Add `updated_at` trigger with `CURRENT_TIMESTAMP`
- Index FKs and frequently queried columns

### branch-pr
- Every PR MUST link an approved issue — no exceptions
- Every PR MUST have exactly one `type:*` label
- PR title: `type(scope): description` (conventional commits)
- PR body: Summary + Changes + Testing sections
- Branch: `{type}/{issue-number}-{description}`
- Automated checks must pass before merge
- Never force push to main/master

### judgment-day
- Launch TWO blind independent judge sub-agents simultaneously (no communication)
- Target: correctness, security, performance, conventions
- Synthesize: common findings = confirmed, unique = consider
- Apply fixes for confirmed issues only
- Re-judge up to 2 iterations or until both pass
- Escalate to human if disagreement persists after 2 iterations

### ui-ux-pro-max
- Viva stack: HTML/CSS + Tailwind — use Tailwind-specific instructions
- Custom palette: principal (#b15b0a), claro (#F5E9D3), oscuro (#4A3B2B), verde-artesanal (#6B8E23), naranja-artesanal (#D2691E)
- Font: Outfit (sans-serif) via Google Fonts
- Breakpoints: xs(375) sm(640) md(768) lg(1024) xl(1280) 2xl(1440)
- Prefer rem over px (e.g., `max-w-[42rem]` not `max-w-[672px]`)
- Split UI cards cap: `max-w-[42rem]` max for balanced desktop proportions
- Apply Tailwind consistently, minimize arbitrary values

### issue-creation
- Use templates when available
- Format: Summary + Steps to Reproduce (bugs) + Expected vs Actual
- Labels: type:bug, type:feature, type:enhancement, type:docs
- Bugs include: environment, logs, screenshots
- One issue = one problem/feature
- Reference related issues/PRs

### skill-creator
- Follow Agent Skills spec: YAML frontmatter + markdown body
- Required fields: name, description (with "Trigger:"), license, metadata
- Include Critical Patterns section with actionable rules
- Rules format: "do X", "never Y", "prefer Z over W"
- One-line code examples where critical
- SKILL.md extension required
- Place in appropriate skill directory

### viva-req-audit
- **RF (Funcionales)**: Escribir como lo diría un usuario/PO. ❌ "dashboard" → ✅ "panel de control". ❌ "header del drawer" → ✅ "parte superior del panel lateral". ❌ "botón hamburguesa" → ✅ "botón de tres líneas". ❌ "scroll" → ✅ "desplazarse". ❌ "meta descripción" → ✅ "descripción para buscadores".
- **💡 Pregunta filtro**: "¿Le mostraría esto a un cliente y diría 'tiene sentido'?" — si hay que explicar, reescribir.
- **RNF (No Funcionales)**: Conceptos técnicos SÍ (JWT, Redis, PostgreSQL, scroll smooth, tokens), código literal NO.
- **Regla de oro RNF**: Si lo dirías en una reunión de arquitectura → ✅. Si lo copiaste del código → ❌.
- **Duplicados**: Si hay `[MOVIDO DE]` en el destino, eliminar del origen.
- **Cobertura**: RF cubre front (UI visible en lenguaje de usuario), RNF cubre back (lógica, seguridad, performance, arquitectura).

### viva-req-sync
- **Cobertura**: 29 módulos — cubre TODOS los controladores (22), vistas (18), functions (13), services (18 PHP), scripts JS (37), workers (7), API (3), styles (2), utils (2)
- **Exhaustividad total**: leer CADA archivo completo del módulo (views, controllers, functions, scripts JS, services, workers)
- **Algoritmo**: Código → Requerimiento → ¿Está en doc? → Si NO → Agregar
- **Separar**: funcionales (UI: textos, colores, interacciones, navegación) de no funcionales (lógica: validaciones, queries, rutas, cálculos)
- **Comparación**: semántica con doc existente, no texto literal
- **Reqs nuevos**: formato `| # | Descripción |   | SI/NO/ADI | [NUEVO] |`, numeración con decimales (ej: 42.1)
- **Reportar**: al orchestrator después de CADA módulo y ESPERAR confirmación
- **Documento destino**: `docs/requerimientos.md`

**Mapeo completo de 29 módulos:**

**🏠 FRONTEND / PÚBLICO (18 módulos)**
| # | Módulo | Controladores | Vistas | Scripts JS | Workers | Services/Domain |
|---|--------|--------------|--------|------------|---------|-----------------|
| 1 | Landing | `index.controller.php` | `index.view.php` | `controllers/LandingController.js` | — | `services/HomeService.php` |
| 2 | Login | `login.controller.php` `logout.controller.php` | `login.view.php` | `controllers/AuthController.js` | — | `services/LoginService.php` `functions/auth_helper.php` |
| 3 | Registro | `register.controller.php` | `registro.view.php` | `controllers/AuthController.js` | `workers/Jobs/RegisterUserJob.php` | `services/RegisterService.php` |
| 4 | Recuperar Pass | `recuperar.controller.php` | `recuperar.view.php` | `controllers/PasswordRecoveryController.js` | — | `services/RecoveryService.php` `scripts/services/PasswordRecoveryService.js` `scripts/services/PasswordService.js` `scripts/domain/PasswordValidator.js` |
| 5 | Catálogo | `catalogo.controller.php` | `catalogo.view.php` | `controllers/CatalogController.js` | — | `scripts/services/CatalogService.js` |
| 6 | Producto Detalle | `producto.controller.php` | `producto.view.php` | `controllers/ProductDetailController.js` | — | `services/ProductDetailService.php` `services/ProductService.php` |
| 7 | Carrito | `carrito.controller.php` | — (AJAX) | `controllers/CartController.js` | `workers/Jobs/ProcessCartJob.php` | `services/CartService.php` `scripts/services/CartService.js` `scripts/domain/CartDomain.js` `scripts/domain/CartStore.js` |
| 8 | Checkout | `checkout.controller.php` | `checkout.view.php` `checkout_response.view.php` | `controllers/CheckoutController.js` | — | `services/CheckoutService.php` `services/EpaycoService.php` `services/InvoiceService.php` `api/epayco_webhook.php` |
| 9 | Pedidos | `pedido.controller.php` | `pedido.view.php` | `controllers/TrackingController.js` | — | `services/OrderService.php` `services/ShippingOrchestrator.php` |
| 10 | Favoritos | `favoritos.controller.php` | — (AJAX dinámico) | `controllers/FavoritesController.js` | — | `scripts/services/FavoritesService.js` `scripts/domain/FavoritesStore.js` |
| 11 | Perfil / Cuenta | `perfil.controller.php` | `perfil.view.php` | `controllers/ProfileController.js` `controllers/UserMenuController.js` | — | `services/UserService.php` |
| 12 | Reseñas | `resenas.controller.php` | — (embebido) | — | — | `scripts/services/ReviewService.js` |
| 13 | Stands | `stands.controller.php` | `stands.view.php` | — | — | `services/StandsListService.php` |
| 14 | Stand Detail | `stand_detail.controller.php` | `stand_detail.view.php` | — | — | `services/StandDetailService.php` |
| 15 | Reg. Vendedor | `registro_vendedor.controller.php` `vendor_registration.controller.php` | `registro_vendedor.view.php` | `controllers/VendorRegistrationController.js` | — | `services/VendorService.php` |
| 16 | Ciudades/Ubic. | `ciudades.controller.php` | — (JSON API) | `controllers/LocationController.js` | — | `scripts/services/LocationService.js` |
| 17 | Política Privacidad | `politica_privacidad.controller.php` | `politica_privacidad.view.php` | — | — | — |
| 18 | Términos y Cond. | `terminos_condiciones.controller.php` | `terminos_condiciones.view.php` | — | — | — |

**🧑‍🌾 SELLER HUB / MIS PRODUCTOS (6 módulos)**
| # | Módulo | Controladores | Vistas | Scripts JS | Workers | Services/Domain |
|---|--------|--------------|--------|------------|---------|-----------------|
| 19 | Dashboard Vendedor | `mis_productos.controller.php` | `mis_productos.view.php` | `controllers/ProductAdminController.js` | — | `services/MyProductsService.php` `views/mis_productos/header.view.php` `views/mis_productos/sidebar.view.php` `views/mis_productos/kpi_cards.view.php` |
| 20 | Configurar Producto | `controllers/mis_productos/configuration.controller.php` | `views/mis_productos/configuration.view.php` | — | — | — |
| 21 | Agregar Producto | `controllers/mis_productos/form_add_product.controller.php` | `views/mis_productos/form_add_product.view.php` | — | — | `functions/upload_product.php` `functions/update_product.php` `functions/delete_product.php` |
| 22 | Inventario | `controllers/mis_productos/inventory.controller.php` | `views/mis_productos/inventory.view.php` | — | — | — |
| 23 | Mi Stand (Seller) | `controllers/mis_productos/stand.controller.php` | `views/mis_productos/stand.view.php` | — | — | — |
| 24 | Estadísticas Vendedor | `controllers/mis_productos/statistics.controller.php` | `views/mis_productos/statistics.view.php` | `producer_stats.js` | — | `api/stats_producer.php` `functions/producer_graphics.php` |

**🛠️ ADMIN (1 módulo)**
| # | Módulo | Controladores | Vistas | Scripts JS | Workers | Services/Domain |
|---|--------|--------------|--------|------------|---------|-----------------|
| 25 | Admin | `admin.controller.php` | `admin_dashboard.view.php` | `controllers/AdminDashboardController.js` `controllers/AdminMenusController.js` `dashboard_stats.js` | — | `scripts/services/AdminDashboardService.js` `scripts/services/AdminCrudService.js` `scripts/services/AdminService.js` `api/stats_admin.php` `functions/admin_graphics.php` |

**⚙️ WORKERS / BACKGROUND (3 módulos)**
| # | Módulo | Controladores | Vistas | Scripts JS | Workers | Services/Domain |
|---|--------|--------------|--------|------------|---------|-----------------|
| 26 | Worker: Procesar Carrito | — | — | — | `workers/Jobs/ProcessCartJob.php` (cola `viva:cola:carrito`, DLQ `viva:cola:deadletter`) | `workers/Config/RedisConfig.php` `workers/Services/RedisCacheService.php` `workers/Services/ValidationService.php` |
| 27 | Worker: Registrar Usuario | — | — | — | `workers/Jobs/RegisterUserJob.php` (cola `viva:queue:users`) | `workers/Config/RedisConfig.php` `workers/Services/ValidationService.php` |
| 28 | Worker: Warmup Emails | — | — | — | `workers/WarmupEmails.php` (carga emails PostgreSQL → Redis Set) | `workers/Config/RedisConfig.php` |

**🧩 INFRASTRUCTURE / DOMAIN LAYER (1 módulo)**
| # | Módulo | Controladores | Vistas | Scripts JS | Workers | Services/Domain |
|---|--------|--------------|--------|------------|---------|-----------------|
| 29 | Services & Helpers | — | `views/partials/` (base_head, card_producto, card_stand, footer, footer_login, header, navbar, carrito) | `scripts/ui/Toast.js` `scripts/utils/EventRouter.js` `scripts/main.js` | `workers/Worker.php` | **18 PHP Services**: CartService, CheckoutService, EpaycoService, HomeService, InvoiceService, LoginService, MyProductsService, OrderService, ProductDetailService, ProductService, PuntoEnvioService, RecoveryService, RegisterService, ShippingOrchestrator, StandDetailService, StandsListService, UserService, VendorService. **13 Functions**: admin_graphics, auth_helper, database, delete_product, error_handler, mail_service, navbar_usuario, producer_graphics, queries, update_product, upload_product, upload, url_helper. **5 JS Domain**: AuthValidator, CartDomain, CartStore, FavoritesStore, PasswordValidator. **9 JS Services**: AdminCrudService, AdminDashboardService, AdminService, ApiService, CartService, CatalogService, FavoritesService, LocationService, ReviewService. **Styles**: `styles/input.css` `styles/output.css`. **Utils**: `utils/image_processing.php` `utils/image_uploader.php` |

## Project Conventions

| File | Path | Notes |
|------|------|-------|
| AGENTS.md | AGENTS.md | Reglas del proyecto, estructura de skills, flujo Agent Teams Lite |
| openspec/ | openspec/ | SDD documentation directory (specs, changes, archive) |
| .atl/project-memories.md | .atl/project-memories.md | Bugfixes, decisions, and architecture notes |

Read the convention files listed above for project-specific patterns and rules. All referenced paths have been extracted — no need to read index files to discover more.

---

## Usage

Para usar un skill:
1. Mencionar el trigger en la conversación
2. El orquestador cargará el skill apropiado y resolverá las compact rules

Para actualizar este registry:
- "actualizar skills" o "actualizar registry"

---

## Notas Importantes

- **Stack**: PHP 8.x + PostgreSQL + Vanilla JS + TailwindCSS + Redis/Predis
- **Redis**: Usa Predis (no phpredis) para compatibilidad cross-platform
- **Workers**: Sistema asíncrono con retry exponencial y Dead Letter Queue
- **Seguridad**: Fail-fast en credenciales, .env-only config, parametrización SQL
- **Arquitectura JS**: Clean Architecture (Controllers/Services/Domain) con EventRouter
- **Strict TDD Mode**: disabled (no test runner detected)

---

## Project Standards (auto-resolved)

Rules auto-injected into every sub-agent launch prompt:

### Viva Project Standards
- **PHP MVC**: Controllers handle routing/input, Services contain business logic, Views render HTML
- **PHP Helpers**: Functions in src/functions/ for reusable logic (auth, db, mail, url)
- **DB Access**: Always through `Database::getInstance()->ejecutar('queryName', [:params])`
- **JS Clean Architecture**: Controllers (UI) → Services (API) → Domain (pure logic)
- **Event Delegation**: EventRouter on document.body, data-action attributes for all interactivity
- **API Responses**: Always JSON with `{exito: bool, mensaje: string}` structure
- **Security**: Fail-fast on missing .env credentials, parameterized queries, sanitize all inputs
- **Error Handling**: try/catch → JSON error for APIs, Toast notification for frontend
- **No fallbacks**: Redis failure → reject request, never blind-insert to DB
- **No hardcoded credentials**: .env only, no ?? fallbacks with defaults
