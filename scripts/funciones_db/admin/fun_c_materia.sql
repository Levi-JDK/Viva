CREATE OR REPLACE FUNCTION fun_c_materia(
    p_id_materia  tab_materia_prima.id_materia%TYPE,
    p_nom_materia tab_materia_prima.nom_materia%TYPE
) RETURNS BOOLEAN AS $$
BEGIN
    -- Validacion: ID materia requerido
    IF p_id_materia IS NULL THEN RETURN FALSE; END IF;
    -- Validacion: Nombre materia requerido
    IF p_nom_materia IS NULL OR TRIM(p_nom_materia) = '' THEN RETURN FALSE; END IF;

    PERFORM 1 FROM tab_materia_prima WHERE id_materia = p_id_materia;
    -- Validacion: Materia ya existe
    IF FOUND THEN RETURN FALSE; END IF;

    INSERT INTO tab_materia_prima (id_materia, nom_materia)
    VALUES (p_id_materia, p_nom_materia);

    RETURN TRUE;
END;
$$ LANGUAGE plpgsql;
