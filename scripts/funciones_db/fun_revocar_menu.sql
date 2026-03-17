CREATE OR REPLACE FUNCTION fun_revocar_menu(p_id_user INTEGER, p_id_menu INTEGER)
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
