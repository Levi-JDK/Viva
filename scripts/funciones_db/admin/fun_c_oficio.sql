-- Generado automáticamente aplicando Skill: create-sql-function
CREATE OR REPLACE FUNCTION fun_c_oficio(
    p_id_oficio tab_oficios.id_oficio%TYPE,
    p_nom_oficio tab_oficios.nom_oficio%TYPE
) RETURNS BOOLEAN AS $$
BEGIN

    -- Validaciones en caliente
    IF p_id_oficio IS NULL THEN
        RAISE NOTICE 'El parámetro p_id_oficio es inválido o nulo.';
        RETURN FALSE;
    END IF;

    IF p_nom_oficio IS NULL THEN
        RAISE NOTICE 'El parámetro p_nom_oficio es inválido o nulo.';
        RETURN FALSE;
    END IF;

    -- Operación DML Pura
    PERFORM 1 FROM tab_oficios WHERE id_oficio = p_id_oficio;
    IF FOUND THEN 
        RAISE NOTICE 'El registro en tab_oficios ya existe.';
        RETURN FALSE; 
    END IF;

    INSERT INTO tab_oficios (id_oficio, nom_oficio)
    VALUES (p_id_oficio, p_nom_oficio);

    RETURN TRUE;
EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error transaccional en %: %', 'fun_c_oficio', SQLERRM;
        RETURN FALSE;
END;
$$ LANGUAGE plpgsql;
