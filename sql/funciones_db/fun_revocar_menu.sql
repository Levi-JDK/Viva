CREATE OR REPLACE FUNCTION fun_revocar_menu(p_id_user tab_users.id_user%TYPE, p_id_menu tab_menu.id_menu%TYPE)
RETURNS BOOLEAN AS $$
BEGIN
    DELETE FROM tab_menu_user 
    WHERE id_user = p_id_user AND id_menu = p_id_menu;
    
    IF FOUND THEN
        RETURN TRUE;
    ELSE
        RETURN FALSE;
    END IF;
END;
$$ LANGUAGE plpgsql;
