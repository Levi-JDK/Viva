-- Generado automáticamente aplicando Skill: create-sql-function
CREATE OR REPLACE FUNCTION fun_u_banco(
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
    IF NOT FOUND THEN 
        RAISE NOTICE 'El registro en tab_bancos no existe.';
        RETURN FALSE; 
    END IF;

    UPDATE tab_bancos
    SET nom_banco = p_nom_banco
    WHERE id_banco = p_id_banco;

    RETURN TRUE;
EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error transaccional en %: %', 'fun_u_banco', SQLERRM;
        RETURN FALSE;
END;
$$ LANGUAGE plpgsql;
