CREATE OR REPLACE FUNCTION fun_c_user(
    p_mail_user tab_users.mail_user%TYPE,
    p_pass_user tab_users.pass_user%TYPE,
    p_nom_user  tab_users.nom_user%TYPE,
    p_ape_user  tab_users.ape_user%TYPE
) RETURNS BOOLEAN AS $$
DECLARE
    w_mail_user tab_users.mail_user%TYPE;
BEGIN
    -- Verificar si el usuario ya existe
    IF (SELECT fun_val_mail(p_mail_user)) = FALSE THEN
        RETURN FALSE;
    ELSE  
        INSERT INTO tab_users (id_user, mail_user, pass_user, nom_user, ape_user)
        VALUES (COALESCE((SELECT MAX(id_user) FROM tab_users),0)+1, p_mail_user, p_pass_user, p_nom_user, p_ape_user);
        RETURN TRUE;
    END IF; 
END;
$$ LANGUAGE plpgsql;
