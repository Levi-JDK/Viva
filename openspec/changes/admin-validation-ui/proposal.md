# Proposal: admin-validation-ui

## Intent

Fix de UI/UX del panel **Validaciones IA** en el admin dashboard. El panel actual tiene problemas de integración visual (superposición, falta de scroll, modal translúcido, responsive roto) y no sigue los patrones establecidos por las secciones de usuarios y productos.

## Scope

### In Scope
- Refactor de `admin_validation.view.php` para alinear estructura, clases y layout con paneles existentes (usuarios, productos).
- Fix del modal de evidence: opacidad completa, scroll interno suave, z-index consistente con otros modales del admin.
- Rediseño responsive de la tabla: eliminar scroll horizontal forzado en desktop, mejorar cards en mobile, botones de acción siempre visibles.
- Ajustes de consistencia visual: padding, breakpoints (`md` vs `xl`), tipografía y spacing.

### Out of Scope
- Cambios en backend/APIs.
- Cambios en lógica de negocio o estados de validación.
- Modificaciones a `AdminValidationService.js`.
- Nuevas funcionalidades (solo UI fix).

## Capabilities

### New Capabilities
None

### Modified Capabilities
None

## Approach

1. **Auditar estructura HTML**: comparar `panel-validaciones` contra `panel-usuarios` y `panel-productos`; replicar clases de contenedor, alturas, overflow y z-index.
2. **Mover modal de evidence**: extraerlo del interior del `<section>` y colocarlo al final del `<body>` (igual que el modal CRUD y confirmación global) para evitar clipping y superposición con el dashboard.
3. **Tabla responsive**: reducir número de columnas visibles en breakpoints intermedios (`lg`/`md`) usando `hidden lg:table-cell` donde corresponda; en mobile mantener cards con layout vertical claro.
4. **Modal opaco**: cambiar fondo del modal de `bg-black/80` a `bg-black/95` y alinear `z-[100]` con el resto de modales del admin.
5. **Scroll**: asegurar que el contenedor principal del panel permita scroll vertical dentro del flujo del `<main>`; eliminar cualquier regla que bloquee overflow.
6. **Controller JS**: actualizar `createRow` y `createCard` para usar clases CSS consistentes con otros paneles (ej. botones siempre visibles, sin `md:opacity-0`).

## Affected Areas

| Area | Impact | Description |
|------|--------|-------------|
| `src/views/admin_validation.view.php` | Modified | Estructura del panel y modal |
| `src/scripts/controllers/AdminValidationController.js` | Modified | Clases de render en rows/cards |
| `src/views/admin_dashboard.view.php` | Modified | Posición del modal de evidence |

## Risks

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| Romper integración con EventRouter | Low | Preservar `data-action`, `id`s y selectors del controller |
| Modal clipping al moverlo en DOM | Med | Verificar `position: fixed` y z-index en contexto del dashboard |
| Regresión visual mobile | Low | Testear en viewport 375px y 768px |

## Rollback Plan

Revertir los 3 archivos modificados al estado anterior. El cambio es 100% frontend; no afecta datos ni APIs.

## Dependencies

Ninguna.

## Success Criteria

- [ ] Panel se integra visualmente igual que usuarios/productos (sin superposición).
- [ ] Scroll vertical funciona correctamente en el panel con cualquier cantidad de filas.
- [ ] Modal de evidence es opaco (`bg-black/95`) y tiene scroll interno suave.
- [ ] Tabla desktop no fuerza scroll horizontal; cards mobile son legibles y funcionales.
- [ ] Botones de acción siempre visibles en todas las resoluciones.
