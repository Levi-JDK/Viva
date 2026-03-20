-- Generado automáticamente aplicando Skill: create-sql-function
CREATE OR REPLACE FUNCTION fun_c_oficio(
        p_nom_oficio tab_oficios.nom_oficio%TYPE
) RETURNS BOOLEAN AS $$
DECLARE
    v_id_oficio tab_oficios.id_oficio%TYPE;
BEGIN

    -- Validaciones en caliente
    

    IF p_nom_oficio IS NULL THEN
        RAISE NOTICE 'El parámetro p_nom_oficio es inválido o nulo.';
        RETURN FALSE;
    END IF;

    -- Operación DML Pura

    
    v_id_oficio := COALESCE((SELECT MAX(id_oficio) FROM tab_oficios), 0) + 1;

    INSERT INTO tab_oficios (id_oficio, nom_oficio)
    VALUES (v_id_oficio, p_nom_oficio);

    RETURN TRUE;
EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error transaccional en %: %', 'fun_c_oficio', SQLERRM;
        RETURN FALSE;
END;
$$ LANGUAGE plpgsql;
