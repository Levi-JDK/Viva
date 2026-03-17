CREATE OR REPLACE FUNCTION fun_val_productor(
        p_id_user           tab_productores.id_user%TYPE
) RETURNS BOOLEAN AS
$$
BEGIN   
    IF p_id_user IS NULL OR p_id_user <= 0 THEN
        RETURN FALSE;
    END IF;
    PERFORM id_user FROM tab_productores WHERE id_user = p_id_user;
    IF FOUND THEN
        RETURN TRUE;
    ELSE
        RETURN FALSE;
    END IF;
END;
$$
LANGUAGE PLPGSQL;