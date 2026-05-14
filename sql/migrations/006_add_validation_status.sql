-- 006: Agregar validation_status a tab_productos
-- Este migration se ejecuta manualmente.

-- 1. Agregar columna con default 'approved' para no romper productos existentes
ALTER TABLE tab_productos
ADD COLUMN IF NOT EXISTS validation_status VARCHAR(20) NOT NULL DEFAULT 'approved';

-- 2. Agregar CHECK constraint
ALTER TABLE tab_productos
ADD CONSTRAINT chk_validation_status
CHECK (validation_status IN ('pending_review', 'approved', 'rejected'));

-- 3. Modificar CHECK constraint de stock+activo
-- Primero dropear el viejo
ALTER TABLE tab_productos
DROP CONSTRAINT IF EXISTS chk_pp_stock_activo;

-- Crear el nuevo: permite is_active=false con stock>0 si validation_status='pending_review'
ALTER TABLE tab_productos
ADD CONSTRAINT chk_pp_stock_activo CHECK (
    (validation_status = 'pending_review' AND is_active = FALSE) OR
    (stock_productor > 0 AND is_active = TRUE) OR
    (stock_productor = 0 AND is_active = FALSE)
);

-- 4. Index para filtrar por status
CREATE INDEX IF NOT EXISTS idx_productos_validation_status
ON tab_productos(validation_status);

-- 5. INDEX compuesto para listados admin
CREATE INDEX IF NOT EXISTS idx_productos_status_created
ON tab_productos(validation_status, created_at DESC);
