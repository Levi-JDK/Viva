-- Generado automáticamente aplicando Skill: create-sql-function
CREATE OR REPLACE FUNCTION fun_c_materia(
        p_nom_materia tab_materia_prima.nom_materia%TYPE
) RETURNS BOOLEAN AS $$
DECLARE
    v_id_materia tab_materia_prima.id_materia%TYPE;
BEGIN

    -- Validaciones en caliente
    

    IF p_nom_materia IS NULL THEN
        RAISE NOTICE 'El parámetro p_nom_materia es inválido o nulo.';
        RETURN FALSE;
    END IF;

    -- Operación DML Pura

    
    v_id_materia := COALESCE((SELECT MAX(id_materia) FROM tab_materia_prima), 0) + 1;

    INSERT INTO tab_materia_prima (id_materia, nom_materia)
    VALUES (v_id_materia, p_nom_materia);

    RETURN TRUE;
EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error transaccional en %: %', 'fun_c_materia', SQLERRM;
        RETURN FALSE;
END;
$$ LANGUAGE plpgsql;
