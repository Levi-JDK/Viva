CREATE OR REPLACE FUNCTION fun_u_vista_producto(
    p_id_producto DECIMAL(12,0)
)
RETURNS BOOLEAN AS $$
DECLARE
    v_existe BOOLEAN;
BEGIN
    -- Validar que el producto exista y no este eliminado logicamente
    SELECT EXISTS (
        SELECT 1 
        FROM tab_productos 
        WHERE id_producto = p_id_producto 
          AND is_deleted = FALSE
    ) INTO v_existe;

    IF NOT v_existe THEN
        RETURN FALSE;
    END IF;

    -- Incrementar el contador de vistas
    UPDATE tab_productos 
    SET vistas = vistas + 1 
    WHERE id_producto = p_id_producto;

    RETURN TRUE;

EXCEPTION
    WHEN OTHERS THEN
        -- En caso de error, retornamos falso silenciosamente
        RETURN FALSE;
END;
$$ LANGUAGE plpgsql;
