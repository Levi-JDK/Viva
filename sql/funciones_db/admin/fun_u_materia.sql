-- Generado automáticamente aplicando Skill: create-sql-function
CREATE OR REPLACE FUNCTION fun_u_materia(
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
    IF NOT FOUND THEN 
        RAISE NOTICE 'El registro en tab_materia_prima no existe.';
        RETURN FALSE; 
    END IF;

    UPDATE tab_materia_prima
    SET nom_materia = p_nom_materia
    WHERE id_materia = p_id_materia;

    RETURN TRUE;
EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error transaccional en %: %', 'fun_u_materia', SQLERRM;
        RETURN FALSE;
END;
$$ LANGUAGE plpgsql;
