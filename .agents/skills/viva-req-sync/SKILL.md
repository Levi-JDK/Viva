---
name: viva-req-sync
description: >
  Sincronización exhaustiva de requerimientos desde el código hacia docs/requerimientos.md.
  Lee el código implementado, extrae requerimientos funcionales y no funcionales,
  los compara con lo ya documentado, y agrega los faltantes marcándolos como nuevos.
  Trigger: Cuando el usuario dice "sincronizar requerimientos", "actualizar reqs desde código",
  "documentar lo implementado", "req-sync", o necesita mapear módulos completos al doc de requerimientos.
license: Apache-2.0
metadata:
  author: gentleman-programming
  version: "1.0"
---

## Cuándo Usar

- Cuando el código tiene funcionalidades que no están en `docs/requerimientos.md`
- Cuando querés sincronizar módulo por módulo de forma exhaustiva
- Cuando necesitás que no se escape ni una coma de lo implementado
- Cuando querés preparar requerimientos nuevos para pasar al Excel

## Regla Crítica: Exhaustividad Total

**ESTA SKILL NO PUEDE DEJAR ESCAPAR NADA.**

- Cada archivo del módulo debe leerse completo
- Cada función, cada validación, cada ruta, cada elemento UI cuenta
- Si está en el código, debe estar en el doc
- Algoritmo: Código → Requerimiento → ¿Está en doc? → Si NO → Agregar

## Mapeo de Módulos

Basado en el escaneo real del filesystem (29 módulos total):

### 🏠 FRONTEND / PÚBLICO (18 módulos)

| # | Módulo | Controlador(es) PHP | Vista(s) | Scripts JS | Workers | Domain/Services |
|---|--------|---------------------|----------|------------|---------|----------------|
| 1 | Landing | `src/controllers/index.controller.php` | `src/views/index.view.php` | `src/scripts/controllers/LandingController.js` | — | `src/services/HomeService.php` |
| 2 | Login | `src/controllers/login.controller.php` + `logout.controller.php` | `src/views/login.view.php` | `src/scripts/controllers/AuthController.js` | — | `src/services/LoginService.php`, `src/functions/auth_helper.php` |
| 3 | Registro | `src/controllers/register.controller.php` | `src/views/registro.view.php` | `src/scripts/controllers/AuthController.js` | `src/workers/Jobs/RegisterUserJob.php` | `src/services/RegisterService.php` |
| 4 | Recuperar Pass | `src/controllers/recuperar.controller.php` | `src/views/recuperar.view.php` | `src/scripts/controllers/PasswordRecoveryController.js` | — | `src/services/RecoveryService.php`, `src/scripts/domain/PasswordRecoveryService.js`, `src/scripts/domain/PasswordService.js`, `src/scripts/domain/PasswordValidator.js` |
| 5 | Catálogo | `src/controllers/catalogo.controller.php` | `src/views/catalogo.view.php` | `src/scripts/controllers/CatalogController.js` | — | `src/scripts/services/CatalogService.js` |
| 6 | Producto Detalle | `src/controllers/producto.controller.php` | `src/views/producto.view.php` | `src/scripts/controllers/ProductDetailController.js` | — | `src/services/ProductDetailService.php`, `src/services/ProductService.php` |
| 7 | Carrito | `src/controllers/carrito.controller.php` | `src/views/partials/carrito.php` | `src/scripts/controllers/CartController.js` | `src/workers/Jobs/ProcessCartJob.php` (cola `viva:cola:carrito`) | `src/services/CartService.php`, `src/scripts/services/CartService.js`, `src/scripts/domain/CartDomain.js`, `src/scripts/domain/CartStore.js` |
| 8 | Checkout | `src/controllers/checkout.controller.php` | `src/views/checkout.view.php` + `checkout_response.view.php` | `src/scripts/controllers/CheckoutController.js` | — | `src/services/CheckoutService.php`, `src/services/EpaycoService.php`, `src/services/InvoiceService.php`, `src/api/epayco_webhook.php` |
| 9 | Pedidos | `src/controllers/pedido.controller.php` | `src/views/pedido.view.php` | `src/scripts/controllers/TrackingController.js` | — | `src/services/OrderService.php`, `src/services/ShippingOrchestrator.php` |
| 10 | Favoritos | `src/controllers/favoritos.controller.php` | — (AJAX dinámico) | `src/scripts/services/FavoritesService.js` | — | `src/scripts/domain/FavoritesStore.js` |
| 11 | Perfil / Cuenta | `src/controllers/perfil.controller.php` | `src/views/perfil.view.php` | `src/scripts/controllers/ProfileController.js` + `UserMenuController.js` | — | `src/services/UserService.php` |
| 12 | Reseñas | `src/controllers/resenas.controller.php` | — (embebido) | — | — | `src/scripts/services/ReviewService.js` |
| 13 | Stands | `src/controllers/stands.controller.php` | `src/views/stands.view.php` | — | — | `src/services/StandsListService.php` |
| 14 | Stand Detail | `src/controllers/stand_detail.controller.php` | `src/views/stand_detail.view.php` | — | — | `src/services/StandDetailService.php` |
| 15 | Reg. Vendedor | `src/controllers/registro_vendedor.controller.php` + `vendor_registration.controller.php` | `src/views/registro_vendedor.view.php` | `src/scripts/controllers/VendorRegistrationController.js` | — | `src/services/VendorService.php` |
| 16 | Ciudades/Ubic. | `src/controllers/ciudades.controller.php` | — (JSON API) | `src/scripts/controllers/LocationController.js` | — | `src/scripts/services/LocationService.js` |
| 17 | Política Privacidad | `src/controllers/politica_privacidad.controller.php` | `src/views/politica_privacidad.view.php` | — | — | — |
| 18 | Términos y Cond. | `src/controllers/terminos_condiciones.controller.php` | `src/views/terminos_condiciones.view.php` | — | — | — |

