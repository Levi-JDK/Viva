-- Generado automáticamente aplicando Skill: create-sql-function
CREATE OR REPLACE FUNCTION fun_c_departamento(
        p_id_departamento tab_departamentos.id_departamento%TYPE,
    p_nom_departamento tab_departamentos.nom_departamento%TYPE
) RETURNS BOOLEAN AS $$
DECLARE
    v_id_pais tab_departamentos.id_pais%TYPE;
BEGIN

    -- Validaciones en caliente
    

    

    IF p_nom_departamento IS NULL THEN
        RAISE NOTICE 'El parámetro p_nom_departamento es inválido o nulo.';
        RETURN FALSE;
    END IF;

    -- Operación DML Pura

    
    v_id_pais := COALESCE((SELECT MAX(id_pais) FROM tab_departamentos), 0) + 1;

    INSERT INTO tab_departamentos (id_pais, id_departamento, nom_departamento)
    VALUES (v_id_pais, p_id_departamento, p_nom_departamento);

    RETURN TRUE;
EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error transaccional en %: %', 'fun_c_departamento', SQLERRM;
        RETURN FALSE;
END;
$$ LANGUAGE plpgsql;
