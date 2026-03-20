-- Generado automáticamente aplicando Skill: create-sql-function
CREATE OR REPLACE FUNCTION fun_c_banco(
        p_nom_banco tab_bancos.nom_banco%TYPE
) RETURNS BOOLEAN AS $$
DECLARE
    v_id_banco tab_bancos.id_banco%TYPE;
BEGIN

    -- Validaciones en caliente
    

    IF p_nom_banco IS NULL THEN
        RAISE NOTICE 'El parámetro p_nom_banco es inválido o nulo.';
        RETURN FALSE;
    END IF;


    -- Operación DML Pura

    
    v_id_banco := COALESCE((SELECT MAX(id_banco) FROM tab_bancos), 0) + 1;

    INSERT INTO tab_bancos (id_banco, nom_banco)
    VALUES (v_id_banco, p_nom_banco);

    RETURN TRUE;
EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error transaccional en %: %', 'fun_c_banco', SQLERRM;
        RETURN FALSE;
END;
$$ LANGUAGE plpgsql;