### 🧑‍🌾 SELLER HUB / MIS PRODUCTOS (6 módulos)

| # | Módulo | Controlador(es) PHP | Vista(s) | Scripts JS | Workers | Domain/Services |
|---|--------|---------------------|----------|------------|---------|----------------|
| 19 | Dashboard Vendedor | `src/controllers/mis_productos.controller.php` | `src/views/mis_productos.view.php` + `header.view.php` + `sidebar.view.php` + `kpi_cards.view.php` | `src/scripts/controllers/ProductAdminController.js` | — | `src/services/MyProductsService.php` |
| 20 | Configurar Producto | `src/controllers/mis_productos/configuration.controller.php` | `src/views/mis_productos/configuration.view.php` | — | — | — |
| 21 | Agregar Producto | `src/controllers/mis_productos/form_add_product.controller.php` | `src/views/mis_productos/form_add_product.view.php` | — | — | `src/functions/upload_product.php`, `update_product.php`, `delete_product.php` |
| 22 | Inventario | `src/controllers/mis_productos/inventory.controller.php` | `src/views/mis_productos/inventory.view.php` | — | — | — |
| 23 | Mi Stand (Seller) | `src/controllers/mis_productos/stand.controller.php` | `src/views/mis_productos/stand.view.php` | — | — | — |
| 24 | Estadísticas Vendedor | `src/controllers/mis_productos/statistics.controller.php` | `src/views/mis_productos/statistics.view.php` | `src/scripts/producer_stats.js` | — | `src/functions/stats_producer.php`, `src/functions/producer_graphics.php` |

### 🛠️ ADMIN (1 módulo)

| # | Módulo | Controlador(es) PHP | Vista(s) | Scripts JS | Workers | Domain/Services |
|---|--------|---------------------|----------|------------|---------|----------------|
| 25 | Admin | `src/controllers/admin.controller.php` | `src/views/admin_dashboard.view.php` | `src/scripts/controllers/AdminDashboardController.js`, `AdminMenusController.js`, `dashboard_stats.js` | — | `src/scripts/services/AdminDashboardService.js`, `src/scripts/services/AdminCrudService.js`, `src/scripts/services/AdminService.js`, `src/functions/stats_admin.php`, `src/functions/admin_graphics.php` |

