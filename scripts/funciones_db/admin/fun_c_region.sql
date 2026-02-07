CREATE OR REPLACE FUNCTION fun_c_region(
    p_id_region  tab_regiones.id_region%TYPE,
    p_nom_region tab_regiones.nom_region%TYPE
) RETURNS BOOLEAN AS $$
BEGIN
    -- Validacion: ID región requerido
    IF p_id_region IS NULL THEN RETURN FALSE; END IF;
    -- Validacion: Nombre región requerido
    IF p_nom_region IS NULL OR TRIM(p_nom_region) = '' THEN RETURN FALSE; END IF;

    PERFORM 1 FROM tab_regiones WHERE id_region = p_id_region;
    -- Validacion: Región ya existe
    IF FOUND THEN RETURN FALSE; END IF;

    INSERT INTO tab_regiones (id_region, nom_region)
    VALUES (p_id_region, p_nom_region);

    RETURN TRUE;
END;
$$ LANGUAGE plpgsql;
