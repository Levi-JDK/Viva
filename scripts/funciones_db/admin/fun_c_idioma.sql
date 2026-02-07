CREATE OR REPLACE FUNCTION fun_c_idioma(
    p_id_idioma  tab_idiomas.id_idioma%TYPE,
    p_nom_idioma tab_idiomas.nom_idioma%TYPE
) RETURNS BOOLEAN AS $$
BEGIN
    -- Validacion: ID idioma requerido
    IF p_id_idioma IS NULL OR TRIM(p_id_idioma) = '' THEN RETURN FALSE; END IF;
    -- Validacion: Nombre idioma requerido
    IF p_nom_idioma IS NULL OR TRIM(p_nom_idioma) = '' THEN RETURN FALSE; END IF;

    PERFORM 1 FROM tab_idiomas WHERE id_idioma = p_id_idioma;
    -- Validacion: Idioma ya existe
    IF FOUND THEN RETURN FALSE; END IF;

    INSERT INTO tab_idiomas (id_idioma, nom_idioma)
    VALUES (p_id_idioma, p_nom_idioma);

    RETURN TRUE;
END;
$$ LANGUAGE plpgsql;
