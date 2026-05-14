# Delta for product-validation-workflow

## ADDED Requirements

### Requirement: Validation Status Column

`tab_productos` SHALL have a `validation_status VARCHAR(20)` column with CHECK constraint allowing only `pending_review`, `approved`, `rejected`. Default MUST be `'approved'` (migration-safe for existing rows). An index on `(validation_status)` MUST be created.

#### Scenario: New product inserted with pending status
- GIVEN `registrarProducto` is called for a new product
- WHEN the insert executes
- THEN `validation_status = 'pending_review'` and `is_active = false`

#### Scenario: Existing products after migration
- GIVEN migration adds `validation_status` with DEFAULT `'approved'`
- WHEN migration completes
- THEN existing products retain visibility (`is_active = true`, `validation_status = 'approved'`)

### Requirement: Modified Stock-Active Check

`chk_pp_stock_activo` MUST be altered to allow `is_active = false` when `validation_status = 'pending_review'`, regardless of `stock_productor`. New constraint: `(stock_productor > 0 AND is_active = TRUE) OR (stock_productor = 0 AND is_active = FALSE) OR (validation_status = 'pending_review' AND is_active = FALSE)`.

#### Scenario: Pending product with stock passes constraint
- GIVEN a product has `stock_productor = 10`, `validation_status = 'pending_review'`, `is_active = false`
- THEN the row MUST be accepted by the CHECK constraint

### Requirement: Worker Updates Validation Status

After `ProductValidationJob::handle()` completes, the worker MUST update `tab_productos.validation_status` and `is_active` based on the IA decision:

| IA Decision | `validation_status` | `is_active` |
|---|---|---|
| `approved` | `'approved'` | `true` |
| `revision_humana` | `'pending_review'` | `false` |
| `rejected` | `'rejected'` | `false` |
| `pending_validacion_ia` | `'pending_review'` | `false` |

#### Scenario: IA approves product
- GIVEN `ProductValidationService` returns `decision = 'approved'`
- WHEN the worker finishes processing
- THEN `validation_status = 'approved'` and `is_active = true`

#### Scenario: IA returns revision_humana
- GIVEN `ProductValidationService` returns `decision = 'revision_humana'`
- THEN `validation_status = 'pending_review'` and `is_active = false` (admin reviews)

#### Scenario: IA providers fail
- GIVEN both providers fail, result is `pending_validacion_ia`
- THEN `validation_status = 'pending_review'` and `is_active = false` (admin reviews)

### Requirement: Catalog Excludes Non-Approved

All catalog/search queries (`obtenerProductosCatalogo`, `obtenerProductosDestacados`, `obtenerProductoPorId` for public, `obtenerProductos` for public views) MUST add `AND validation_status = 'approved'` alongside existing `is_active = TRUE AND is_deleted = FALSE`. Admin/internal queries MUST NOT apply this filter.

#### Scenario: Pending product hidden from catalog
- GIVEN a product has `validation_status = 'pending_review'`
- WHEN a visitor browses the catalog
- THEN the product MUST NOT appear in results

#### Scenario: Admin queries include all statuses
- GIVEN an admin lists products via validation panel
- WHEN the API queries products by `validation_status`
- THEN results include products regardless of `validation_status` value