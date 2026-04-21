-- Función para eliminar un producto de favoritos
-- Realizamos un borrado físico simple para evitar acumular registros basura de favoritos
CREATE OR REPLACE FUNCTION fun_d_favoritos(
    p_id_user INTEGER,
    p_id_producto DECIMAL(12,0)
)
RETURNS BOOLEAN AS $$
BEGIN
    -- Validar que el usuario exista
    IF NOT EXISTS (SELECT 1 FROM tab_users WHERE id_user = p_id_user) THEN
        RETURN FALSE;
    END IF;

    -- Validar que el ítem realmente esté en favoritos
    IF NOT EXISTS (SELECT 1 FROM tab_favoritos WHERE id_user = p_id_user AND id_producto = p_id_producto) THEN
        RETURN FALSE;
    END IF;

    DELETE FROM tab_favoritos 
    WHERE id_user = p_id_user AND id_producto = p_id_producto;
    
    RETURN TRUE;
EXCEPTION WHEN OTHERS THEN
    RETURN FALSE;
END;
$$ LANGUAGE plpgsql;
