CREATE OR REPLACE FUNCTION fun_asignar_menu(p_id_user tab_users.id_user%TYPE, p_id_menu tab_menu.id_menu%TYPE)
RETURNS BOOLEAN AS $$
BEGIN
    INSERT INTO tab_menu_user (id_user, id_menu, is_deleted)
    VALUES (p_id_user, p_id_menu, FALSE)
    ON CONFLICT (id_user, id_menu)
    DO UPDATE SET is_deleted = FALSE, updated_at = CURRENT_TIMESTAMP, updated_by = current_user;
    
    RETURN TRUE;
EXCEPTION
    WHEN OTHERS THEN
        RETURN FALSE;
END;
$$ LANGUAGE plpgsql;
