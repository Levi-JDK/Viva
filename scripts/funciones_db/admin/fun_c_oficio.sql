CREATE OR REPLACE FUNCTION fun_c_oficio(
    p_id_oficio  tab_oficios.id_oficio%TYPE,
    p_nom_oficio tab_oficios.nom_oficio%TYPE
) RETURNS BOOLEAN AS $$
BEGIN
    -- Validacion: ID oficio requerido
    IF p_id_oficio IS NULL THEN RETURN FALSE; END IF;
    -- Validacion: Nombre oficio requerido
    IF p_nom_oficio IS NULL OR TRIM(p_nom_oficio) = '' THEN RETURN FALSE; END IF;

    PERFORM 1 FROM tab_oficios WHERE id_oficio = p_id_oficio;
    -- Validacion: Oficio ya existe
    IF FOUND THEN RETURN FALSE; END IF;

    INSERT INTO tab_oficios (id_oficio, nom_oficio)
    VALUES (p_id_oficio, p_nom_oficio);

    RETURN TRUE;
END;
$$ LANGUAGE plpgsql;
