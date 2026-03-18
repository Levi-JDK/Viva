-- Actualiza la contraseña del usuario y marca el token OTP como usado.
CREATE OR REPLACE FUNCTION fun_u_password(
    p_id_user    INTEGER,
    p_pass_user  VARCHAR  -- Hash ya calculado en PHP
) RETURNS BOOLEAN AS $$
BEGIN
    -- Actualizar contraseña
    UPDATE tab_users
    SET    pass_user  = p_pass_user,
           updated_at = CURRENT_TIMESTAMP,
           updated_by = 'sistema'
    WHERE  id_user   = p_id_user
    AND    is_deleted = FALSE;

    IF NOT FOUND THEN
        RETURN FALSE;
    END IF;

    -- Marcar todos los tokens pendientes de este usuario como usados
    UPDATE tab_reset_tokens
    SET    is_used = TRUE
    WHERE  id_user = p_id_user
    AND    is_used = FALSE;

    RETURN TRUE;
END;
$$ LANGUAGE plpgsql;
