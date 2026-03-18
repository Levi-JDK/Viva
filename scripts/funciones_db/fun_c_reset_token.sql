-- Genera un nuevo OTP de 6 dígitos para recuperación de contraseña.
-- Invalida tokens anteriores del mismo usuario antes de insertar.
CREATE OR REPLACE FUNCTION fun_c_reset_token(
    p_mail_user VARCHAR,
    p_minutos   INTEGER DEFAULT 15
) RETURNS VARCHAR AS $$
DECLARE
    w_id_user    tab_users.id_user%TYPE;
    w_token      VARCHAR(6);
BEGIN
    -- Verificar que el correo exista
    SELECT id_user INTO w_id_user
    FROM   tab_users
    WHERE  mail_user = p_mail_user
    AND    is_deleted = FALSE
    LIMIT  1;

    IF w_id_user IS NULL THEN
        RETURN NULL; -- Correo no encontrado
    END IF;

    -- Invalidar tokens anteriores del mismo usuario
    UPDATE tab_reset_tokens
    SET    is_used = TRUE
    WHERE  id_user = w_id_user
    AND    is_used = FALSE;

    -- Generar token OTP de 6 dígitos (con cero a la izquierda si aplica)
    w_token := LPAD(FLOOR(RANDOM() * 1000000)::TEXT, 6, '0');

    -- Insertar nuevo token con expiración calculando el ID incremental manual
    INSERT INTO tab_reset_tokens (id_token, id_user, token_reset, expira_at)
    VALUES (
        COALESCE((SELECT MAX(id_token) + 1 FROM tab_reset_tokens), 1),
        w_id_user, 
        w_token, 
        CURRENT_TIMESTAMP + (p_minutos || ' minutes')::INTERVAL
    );

    RETURN w_token;
END;
$$ LANGUAGE plpgsql;