### ⚙️ WORKERS / BACKGROUND (3 módulos)

| # | Módulo | Controlador(es) PHP | Vista(s) | Scripts JS | Workers | Domain/Services |
|---|--------|---------------------|----------|------------|---------|----------------|
| 26 | Worker: Procesar Carrito | — | — | — | `src/workers/Jobs/ProcessCartJob.php` (cola `viva:cola:carrito`, DLQ `viva:cola:deadletter`) | `src/workers/RedisConfig.php`, `src/workers/Services/RedisCacheService.php`, `src/workers/Services/ValidationService.php` |
| 27 | Worker: Registrar Usuario | — | — | — | `src/workers/Jobs/RegisterUserJob.php` (cola `viva:queue:users`) | `src/workers/RedisConfig.php`, `src/workers/Services/ValidationService.php` |
| 28 | Worker: Warmup Emails | — | — | — | `src/workers/WarmupEmails.php` (carga emails PostgreSQL → Redis Set) | `src/workers/RedisConfig.php` |

### 🧩 INFRASTRUCTURE / DOMAIN LAYER (1 módulo)

| # | Módulo | Controlador(es) PHP | Vista(s) | Scripts JS | Workers | Domain/Services |
|---|--------|---------------------|----------|------------|---------|----------------|
| 29 | Services & Helpers | — | `src/views/partials/` (base_head, card_producto, card_stand, footer, header, footer_login, navbar) | `src/scripts/ui/Toast.js`, `src/scripts/utils/EventRouter.js`, `src/scripts/main.js` | — | **18 PHP Services**: CartService, CheckoutService, EpaycoService, HomeService, InvoiceService, LoginService, MyProductsService, OrderService, ProductDetailService, ProductService, PuntoEnvioService, RecoveryService, RegisterService, ShippingOrchestrator, StandDetailService, StandsListService, UserService, VendorService. **13 Functions**: admin_graphics, auth_helper, database, delete_product, error_handler, mail_service, navbar_usuario, producer_graphics, queries, update_product, upload_product, upload, url_helper |

## Flujo de Trabajo

### 1. Leer Documento Existente
```bash
read docs/requerimientos.md
```
Guardar en memoria la estructura completa: módulos, requerimientos existentes, numeración.

### 2. Procesar Módulo por Módulo

Para **CADA módulo** del mapeo:

#### 2.1. Exploración Exhaustiva del Código
Delegar a subagente `sdd-explore` con:
```
TAREA: Leer TODO el código del módulo [NOMBRE]
- Listar archivos: glob src/**/* relacionado con el módulo
- Leer CADA archivo completo (views, controllers, functions, scripts JS)
- Extraer:
  - REQUERIMIENTOS FUNCIONALES: UI elements, textos, colores, interacciones, navegación
  - REQUERIMIENTOS NO FUNCIONALES: Validaciones, queries, funciones, rutas, lógica de negocio
- Formato para cada req: "# Descripción técnica exacta"
```

#### 2.2. Comparación con Documentación Existente
Subagente debe comparar:
- ¿El requerimiento extraído YA está en `docs/requerimientos.md`?
- Buscar por coincidencia semántica (no solo texto literal)
- Si el texto difiere pero la funcionalidad es la misma → está cubierto

#### 2.3. Generar Requerimientos Faltantes
Para los NO documentados:
```markdown
| # | Descripción |   | Nivel de Cumplimiento | [NUEVO] |
```
- Dejar `SI/NO/ADI` **vacío** (no tachado, solo en blanco)
- Agregar `[NUEVO]` en Observaciones
- Numeración: continuar secuencia existente o insertar con decimales (ej: 42.1, 42.2)

