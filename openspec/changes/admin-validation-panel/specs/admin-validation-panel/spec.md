# Specification: admin-validation-panel

## Purpose

UI y API para que administradores revisen productos pendientes de validación, vean el evidence de la decisión IA, y ejecuten acciones (aprobar, rechazar, reprocesar).

## Requirements

### Requirement: List Products by Validation Status (API)

GET endpoint `validation_products.php` MUST return products filtered by `validation_status` with server-side pagination (default 20/page). Response format: `{exito, mensaje, data: {items, total, page}}`. Access restricted to admin users (id_menu = 8 via `AuthHelper::checkAccess`).

#### Scenario: Admin lists pending products
- GIVEN admin requests `GET /api/validation_products?status=pending_review&page=1`
- THEN response contains only `pending_review` products, paginated at 20 per page

#### Scenario: Non-admin access denied
- GIVEN a non-admin requests the endpoint
- THEN response MUST be HTTP 401 with `{exito: false, mensaje: "Acceso no autorizado"}`

### Requirement: Admin Action on Product (API)

POST endpoint `validation_action.php` MUST accept `{product_id, action}` where action is `approve`, `reject`, or `reprocess`. Approve: `validation_status = 'approved'`, `is_active = true`. Reject: `validation_status = 'rejected'`, `is_active = false`. Reprocess: reset `validation_status = 'pending_review'` and re-enqueue via `viva_enqueue_product_validation()`. Access restricted to admin (id_menu = 8).

#### Scenario: Admin approves product
- GIVEN admin POSTs `{product_id: 42, action: 'approve'}`
- THEN `validation_status = 'approved'`, `is_active = true`, response confirms success

#### Scenario: Admin rejects product
- GIVEN admin POSTs `{product_id: 42, action: 'reject'}`
- THEN `validation_status = 'rejected'`, `is_active = false`

#### Scenario: Admin reprocesses validation
- GIVEN admin POSTs `{product_id: 42, action: 'reprocess'}`
- THEN product is re-enqueued for IA, `validation_status = 'pending_review'`

#### Scenario: Invalid product_id
- GIVEN admin POSTs `{product_id: 0, action: 'approve'}`
- THEN response MUST be `{exito: false, mensaje: "product_id es requerido"}` with HTTP 400

### Requirement: Validation Evidence Display

The admin panel MUST fetch and display evidence from `ai.product_validation_results` for each product: IA decision and reason, plagiarism detection (method, score, matched product/producer/image), text-image coherence (status, score), artisan assessment (status, score), RAG rules used, and provider/fallback info.

#### Scenario: Admin views product evidence
- GIVEN admin selects a pending product in the panel
- THEN panel displays: decision, reason, plagiarism details, coherence score, artisan status, and RAG rules from the latest validation result

### Requirement: Admin Panel UI

The admin dashboard MUST include a "Validación" panel with filter tabs (Pendientes, Aprobados, Rechazados). Each product card shows thumbnail, name, producer, IA decision badge, and action buttons [Aprobar] [Rechazar] [Re-procesar]. Clicking a product expands the evidence detail. JS follows Clean Architecture: `AdminValidationController.js` (UI events via EventRouter `data-action`) → `AdminValidationService.js` (API calls). Uses existing `AdminDashboardService` patterns.

#### Scenario: Admin filters by status tab
- GIVEN admin clicks "Pendientes" tab
- THEN only `pending_review` products display with pagination