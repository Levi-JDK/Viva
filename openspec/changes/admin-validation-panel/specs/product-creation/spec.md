# Delta for product-creation

## MODIFIED Requirements

### Requirement: Upload Response and Initial Status

`upload_product.php` MUST set `validation_status = 'pending_review'` and `is_active = false` on new products and respond with `{exito: true, mensaje: "Producto añadido satisfactoriamente, en espera de revisión"}`. The `fun_c_producto` SQL function MUST accept and set `validation_status`.

(Previously: products were created with `is_active = true` and response said "Producto publicado exitosamente")

#### Scenario: Producer uploads product
- GIVEN a producer submits a valid product via `upload_product.php`
- WHEN the insert completes
- THEN `validation_status = 'pending_review'`, `is_active = false`, and response says "en espera de revisión"

#### Scenario: IA auto-approves after worker runs
- GIVEN a product was just uploaded (status `pending_review`)
- WHEN the worker processes validation and IA returns `approved`
- THEN product becomes visible (`validation_status = 'approved'`, `is_active = true`)