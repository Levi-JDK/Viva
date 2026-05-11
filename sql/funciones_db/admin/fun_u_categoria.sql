-- Generado automáticamente aplicando Skill: create-sql-function
CREATE OR REPLACE FUNCTION fun_u_categoria(
    p_id_categoria tab_categorias.id_categoria%TYPE,
    p_nom_categoria tab_categorias.nom_categoria%TYPE
) RETURNS BOOLEAN AS $$
BEGIN

    -- Validaciones en caliente
    IF p_id_categoria IS NULL THEN
        RAISE NOTICE 'El parámetro p_id_categoria es inválido o nulo.';
        RETURN FALSE;
    END IF;

    IF p_nom_categoria IS NULL THEN
        RAISE NOTICE 'El parámetro p_nom_categoria es inválido o nulo.';
        RETURN FALSE;
    END IF;

    -- Operación DML Pura
    PERFORM 1 FROM tab_categorias WHERE id_categoria = p_id_categoria;
    IF NOT FOUND THEN 
        RAISE NOTICE 'El registro en tab_categorias no existe.';
        RETURN FALSE; 
    END IF;

    UPDATE tab_categorias
    SET nom_categoria = p_nom_categoria
    WHERE id_categoria = p_id_categoria;

    RETURN TRUE;
EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error transaccional en %: %', 'fun_u_categoria', SQLERRM;
        RETURN FALSE;
END;
$$ LANGUAGE plpgsql;
