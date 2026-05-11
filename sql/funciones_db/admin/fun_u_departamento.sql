-- Generado automáticamente aplicando Skill: create-sql-function
CREATE OR REPLACE FUNCTION fun_u_departamento(
    p_id_pais tab_departamentos.id_pais%TYPE,
    p_id_departamento tab_departamentos.id_departamento%TYPE,
    p_nom_departamento tab_departamentos.nom_departamento%TYPE
) RETURNS BOOLEAN AS $$
BEGIN

    -- Validaciones en caliente
    IF p_id_pais IS NULL THEN
        RAISE NOTICE 'El parámetro p_id_pais es inválido o nulo.';
        RETURN FALSE;
    END IF;

    IF p_id_departamento IS NULL THEN
        RAISE NOTICE 'El parámetro p_id_departamento es inválido o nulo.';
        RETURN FALSE;
    END IF;

    IF p_nom_departamento IS NULL THEN
        RAISE NOTICE 'El parámetro p_nom_departamento es inválido o nulo.';
        RETURN FALSE;
    END IF;

    -- Operación DML Pura
    PERFORM 1 FROM tab_departamentos WHERE id_pais = p_id_pais;
    IF NOT FOUND THEN 
        RAISE NOTICE 'El registro en tab_departamentos no existe.';
        RETURN FALSE; 
    END IF;

    UPDATE tab_departamentos
    SET id_departamento = p_id_departamento,
        nom_departamento = p_nom_departamento
    WHERE id_pais = p_id_pais;

    RETURN TRUE;
EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error transaccional en %: %', 'fun_u_departamento', SQLERRM;
        RETURN FALSE;
END;
$$ LANGUAGE plpgsql;
