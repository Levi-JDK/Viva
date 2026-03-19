-- Generado automáticamente aplicando Skill: create-sql-function
CREATE OR REPLACE FUNCTION fun_c_categoria(
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
    IF FOUND THEN 
        RAISE NOTICE 'El registro en tab_categorias ya existe.';
        RETURN FALSE; 
    END IF;

    INSERT INTO tab_categorias (id_categoria, nom_categoria)
    VALUES (p_id_categoria, p_nom_categoria);

    RETURN TRUE;
EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error transaccional en %: %', 'fun_c_categoria', SQLERRM;
        RETURN FALSE;
END;
$$ LANGUAGE plpgsql;
