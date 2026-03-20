# AGENTS.md - Viva Project

> Este archivo define las reglas y estructura de skills para el proyecto Viva.
> 
> **IMPORTANTE**: Este proyecto usa **Agent Teams Lite** - El orchestrator NUNCA toca código, solo delega a subagentes.

---

## Regla Fundamental del Orchestrator

```
╔══════════════════════════════════════════════════════════════════════════════╗
║  EL ORCHESTRATOR NUNCA TOCA CÓDIGO                                          ║
║  Solo delega tareas a subagentes y sintetiza sus resultados                 ║
╚══════════════════════════════════════════════════════════════════════════════╝
```

### ¿Qué DEBE hacer el orchestrator?
- ✅ Responder preguntas cortas si conoce la respuesta
- ✅ Coordinar subagentes
- ✅ Mostrar resúmenes al usuario
- ✅ Pedir decisiones al usuario
- ✅ Rastrear estado del proyecto

### ¿Qué NUNCA debe hacer el orchestrator?
- ❌ NO leer código fuente para entender el codebase → **DELEGAR**
- ❌ NO escribir o editar código → **DELEGAR**
- ❌ NO escribir specs, proposals, designs, tasks → **DELEGAR**
- ❌ NO ejecutar tests o builds → **DELEGAR**
- ❌ NO hacer análisis "rápidos" inline → **DELEGAR**

### Flujo de Trabajo
```
Usuario: "quiero agregar dark mode"

ORCHESTRATOR (solo coordina):
  → Lanza subagente EXPLORER    → retorna análisis del codebase
  → Muestra resumen al usuario  → usuario APRUEBA
  → Lanza subagente PROPOSER   → retorna proposal
  → Lanza subagente SPEC       → retorna especificaciones
  → Lanza subagente DESIGN     → retorna diseño técnico
  → Lanza subagente TASKS      → retorna checklist de tareas
  → Usuario APRUEBA todo
  → Lanza subagente IMPLEMENTER → retorna código escrito
  → Lanza subagente VERIFIER   → retorna verificación
  → Lanza subagente ARCHIVER   → retorna cambio cerrado
```

---

## 📋 Reglas del Proyecto

### Seguridad (Obligatorio)
- **NUNCA** dejar fallbacks (ej. en bloques catch) que puedan generar fallos de seguridad (como blind-inserts). Si un servicio asíncrono o caché falla, el fallback debe replicar TODAS las validaciones de negocio y seguridad originales, o denegar el servicio. SIEMPRE preguntar al usuario antes de implementar un fallback de este tipo.
- **NUNCA** usar operadores null coalescing (`??`) con credenciales hardcodeadas (ej. `$_ENV['DB_HOST'] ?? 'localhost'`). Confiar estrictamente en el `.env`. Si faltan credenciales, la aplicación DEBE fallar inmediatamente (Fail Fast) para no enmascarar errores de configuración en producción.
- **Nunca** commitear credenciales, claves API, o tokens en el código
- Usar variables de entorno (.env) para datos sensibles
- .env debe estar en .gitignore
- En producción, sanitizar todos los mensajes de error antes de mostrar al usuario
- No exponer datos personales en console.log o respuestas JSON

### Código
- PHP: Seguir patrón MVC (Controllers/Views/Functions)
- Frontend: Vanilla JS + TailwindCSS
- APIs: Siempre devolver JSON con estructura consistente
- Preferir funciones helper reutilizables sobre código duplicado

### SDD (Spec-Driven Development)
- Para cambios importantes, usar workflow SDD completo
- Proposal → Spec → Design → Tasks → Apply → Verify → Archive
- Para fixes rápidos, documentar en proposal y archivar al final

---

## 🎯 Skills del Proyecto

Los siguientes skills están disponibles y se activan automáticamente según el contexto:

### SDD Skills (Subagentes)

| Trigger | Skill | Descripción |
|---------|-------|-------------|
| "iniciar sdd" | sdd-init | Inicializa SDD en el proyecto |
| "explorar" | sdd-explore | Investiga código o funcionalidades |
| "proponer" | sdd-propose | Crea proposal de cambio |
| "especificar" | sdd-spec | Escribe especificaciones |
| "diseñar" | sdd-design | Crea diseño técnico |
| "tareas" | sdd-tasks | Genera checklist de tareas |
| "implementar" | sdd-apply | Implementa código |
| "verificar" | sdd-verify | Verifica implementación |
| "archivar" | sdd-archive | Archiva cambio completado |

### Code Skills (para subagentes)

| Trigger | Skill | Descripción |
|---------|-------|-------------|
| "crear api" | create-api | Genera una nueva API REST |
| "crear helper" | create-helper | Genera función helper |
| "revisar seguridad" | security-audit | Analiza el código en busca de vulnerabilidades |
| Vanilla JS, ES6 Modules, EventRouter | viva-js | Convenciones JS del proyecto (Clean Architecture, Controllers/Services/Domain, EventRouter, BASE_URL) |

### Redis Worker Skills

### Redis Worker Skills

| Trigger | Skill | Descripción |
|---------|-------|-------------|
| "Crear pipeline Redis-PostgreSQL" | redis-async-worker | Workers asíncronos con Redis (Predis) |
| "Crear worker asíncrono" | redis-async-worker | Sistema de colas con retry y DLQ |

---

## 📁 Estructura de Archivos

```
viva/
├── src/
│   ├── api/              # Endpoints REST
│   ├── controllers/      # Controladores MVC
│   ├── functions/        # Funciones helper
│   ├── views/           # Vistas PHP
│   ├── scripts/         # JavaScript
│   └── workers/         # Workers asíncronos (Redis + Predis)
├── .atl/
│   ├── skills/          # Skills del proyecto
│   └── skill-registry.md
├── openspec/            # Documentación SDD (si se usa modo openspec)
│   ├── config.yaml
│   ├── specs/
│   └── changes/
├── .env                 # Variables de entorno (NO commitear)
└── composer.json        # Dependencias PHP (incluye predis/predis)
```

---

## 🔧 Comandos SDD

```bash
# Inicializar proyecto con SDD
/sdd-init

# Nuevo cambio (explora + propone)
/sdd-new nombre-del-cambio

# Continuar con el siguiente paso
/sdd-continue

# Fast-forward: proposal → spec → design → tasks
/sdd-ff nombre-del-cambio

# Implementar tareas
/sdd-apply

# Verificar implementación
/sdd-verify

# Archivar cambio completado
/sdd-archive

# Actualizar registro de skills
/skill-registry
```

---

## 📞 Contacto

- Proyecto: VIVA Marketplace
- Stack: PHP + PostgreSQL + Vanilla JS + TailwindCSS
- Redis: Predis (compatible cross-platform)
- Última actualización: 2026-03-20

---

## Ejemplo de Conversión

### ❌ MAL (el orchestrator hace trabajo inline):
```
Usuario: "agrega autenticación"
Orchestrator: *lee todos los archivos de auth* *escribe el código* 
Resultado: Contexto bloated, posible pérdida de estado
```

### ✅ BIEN (el orchestrator delega):
```
Usuario: "agrega autenticación"
Orchestrator: Voy a delegar esto a subagentes especializados.
→ Lanza EXPLORER: analiza el codebase
→ Muestra resumen, usuario APRUEBA
→ Lanza IMPLEMENTER: escribe el código
→ Lanza VERIFIER: verifica que funciona
Resultado: Contexto limpio, subagentes con contexto fresco
```
