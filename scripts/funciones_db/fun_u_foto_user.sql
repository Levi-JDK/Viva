CREATE OR REPLACE FUNCTION fun_u_foto_user(
    p_id_user   integer,
    p_foto_user varchar
) RETURNS BOOLEAN AS $$
DECLARE
    w_id_user integer;
BEGIN
    -- Verificar si el usuario existe
    SELECT id_user INTO w_id_user FROM tab_users WHERE id_user = p_id_user;
    
    IF NOT FOUND THEN
        RETURN FALSE;
    END IF;

    -- Actualizar la foto
    UPDATE tab_users
    SET foto_user = p_foto_user,
        updated_at = CURRENT_TIMESTAMP
    WHERE id_user = w_id_user;
    
    RETURN TRUE;
END;
$$
LANGUAGE plpgsql;