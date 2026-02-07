CREATE OR REPLACE FUNCTION fun_c_ciudad(
    p_id_ciudad  tab_ciudades.id_ciudad%TYPE,
    p_nom_ciudad tab_ciudades.nom_ciudad%TYPE,
    p_zip_ciudad tab_ciudades.zip_ciudad%TYPE,
    p_id_pais    tab_ciudades.id_pais%TYPE
) RETURNS BOOLEAN AS $$
BEGIN
    -- Validacion: ID ciudad requerido
    IF p_id_ciudad IS NULL THEN RETURN FALSE; END IF;
    -- Validacion: Nombre ciudad requerido
    IF p_nom_ciudad IS NULL OR TRIM(p_nom_ciudad) = '' THEN RETURN FALSE; END IF;
    -- Validacion: ZIP requerido
    IF p_zip_ciudad IS NULL OR TRIM(p_zip_ciudad) = '' THEN RETURN FALSE; END IF;
    -- Validacion: ID país requerido
    IF p_id_pais IS NULL THEN RETURN FALSE; END IF;

    -- Validar País
    PERFORM 1 FROM tab_paises WHERE id_pais = p_id_pais AND is_deleted = FALSE;
    -- Validacion: País no válido
    IF NOT FOUND THEN RETURN FALSE; END IF;

    -- Validar existencia compuesta (id_ciudad, id_pais)
    PERFORM 1 FROM tab_ciudades WHERE id_ciudad = p_id_ciudad AND id_pais = p_id_pais;
    -- Validacion: Ciudad ya existe en país
    IF FOUND THEN RETURN FALSE; END IF;

    INSERT INTO tab_ciudades (id_ciudad, nom_ciudad, zip_ciudad, id_pais)
    VALUES (p_id_ciudad, p_nom_ciudad, p_zip_ciudad, p_id_pais);

    RETURN TRUE;
END;
$$ LANGUAGE plpgsql;