#### 2.4. Actualizar Documento
```bash
edit docs/requerimientos.md
```
Insertar nuevos requerimientos en la sección correspondiente del módulo.

#### 2.5. REPORTAR ANTES DE CONTINUAR
Mostrar al orchestrator:
```
✅ Módulo [NOMBRE] completado:
   - Requerimientos existentes: X
   - Nuevos agregados: Y
   - Total ahora: Z
   
   Nuevos reqs:
   1. [Descripción del nuevo req 1]
   2. [Descripción del nuevo req 2]
   
   ¿Continuar con siguiente módulo?
```

**ESPERAR confirmación del orchestrator antes de pasar al siguiente módulo.**

### 3. Al Finalizar Todos los Módulos

Guardar en engram:
- title: "Sync requerimientos VIVA completado"
- type: "discovery"
- topic_key: "requirements-sync"
- content: Total de nuevos reqs por módulo, archivo actualizado

## Formato de Requerimientos

### Funcionales (lo que se ve):
```
| # | El sistema debe mostrar el botón "Agregar al carrito" en color naranja #FF6B35 |   | SI | [NUEVO] |
```

### No Funcionales (lo que implemento):
```
| # | El sistema debe validar que el email no esté registrado mediante función fun_val_email |   | SI | [NUEVO] |
```

## Reglas de Extracción (CRÍTICO - Leer bien)

### UI (FUNCIONALES) - SOLO LO QUE SE VE:
**NUNCA incluir:**
- ❌ Código CSS (translateY, fade-in, clases de Tailwind)
- ❌ Nombres de iconos (fa-users, fa-hand-holding-heart)
- ❌ JavaScript (IntersectionObserver, EventRouter, addEventListener)
- ❌ Animaciones técnicas (srcset, lazy loading, threshold)

**SÍ incluir:**
- ✅ Textos literales (copiar exacto del código)
- ✅ Colores (decir "naranja", no código hex)
- ✅ Elementos (botones, inputs, modales, cards)
- ✅ Interacciones VISIBLES (click, hover, despliegue)
- ✅ Navegación (rutas visibles, enlaces)
- ✅ Posiciones relativas ("arriba", "a la izquierda", no flex/grid)

**Ejemplo CORRECTO:** "El sistema debe mostrar el menú desplegable al hacer clic en la foto de perfil"
**Ejemplo INCORRECTO:** "El sistema debe usar toggleClass('show') con jQuery para desplegar el menú"

### Lógica (NO FUNCIONALES) - CONCEPTUAL, SIN CÓDIGO:
**NUNCA incluir:**
- ❌ Código PHP/JS (nombres de funciones, clases, métodos)
- ❌ Queries SQL (tablas, campos, JOINs)
- ❌ Configuraciones técnicas (Predis, Redis, host/port)
- ❌ Rutas específicas (endpoint.php, método HTTP)

**SÍ incluir:**
- ✅ QUÉ hace el sistema (validar, consultar, cachear, redirigir)
- ✅ CON QUÉ tecnología/concepto (usando cache aside, mediante sesiones, con system de roles)
- ✅ PARA QUÉ sirve (para respuestas rápidas, para seguridad, para persistencia)

**Ejemplo CORRECTO:** "El sistema debe navegar entre menús utilizando un sistema de cache aside para tener respuestas rápidas de los menús a los que el usuario tenga acceso"
**Ejemplo INCORRECTO:** "El sistema usa `Predis\Client` conectando a Redis en 127.0.0.1:6379 para cachear menús"

## Comandos

```bash
# Leer requerimientos actuales
read docs/requerimientos.md

# Buscar archivos de un módulo
glob src/**/*pattern*

# Ver estructura de módulos
ls -la src/controllers/ src/views/
```

## Recursos

- **Documento a actualizar**: `docs/requerimientos.md`
- **Excel origen**: `docs/Requerimientos.xlsx` (solo referencia, no modificar)
- **Estructura del proyecto**: Ver `AGENTS.md` sección Estructura de Archivos
