-- Función para agregar un producto a favoritos
-- Si el producto ya estaba antes (incluso si tuvo borrado lógico), se reactiva
CREATE OR REPLACE FUNCTION fun_c_favorito(
    p_id_user INTEGER,
    p_id_producto DECIMAL(12,0)
)
RETURNS BOOLEAN AS $$
BEGIN
    -- Validar que el usuario exista
    IF NOT EXISTS (SELECT 1 FROM tab_users WHERE id_user = p_id_user) THEN
        RETURN FALSE;
    END IF;

    -- Validar que el producto exista, esté activo y no esté borrado
    IF NOT EXISTS (SELECT 1 FROM tab_productos WHERE id_producto = p_id_producto AND is_active = TRUE AND is_deleted = FALSE) THEN
        RETURN FALSE;
    END IF;

    INSERT INTO tab_favoritos (id_user, id_producto)
    VALUES (p_id_user, p_id_producto)
    ON CONFLICT (id_user, id_producto) 
    DO UPDATE SET 
        is_deleted = FALSE, 
        updated_at = CURRENT_TIMESTAMP,
        updated_by = current_user;
        
    RETURN TRUE;
EXCEPTION WHEN OTHERS THEN
    RETURN FALSE;
END;
$$ LANGUAGE plpgsql;
