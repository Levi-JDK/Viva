CREATE OR REPLACE FUNCTION fun_c_grupo(
    p_id_grupo  tab_grupos.id_grupo%TYPE,
    p_nom_grupo tab_grupos.nom_grupo%TYPE
) RETURNS BOOLEAN AS $$
BEGIN
    -- Validacion: ID grupo requerido
    IF p_id_grupo IS NULL THEN RETURN FALSE; END IF;
    -- Validacion: Nombre grupo requerido
    IF p_nom_grupo IS NULL OR TRIM(p_nom_grupo) = '' THEN RETURN FALSE; END IF;

    PERFORM 1 FROM tab_grupos WHERE id_grupo = p_id_grupo;
    -- Validacion: El grupo ya existe
    IF FOUND THEN RETURN FALSE; END IF;

    INSERT INTO tab_grupos (id_grupo, nom_grupo)
    VALUES (p_id_grupo, p_nom_grupo);

    RETURN TRUE;
END;
$$ LANGUAGE plpgsql;
