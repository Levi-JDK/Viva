---
name: viva-req-audit
description: >
  Auditoría y validación de requerimientos VIVA para evitar errores de redacción, 
  términos técnicos en funcionales, y código en no funcionales.
  Revisa duplicados, conflictos, y adherencia a las reglas de redacción.
  Trigger: Cuando el usuario dice "auditar requerimientos", "validar reqs", "revisar reqs", 
  "verificar documentación", o después de sincronizar un módulo para validar calidad.
license: Apache-2.0
metadata:
  author: gentleman-programming
  version: "1.0"
---

## Cuándo Usar

- Después de sincronizar un módulo con `viva-req-sync`
- Cuando querés validar que los requerimientos cumplen con las reglas de redacción
- Cuando necesitás detectar duplicados, conflictos, o términos prohibidos
- Antes de pasar al Excel para asegurar calidad

## Reglas de Oro (El Guardián)

### 1. FUNCIONALES - Escribilo como lo diría un USUARIO/PO

La regla de oro es: **¿esto lo diría un usuario describiendo lo que ve o quiere?**

**✅ Bien (lenguaje de usuario):**
- "El panel lateral que se desliza desde la derecha"
- "Las tarjetas se reordenan solas al cambiar el tamaño de la ventana"
- "El botón de las tres líneas para abrir el menú"
- "Aparecen de a uno a medida que se baja por la página"
- "La página principal del panel de control"
- "Se ve distinto en el teléfono que en la computadora"
- "Animaciones", "transiciones", "desplazarse"
- "Color naranja", "fondo con degradado"
- Textos literales que aparecen en pantalla

**❌ Mal (lenguaje de programador, aunque el usuario lo vea):**
- "header del drawer" → "parte superior del panel lateral"
- "dashboard" → "panel de control" / "página principal"
- "botón hamburguesa" → "botón de tres líneas"
- "versión compacta" → "pantalla angosta" / "cuando el espacio es reducido"
- "cuadrícula adaptable" → "tarjetas que se reordenan solas"
- "meta descripción" → "descripción para buscadores"
- "scroll" → "desplazarse" o "bajar la página"

**❌ Mal en todos lados (código literal):**
- `fade-in`, `translateY`, `scroll-behavior`, `md:`, `lg:`, `hidden`
- `IntersectionObserver`, `addEventListener`, `scrollIntoView`
- `EventRouter`, `LandingController`, `fun_val_login`
- `usando cache aside`, `mediante Redis`, `JWT` (ESTO VA EN RNF)

**💡 Preguntate:** "¿Le mostraría esto a un cliente o un PO y diría 'tiene sentido'?"  
Si la respuesta es SÍ → RF ✅. Si tenés que explicarle qué significa → reescribilo.

---

### 2. NO FUNCIONALES - Conceptual técnico SÍ, CÓDIGO NO

**PERMITIDO en No Funcionales (Qué hace el sistema, conceptualmente):**
- ✅ "El sistema debe validar el email del usuario"
- ✅ "El sistema debe implementar un desplazamiento suave (scroll smooth)"
- ✅ "El sistema debe usar un sistema de cache aside para menús"
- ✅ "El sistema debe navegar entre menús mediante sesiones de usuario"
- ✅ "El sistema debe redirigir con parámetros de retorno"

**PERMITIDO en No Funcionales (Conceptos técnicos — while no sea código literal):**
- ✅ "El sistema debe implementar scroll smooth mediante IntersectionObserver"
- ✅ "El sistema debe usar JWT para autenticación"
- ✅ "El sistema debe usar una función almacenada en PostgreSQL para registrar al productor"
- ✅ "El sistema debe usar Redis como caché de sesiones"
- ✅ "El sistema debe generar un token de un solo uso con expiración"
- ✅ "El sistema debe usar PHPMailer para el envío de correos"
- ✅ "El sistema debe limpiar la cookie de autenticación al cerrar sesión"

**PROHIBIDO en No Funcionales (CÓDIGO LITERAL — pegar código fuente):**
- ❌ PHP: `LoginService::validateUser()`, `password_verify()`, `PDO::PARAM_STR`
- ❌ JS: `EventRouter.register()`, `scrollIntoView({behavior: 'smooth'})`, `RedisClient`
- ❌ SQL: `SELECT * FROM tab_users`, `JOIN tab_profiles`, `ORDER BY RANDOM()`
- ❌ Config: `Predis\Client`, `127.0.0.1:6379`, `argon2id`
- ❌ Nombres de archivo/ruta: `src/functions/auth_helper.php`, `scripts/domain/Validator.js`

**💡 Regla de oro para RNF:** 
Si lo escribirías igual en una reunión de arquitectura → ✅
Si lo COPY-PASTEASTE del código → ❌

