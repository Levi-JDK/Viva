-- Script para agregar columnas de auditoría a tab_reset_tokens
-- Ejecutar en pgAdmin, DBeaver o cualquier gestor de PostgreSQL

ALTER TABLE tab_reset_tokens 
ADD COLUMN IF NOT EXISTS created_by VARCHAR(100) NOT NULL DEFAULT current_user,
ADD COLUMN IF NOT EXISTS updated_by VARCHAR(100) NOT NULL DEFAULT 'N/A',
ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT '1900-01-01 00:00:00',
ADD COLUMN IF NOT EXISTS is_deleted BOOLEAN NOT NULL DEFAULT FALSE;

-- Verificar que las columnas se crearon correctamente
SELECT column_name, data_type, column_default 
FROM information_schema.columns 
WHERE table_name = 'tab_reset_tokens' 
ORDER BY ordinal_position;
