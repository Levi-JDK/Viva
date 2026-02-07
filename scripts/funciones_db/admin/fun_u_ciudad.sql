CREATE OR REPLACE FUNCTION fun_u_ciudad(
    p_id_ciudad  tab_ciudades.id_ciudad%TYPE,
    p_id_pais    tab_ciudades.id_pais%TYPE,
    p_nom_ciudad tab_ciudades.nom_ciudad%TYPE,
    p_zip_ciudad tab_ciudades.zip_ciudad%TYPE
) RETURNS BOOLEAN AS $$
DECLARE
    v_nom_ciudad tab_ciudades.nom_ciudad%TYPE;
    v_zip_ciudad tab_ciudades.zip_ciudad%TYPE;
BEGIN
    IF p_id_ciudad IS NULL OR p_id_pais IS NULL THEN
        -- Validacion: ID ciudad y País requeridos para identificar el registro
        RETURN FALSE;
    END IF;

    SELECT nom_ciudad, zip_ciudad
      INTO v_nom_ciudad, v_zip_ciudad
      FROM tab_ciudades
     WHERE id_ciudad = p_id_ciudad AND id_pais = p_id_pais AND is_deleted = FALSE;

    IF NOT FOUND THEN
        -- Validacion: Ciudad en país no encontrada
        RETURN FALSE;
    END IF;

    IF p_nom_ciudad IS NOT NULL AND TRIM(p_nom_ciudad) <> '' THEN v_nom_ciudad := p_nom_ciudad; END IF;
    IF p_zip_ciudad IS NOT NULL AND TRIM(p_zip_ciudad) <> '' THEN v_zip_ciudad := p_zip_ciudad; END IF;

    UPDATE tab_ciudades
       SET nom_ciudad = v_nom_ciudad,
           zip_ciudad = v_zip_ciudad,
           updated_by = current_user,
           updated_at = CURRENT_TIMESTAMP
     WHERE id_ciudad  = p_id_ciudad
       AND id_pais    = p_id_pais;

    RETURN TRUE;
END;
$$ LANGUAGE plpgsql;
