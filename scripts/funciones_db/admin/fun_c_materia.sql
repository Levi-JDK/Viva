-- Generado automáticamente aplicando Skill: create-sql-function
CREATE OR REPLACE FUNCTION fun_c_materia(
    p_id_materia tab_materia_prima.id_materia%TYPE,
    p_nom_materia tab_materia_prima.nom_materia%TYPE
) RETURNS BOOLEAN AS $$
BEGIN

    -- Validaciones en caliente
    IF p_id_materia IS NULL THEN
        RAISE NOTICE 'El parámetro p_id_materia es inválido o nulo.';
        RETURN FALSE;
    END IF;

    IF p_nom_materia IS NULL THEN
        RAISE NOTICE 'El parámetro p_nom_materia es inválido o nulo.';
        RETURN FALSE;
    END IF;

    -- Operación DML Pura
    PERFORM 1 FROM tab_materia_prima WHERE id_materia = p_id_materia;
    IF FOUND THEN 
        RAISE NOTICE 'El registro en tab_materia_prima ya existe.';
        RETURN FALSE; 
    END IF;

    INSERT INTO tab_materia_prima (id_materia, nom_materia)
    VALUES (p_id_materia, p_nom_materia);

    RETURN TRUE;
EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error transaccional en %: %', 'fun_c_materia', SQLERRM;
        RETURN FALSE;
END;
$$ LANGUAGE plpgsql;
