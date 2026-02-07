CREATE OR REPLACE FUNCTION fun_u_user(
    p_id_user   tab_users.id_user%TYPE,
    p_mail_user tab_users.mail_user%TYPE,
    p_pass_user tab_users.pass_user%TYPE,
    p_nom_user  tab_users.nom_user%TYPE,
    p_ape_user  tab_users.ape_user%TYPE  
) RETURNS BOOLEAN AS $$
DECLARE
    w_id_user tab_users.id_user%TYPE;
BEGIN
    -- Validar los datos del usuario utilizando la función de validación
    IF (SELECT fun_val_user(p_id_user, p_mail_user, p_pass_user, p_nom_user, p_ape_user)) IS FALSE THEN
        RETURN FALSE;
    END IF;
	SELECT id_user INTO w_id_user FROM tab_users WHERE id_user = p_id_user;
    UPDATE tab_users
    SET mail_user = ''
    WHERE id_user = w_id_user;
    -- Verificar si el correo electrónico ya está en uso por otro usuario
    IF (SELECT fun_val_mail(p_mail_user)) IS FALSE THEN
        RETURN FALSE;
    ELSE
        -- Actualizar los datos del usuario
            UPDATE tab_users
            SET mail_user = p_mail_user,
                pass_user = p_pass_user,
                nom_user  = p_nom_user,
                ape_user  = p_ape_user
            WHERE id_user = p_id_user;
            RETURN TRUE;
    END IF;
END;
$$
LANGUAGE plpgsql;

--SELECT fun_val_user(1,'nuevo_prueba@gmail.com','Password123!','Juan','Perez');
--SELECT * FROM tab_users;
--SELECT fun_update_user(1,'nuevo_prueba@gmail.com','Password123!','Juano','PerezozoConLepra');