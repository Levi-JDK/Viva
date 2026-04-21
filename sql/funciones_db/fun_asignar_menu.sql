CREATE OR REPLACE FUNCTION fun_asignar_menu(p_id_user INTEGER, p_id_menu INTEGER)
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
