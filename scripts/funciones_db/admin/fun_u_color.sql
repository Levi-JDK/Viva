CREATE OR REPLACE FUNCTION fun_u_color(
    p_id_color  tab_color.id_color%TYPE,
    p_nom_color tab_color.nom_color%TYPE
) RETURNS BOOLEAN AS $$
DECLARE
    v_nom_color tab_color.nom_color%TYPE;
BEGIN
    IF p_id_color IS NULL OR TRIM(p_id_color) = '' THEN
        -- Validacion: El ID del color es obligatorio.
        RETURN FALSE;
    END IF;

    SELECT nom_color INTO v_nom_color
      FROM tab_color
     WHERE id_color = p_id_color
       AND is_deleted = FALSE;

    IF NOT FOUND THEN
        -- Validacion: Color no encontrado o eliminado.
        RETURN FALSE;
    END IF;

    IF p_nom_color IS NOT NULL AND TRIM(p_nom_color) <> '' THEN
        v_nom_color := p_nom_color;
    END IF;

    UPDATE tab_color
       SET nom_color  = v_nom_color,
           updated_by = current_user,
           updated_at = CURRENT_TIMESTAMP
     WHERE id_color   = p_id_color;

    RETURN TRUE;
END;
$$ LANGUAGE plpgsql;
