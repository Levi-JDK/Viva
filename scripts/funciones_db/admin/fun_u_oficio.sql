CREATE OR REPLACE FUNCTION fun_u_oficio(
    p_id_oficio  tab_oficios.id_oficio%TYPE,
    p_nom_oficio tab_oficios.nom_oficio%TYPE
) RETURNS BOOLEAN AS $$
DECLARE
    v_nom_oficio tab_oficios.nom_oficio%TYPE;
BEGIN
    -- Validacion: ID oficio requerido
    IF p_id_oficio IS NULL THEN RETURN FALSE; END IF;

    SELECT nom_oficio INTO v_nom_oficio FROM tab_oficios WHERE id_oficio = p_id_oficio AND is_deleted = FALSE;
    -- Validacion: Oficio no encontrado
    IF NOT FOUND THEN RETURN FALSE; END IF;

    IF p_nom_oficio IS NOT NULL AND TRIM(p_nom_oficio) <> '' THEN v_nom_oficio := p_nom_oficio; END IF;

    UPDATE tab_oficios
       SET nom_oficio = v_nom_oficio,
           updated_by = current_user,
           updated_at = CURRENT_TIMESTAMP
     WHERE id_oficio  = p_id_oficio;

    RETURN TRUE;
END;
$$ LANGUAGE plpgsql;
