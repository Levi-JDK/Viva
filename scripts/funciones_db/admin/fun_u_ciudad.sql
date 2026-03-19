-- Generado automáticamente aplicando Skill: create-sql-function
CREATE OR REPLACE FUNCTION fun_u_ciudad(
    p_id_ciudad tab_ciudades.id_ciudad%TYPE,
    p_id_pais tab_ciudades.id_pais%TYPE,
    p_nom_ciudad tab_ciudades.nom_ciudad%TYPE,
    p_zip_ciudad tab_ciudades.zip_ciudad%TYPE
) RETURNS BOOLEAN AS $$
BEGIN

    -- Validaciones en caliente
    IF p_id_ciudad IS NULL THEN
        RAISE NOTICE 'El parámetro p_id_ciudad es inválido o nulo.';
        RETURN FALSE;
    END IF;

    IF p_id_pais IS NULL THEN
        RAISE NOTICE 'El parámetro p_id_pais es inválido o nulo.';
        RETURN FALSE;
    END IF;

    IF p_nom_ciudad IS NULL THEN
        RAISE NOTICE 'El parámetro p_nom_ciudad es inválido o nulo.';
        RETURN FALSE;
    END IF;

    IF p_zip_ciudad IS NULL THEN
        RAISE NOTICE 'El parámetro p_zip_ciudad es inválido o nulo.';
        RETURN FALSE;
    END IF;

    -- Operación DML Pura
    PERFORM 1 FROM tab_ciudades WHERE id_ciudad = p_id_ciudad;
    IF NOT FOUND THEN 
        RAISE NOTICE 'El registro en tab_ciudades no existe.';
        RETURN FALSE; 
    END IF;

    UPDATE tab_ciudades
    SET id_pais = p_id_pais,
        nom_ciudad = p_nom_ciudad,
        zip_ciudad = p_zip_ciudad
    WHERE id_ciudad = p_id_ciudad;

    RETURN TRUE;
EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error transaccional en %: %', 'fun_u_ciudad', SQLERRM;
        RETURN FALSE;
END;
$$ LANGUAGE plpgsql;
