-- Funcion que valida los datos de entrada en la tabla de usuarios
CREATE OR REPLACE FUNCTION fun_val_user(
    p_id_user   tab_users.id_user%TYPE,
    p_mail_user tab_users.mail_user%TYPE,
    p_pass_user tab_users.pass_user%TYPE,
    p_nom_user  tab_users.nom_user%TYPE,
    p_ape_user  tab_users.ape_user%TYPE  
) RETURNS BOOLEAN AS $$

DECLARE
    w_id_user 			tab_users.id_user%TYPE;
BEGIN
IF p_id_user IS NULL OR p_mail_user IS NULL OR p_pass_user IS NULL OR p_nom_user IS NULL OR p_ape_user IS NULL THEN
    RAISE NOTICE 'Todos los parámetros son obligatorios y no pueden ser nulos.';
    RETURN FALSE;
    ELSE
        -- Verificar si el usuario existe
        SELECT id_user INTO w_id_user FROM tab_users WHERE id_user = p_id_user;
        IF NOT FOUND THEN
            RETURN FALSE;
        ELSE   
            -- Validar longitud mínima
            IF length(p_pass_user) < 8 THEN
                RETURN FALSE;
            ELSE

            -- Validar que contenga al menos un número
     	        IF p_pass_user !~ '[0-9]' THEN
       		        RETURN FALSE;
    	        ELSE

                    -- Validar que contenga al menos una letra mayúscula
                    IF p_pass_user !~ '[A-Z]' THEN
                        RETURN FALSE;
                    ELSE

                    -- Validar que contenga al menos una letra minúscula
                        IF p_pass_user !~ '[a-z]' THEN
                            RETURN FALSE;
                        ELSE

                    -- Validar que contenga al menos un carácter seguro
                            IF p_pass_user !~ '[!@#$%^&*]' THEN
                                RETURN FALSE;
                            ELSE

                            -- Validar que solo tenga caracteres permitidos
                                IF p_pass_user !~ '^[A-Za-z0-9!@#$%^&*]+$' THEN
                                    RETURN FALSE;
                                ELSE
                                -- Validar formato del correo electrónico
                                    IF p_mail_user !~ '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$' THEN
                                        RETURN FALSE;
                                    ELSIF p_nom_user !~ '^[a-zA-Z]+$' OR p_ape_user !~ '^[a-zA-Z]+$' THEN
  			                            RETURN FALSE;
			                        ELSE
                                        RETURN TRUE; 
                                    END IF; 
                                END IF;
                            END IF;
                        END IF;
                    END IF;
                END IF;
            END IF;
        END IF;  
    END IF;
END;
$$
LANGUAGE plpgsql;