**Ejemplo CORRECTO:** "El sistema debe validar la fortaleza de la contraseña con longitud mínima y caracteres diversos"
**Ejemplo INCORRECTO:** "El sistema debe usar `preg_match('/^(?=.*[A-Z])/', $password)` para validar contraseña"

---

## Tipos de Errores que Detecta

| Categoría | Error | Acción |
|-----------|-------|--------|
| **A. Duplicidad Directa** | Reqs que piden exactamente lo mismo con palabras diferentes | Eliminar uno, unificar |
| **B. Conflictos de Comportamiento** | Reqs que contradicen otros (ej: header sticky vs auto-hide) | Preguntar al PO cuál mantener |
| **C. Términos técnicos en Funcionales** | Conceptos de back/arquitectura en funcionales (ej: "mediante Redis", "usando JWT") | Mover a No Funcionales |
| **D. Código literal en No Funcionales** | PHP, JS, SQL, configuraciones copiadas del código | Reescribir conceptualmente (estilo reunión de arquitectura) |
| **E. Redundancias** | Reqs genéricos que ya están cubiertos por específicos | Eliminar genérico |
| **F. Numeración** | Saltos en numeración, duplicados | Corregir secuencia |

## Flujo de Trabajo

### 1. Leer Módulo a Auditar

```
Leer docs/requerimientos.md sección [MÓDULO]
- Requerimientos Funcionales (líneas X-Y)
- Requerimientos No Funcionales (líneas Y-Z)
```

### 2. Escanear Términos Prohibidos

**En Funcionales, buscar (mover a RNF si se encuentra):**
```
translateY, fade-in, scroll-behavior, IntersectionObserver, EventRouter, 
addEventListener, fa-, md:, lg:, hidden, threshold, stagger, 
fun_, Service::, srcset, loading="lazy", PHP, SQL, Redis, Predis,
"usando el sistema de", "mediante", "Módulo", token, jwt, cache
```

**En No Funcionales, buscar CÓDIGO:**
```
preg_match, PDO::, SELECT, INSERT, UPDATE, JOIN, password_verify, 
RedisClient, Predis\, .php, ->, ::, =>, {behavior:, localhost:6379
```

### 3. Detectar Duplicados y Conflictos

Comparar cada req con todos los demás:
- ¿Mismo texto o semántica idéntica? → Duplicado
- ¿Comportamientos opuestos? → Conflicto
- ¿Genérico vs Específico? → Redundancia

### 4. Generar Reporte

```markdown
## Auditoría Módulo: [NOMBRE]

### Resumen:
- Total reqs: X (Y funcionales + Z no funcionales)
- Errores detectados: W
- Duplicados: A
- Conflictos: B
- Términos prohibidos: C
- Código en no funcionales: D

### Detalle de Errores:

**A. Duplicidad Directa:**
| RFxx | RFyy | Descripción | Acción |
|-------|-------|-------------|--------|

**B. Conflictos:**
| RFxx | RFyy | Descripción | Decisión requerida |
|-------|-------|-------------|-------------------|

**C. Términos Prohibidos en Funcionales:**
| RF# | Término encontrado | Dónde movir |
|-----|-------------------|-------------|

**D. Código en No Funcionales:**
| RF# | Código encontrado | Cómo reescribir |
|-----|-------------------|----------------|

### Dictamen Final:
✅ **Limpio** (0 errores) / ⚠️ **Requiere limpieza** (X errores)
```

### 5. Proponer Correcciones (Opcional)

Si el usuario aprueba, generar versiones corregidas:
- Mover términos/conceptos técnicos de funcionales a no funcionales (conservando redacción)
- Reescribir código literal en no funcionales como conceptos de arquitectura
- Unificar duplicados (eliminar del origen si el destino tiene el req con `[MOVIDO DE]`)
- Resolver conflictos (con decisión del PO)
- Asegurar cobertura: front (RF = UI visible) y back (RNF = lógica, seguridad, performance)

### 6. Guardar en Engram

```
title: "Auditoría [MÓDULO] - X errores detectados"
type: "discovery"
topic_key: "requirements-audit-[modulo-lowercase]"
```

## Comandos

```bash
# Buscar términos prohibidos en un archivo
grep -E "translateY|fade-in|IntersectionObserver" docs/requerimientos.md

# Ver líneas específicas
read docs/requerimientos.md offset=X limit=50
```

## Recursos

- **Skill complementaria**: `viva-req-sync` (para sincronización)
- **Documento a auditar**: `docs/requerimientos.md`
- **Skill de redacción**: Ver reglas en `viva-req-sync` para formato correcto
