CREATE OR REPLACE FUNCTION fun_val_log(p_mail VARCHAR) RETURNS VARCHAR AS $$
DECLARE
    w_pass VARCHAR;
    w_mail BOOLEAN;
    
BEGIN
    SELECT  fun_val_mail(p_mail) INTO w_mail;
	IF w_mail IS TRUE THEN
        RETURN 'Correo no existe'; -- Correo no válido
    ELSE
    -- Verificar si el correo electrónico ya está registrado
    	SELECT pass_user INTO w_pass
   		FROM tab_users
    	WHERE mail_user = p_mail;
   		IF FOUND THEN
	-- Retornamos el hash
            RETURN w_pass;
    	END IF;
	END IF;
END;
$$
LANGUAGE plpgsql;
--select fun_val_log('admin@mail.com');
-- SELECT * FROM tab_users;
