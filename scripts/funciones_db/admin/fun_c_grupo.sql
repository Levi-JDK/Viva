-- Generado automáticamente aplicando Skill: create-sql-function
CREATE OR REPLACE FUNCTION fun_c_grupo(
    p_id_grupo tab_grupos.id_grupo%TYPE,
    p_nom_grupo tab_grupos.nom_grupo%TYPE
) RETURNS BOOLEAN AS $$
BEGIN

    -- Validaciones en caliente
    IF p_id_grupo IS NULL THEN
        RAISE NOTICE 'El parámetro p_id_grupo es inválido o nulo.';
        RETURN FALSE;
    END IF;

    IF p_nom_grupo IS NULL THEN
        RAISE NOTICE 'El parámetro p_nom_grupo es inválido o nulo.';
        RETURN FALSE;
    END IF;

    -- Operación DML Pura
    PERFORM 1 FROM tab_grupos WHERE id_grupo = p_id_grupo;
    IF FOUND THEN 
        RAISE NOTICE 'El registro en tab_grupos ya existe.';
        RETURN FALSE; 
    END IF;

    INSERT INTO tab_grupos (id_grupo, nom_grupo)
    VALUES (p_id_grupo, p_nom_grupo);

    RETURN TRUE;
EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error transaccional en %: %', 'fun_c_grupo', SQLERRM;
        RETURN FALSE;
END;
$$ LANGUAGE plpgsql;
