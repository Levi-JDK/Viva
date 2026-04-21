-- Valida un token OTP: devuelve id_user si es válido, NULL si no.
CREATE OR REPLACE FUNCTION fun_v_reset_token(
    p_mail_user  VARCHAR,
    p_token      VARCHAR
) RETURNS INTEGER AS $$
DECLARE
    w_id_user tab_users.id_user%TYPE;
BEGIN
    SELECT rt.id_user INTO w_id_user
    FROM   tab_reset_tokens rt
    INNER JOIN tab_users u ON rt.id_user = u.id_user
    WHERE  u.mail_user    = p_mail_user
    AND    rt.token_reset = p_token
    AND    rt.is_used     = FALSE
    AND    rt.expira_at   > CURRENT_TIMESTAMP
    ORDER  BY rt.created_at DESC
    LIMIT  1;

    RETURN w_id_user; -- NULL si no se encontró un token válido
END;
$$ LANGUAGE plpgsql;
