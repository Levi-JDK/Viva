-- Generado automáticamente aplicando Skill: create-sql-function
CREATE OR REPLACE FUNCTION fun_c_banco(
    p_id_banco tab_bancos.id_banco%TYPE,
    p_nom_banco tab_bancos.nom_banco%TYPE
) RETURNS BOOLEAN AS $$
BEGIN

    -- Validaciones en caliente
    IF p_id_banco IS NULL THEN
        RAISE NOTICE 'El parámetro p_id_banco es inválido o nulo.';
        RETURN FALSE;
    END IF;

    IF p_nom_banco IS NULL THEN
        RAISE NOTICE 'El parámetro p_nom_banco es inválido o nulo.';
        RETURN FALSE;
    END IF;


    -- Operación DML Pura
    PERFORM 1 FROM tab_bancos WHERE id_banco = p_id_banco;
    IF FOUND THEN 
        RAISE NOTICE 'El registro en tab_bancos ya existe.';
        RETURN FALSE; 
    END IF;

    INSERT INTO tab_bancos (id_banco, nom_banco)
    VALUES (p_id_banco, p_nom_banco);

    RETURN TRUE;
EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error transaccional en %: %', 'fun_c_banco', SQLERRM;
        RETURN FALSE;
END;
$$ LANGUAGE plpgsql;
