CREATE OR REPLACE FUNCTION fun_u_region(
    p_id_region  tab_regiones.id_region%TYPE,
    p_nom_region tab_regiones.nom_region%TYPE
) RETURNS BOOLEAN AS $$
DECLARE
    v_nom_region tab_regiones.nom_region%TYPE;
BEGIN
    -- Validacion: ID región requerido
    IF p_id_region IS NULL THEN RETURN FALSE; END IF;

    SELECT nom_region INTO v_nom_region FROM tab_regiones WHERE id_region = p_id_region AND is_deleted = FALSE;
    -- Validacion: Región no encontrada
    IF NOT FOUND THEN RETURN FALSE; END IF;

    IF p_nom_region IS NOT NULL AND TRIM(p_nom_region) <> '' THEN v_nom_region := p_nom_region; END IF;

    UPDATE tab_regiones
       SET nom_region = v_nom_region,
           updated_by = current_user,
           updated_at = CURRENT_TIMESTAMP
     WHERE id_region  = p_id_region;

    RETURN TRUE;
END;
$$ LANGUAGE plpgsql;
