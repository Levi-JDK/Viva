-- Generado automáticamente aplicando Skill: create-sql-function
CREATE OR REPLACE FUNCTION fun_c_ciudad(
        p_nom_ciudad tab_ciudades.nom_ciudad%TYPE,
    p_zip_ciudad tab_ciudades.zip_ciudad%TYPE,
    p_id_pais tab_ciudades.id_pais%TYPE
) RETURNS BOOLEAN AS $$
DECLARE
    v_id_ciudad tab_ciudades.id_ciudad%TYPE;
BEGIN

    -- Validaciones en caliente
    

    IF p_nom_ciudad IS NULL THEN
        RAISE NOTICE 'El parámetro p_nom_ciudad es inválido o nulo.';
        RETURN FALSE;
    END IF;

    IF p_zip_ciudad IS NULL THEN
        RAISE NOTICE 'El parámetro p_zip_ciudad es inválido o nulo.';
        RETURN FALSE;
    END IF;

    

    -- Operación DML Pura

    
    v_id_ciudad := COALESCE((SELECT MAX(id_ciudad) FROM tab_ciudades), 0) + 1;

    INSERT INTO tab_ciudades (id_ciudad, nom_ciudad, zip_ciudad, id_pais)
    VALUES (v_id_ciudad, p_nom_ciudad, p_zip_ciudad, p_id_pais);

    RETURN TRUE;
EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error transaccional en %: %', 'fun_c_ciudad', SQLERRM;
        RETURN FALSE;
END;
$$ LANGUAGE plpgsql;
