# Proposal: admin-validation-panel

## Intent

Hoy los productos se publican inmediatamente (`is_active = true`) al crearse. El pipeline de validación IA corre en background pero no afecta visibilidad ni permite revisión humana. Esto expone el marketplace a contenido no verificado. Se propone un workflow de validación que bloquee la publicación hasta aprobación y entregue a los administradores un panel para revisar, aprobar, rechazar o reprocesar productos basándose en el evidence de la IA.

## Scope

### In Scope
- Agregar columna `validation_status` a `tab_productos` (`pending_review`, `approved`, `rejected`).
- Modificar CHECK constraint `chk_pp_stock_activo` (Opción A): permitir `is_active = false` cuando `validation_status = 'pending_review'`.
- Worker `ValidationWorker.php` actualiza `validation_status` tras procesar (`approved`, `revision_humana` → `pending_review`, `rejected`).
- Panel admin: listado con filtros por estado, vista de evidence (hashes, imágenes similares, coherencia texto-imagen, RAG rules), botones [Aprobar] [Rechazar] [Re-procesar].
- API endpoints para listar productos por `validation_status` y ejecutar acciones de admin.
- Frontend de creación de producto: mensaje "Producto añadido satisfactoriamente, en espera de revisión".
- Excluir productos `pending_review` del catálogo público.

### Out of Scope
- Modificar el pipeline de embeddings, hashing o modelo de decisión (ya existe).
- Notificaciones email/SMS al vendedor sobre resultado de validación.
- Edición inline de productos desde el panel admin.
- Estadísticas o dashboards de métricas de validación.

## Capabilities

### New Capabilities
- `product-validation-workflow`: estados de validación en `tab_productos` e integración con worker IA.
- `admin-validation-panel`: UI y API para que administradores revisen evidence y tomen decisiones.

### Modified Capabilities
- `product-creation`: cambiar publicación inmediata por creación en estado `pending_review`.

## Approach

1. **Base de datos**: migración para agregar `validation_status VARCHAR(20) DEFAULT 'approved'` (para productos existentes), luego aplicar lógica de negocio para nuevos. Ajustar `chk_pp_stock_activo`.
2. **Worker**: al finalizar validación, escribir `validation_status` y campos de resultado (`plagiarism_status`, `text_image_status`, `artisan_status`, `estado_producto`) en `tab_productos`.
3. **Backend**: API `src/api/validation_products.php` (GET con filtros) y `src/api/validation_action.php` (POST acciones admin). Servicios PHP para queries paginadas.
4. **Frontend admin**: nueva vista embebida en `admin_dashboard.view.php` con controlador JS y service JS (Clean Architecture). Consumir API y renderizar evidence con Tailwind.
5. **Catálogo**: ajustar queries de listado para filtrar `validation_status = 'approved'`.

## Affected Areas

| Area | Impact | Description |
|------|--------|-------------|
| `tab_productos` | Modified | Nueva columna + CHECK constraint ajustado |
| `src/workers/ValidationWorker.php` | Modified | Actualiza `validation_status` tras IA |
| `src/api/` | New | `validation_products.php`, `validation_action.php` |
| `src/controllers/admin.controller.php` | Modified | Rutas al nuevo panel |
| `src/views/admin_dashboard.view.php` | Modified | Incluir sección de validación |
| `src/scripts/controllers/` | New | `AdminValidationController.js` |
| `src/scripts/services/` | New | `AdminValidationService.js` |
| Catálogo/Search queries | Modified | Excluir `pending_review` |

## Risks

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| Productos existentes quedan invisibles si migración falla | Low | Default `'approved'` en migración; backup previo |
| Panel admin lento con muchos productos | Med | Paginación server-side desde v1; índice en `validation_status` |
| Bloqueo de productos legítimos | Med | Admin puede hacer override (aprobar manualmente) |
| Worker IA no actualiza campo nuevo | Low | Unit test del worker contra DB de staging |

## Rollback Plan

1. Revertir migración: eliminar columna `validation_status` (o dejarla nullable sin usar).
2. Restaurar CHECK constraint original.
3. Eliminar archivos de API, controllers y services nuevos.
4. Revertir queries de catálogo a filtro original.
5. Re-activar publicación inmediata en `upload_product.php`.

## Dependencies

- Ninguna externa. Requiere sistema de validación IA operativo (ya desplegado).

## Success Criteria

- [ ] Producto nuevo queda en `pending_review` y no aparece en tienda pública.
- [ ] Worker actualiza `validation_status` y campos de resultado tras procesar.
- [ ] Admin puede listar, filtrar por estado y ver evidence completo de cada producto.
- [ ] Admin puede aprobar, rechazar o solicitar re-procesamiento desde el panel.
- [ ] API responde con estructura consistente `{exito: bool, mensaje: string, data?: any}`.
