-- Generado automáticamente aplicando Skill: create-sql-function
CREATE OR REPLACE FUNCTION fun_c_grupo(
        p_nom_grupo tab_grupos.nom_grupo%TYPE
) RETURNS BOOLEAN AS $$
DECLARE
    v_id_grupo tab_grupos.id_grupo%TYPE;
BEGIN

    -- Validaciones en caliente
    

    IF p_nom_grupo IS NULL THEN
        RAISE NOTICE 'El parámetro p_nom_grupo es inválido o nulo.';
        RETURN FALSE;
    END IF;

    -- Operación DML Pura

    
    v_id_grupo := COALESCE((SELECT MAX(id_grupo) FROM tab_grupos), 0) + 1;

    INSERT INTO tab_grupos (id_grupo, nom_grupo)
    VALUES (v_id_grupo, p_nom_grupo);

    RETURN TRUE;
EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error transaccional en %: %', 'fun_c_grupo', SQLERRM;
        RETURN FALSE;
END;
$$ LANGUAGE plpgsql;
