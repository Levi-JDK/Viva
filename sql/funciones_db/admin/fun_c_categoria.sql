-- Generado automáticamente aplicando Skill: create-sql-function
CREATE OR REPLACE FUNCTION fun_c_categoria(
        p_nom_categoria tab_categorias.nom_categoria%TYPE
) RETURNS BOOLEAN AS $$
DECLARE
    v_id_categoria tab_categorias.id_categoria%TYPE;
BEGIN

    -- Validaciones en caliente
    

    IF p_nom_categoria IS NULL THEN
        RAISE NOTICE 'El parámetro p_nom_categoria es inválido o nulo.';
        RETURN FALSE;
    END IF;

    -- Operación DML Pura

    
    v_id_categoria := COALESCE((SELECT MAX(id_categoria) FROM tab_categorias), 0) + 1;

    INSERT INTO tab_categorias (id_categoria, nom_categoria)
    VALUES (v_id_categoria, p_nom_categoria);

    RETURN TRUE;
EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error transaccional en %: %', 'fun_c_categoria', SQLERRM;
        RETURN FALSE;
END;
$$ LANGUAGE plpgsql;
