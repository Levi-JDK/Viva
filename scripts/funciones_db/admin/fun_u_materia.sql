CREATE OR REPLACE FUNCTION fun_u_materia(
    p_id_materia  tab_materia_prima.id_materia%TYPE,
    p_nom_materia tab_materia_prima.nom_materia%TYPE
) RETURNS BOOLEAN AS $$
DECLARE
    v_nom_materia tab_materia_prima.nom_materia%TYPE;
BEGIN
    -- Validacion: ID materia requerido
    IF p_id_materia IS NULL THEN RETURN FALSE; END IF;

    SELECT nom_materia INTO v_nom_materia FROM tab_materia_prima WHERE id_materia = p_id_materia AND is_deleted = FALSE;
    -- Validacion: Materia no encontrada
    IF NOT FOUND THEN RETURN FALSE; END IF;

    IF p_nom_materia IS NOT NULL AND TRIM(p_nom_materia) <> '' THEN v_nom_materia := p_nom_materia; END IF;

    UPDATE tab_materia_prima
       SET nom_materia = v_nom_materia,
           updated_by  = current_user,
           updated_at  = CURRENT_TIMESTAMP
     WHERE id_materia  = p_id_materia;

    RETURN TRUE;
END;
$$ LANGUAGE plpgsql;
