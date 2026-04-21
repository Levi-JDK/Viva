-- Generado automáticamente aplicando Skill: create-sql-function
CREATE OR REPLACE FUNCTION fun_u_grupo(
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
    IF NOT FOUND THEN 
        RAISE NOTICE 'El registro en tab_grupos no existe.';
        RETURN FALSE; 
    END IF;

    UPDATE tab_grupos
    SET nom_grupo = p_nom_grupo
    WHERE id_grupo = p_id_grupo;

    RETURN TRUE;
EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error transaccional en %: %', 'fun_u_grupo', SQLERRM;
        RETURN FALSE;
END;
$$ LANGUAGE plpgsql;
