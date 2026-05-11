-- Generado automáticamente aplicando Skill: create-sql-function
CREATE OR REPLACE FUNCTION fun_c_color(
    p_id_color tab_color.id_color%TYPE,
    p_nom_color tab_color.nom_color%TYPE
) RETURNS BOOLEAN AS $$
BEGIN

    -- Validaciones en caliente
    IF p_id_color IS NULL THEN
        RAISE NOTICE 'El parámetro p_id_color es inválido o nulo.';
        RETURN FALSE;
    END IF;

    IF p_nom_color IS NULL THEN
        RAISE NOTICE 'El parámetro p_nom_color es inválido o nulo.';
        RETURN FALSE;
    END IF;

    -- Operación DML Pura
    PERFORM 1 FROM tab_color WHERE id_color = p_id_color;
    IF FOUND THEN 
        RAISE NOTICE 'El registro en tab_color ya existe.';
        RETURN FALSE; 
    END IF;

    INSERT INTO tab_color (id_color, nom_color)
    VALUES (p_id_color, p_nom_color);

    RETURN TRUE;
EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error transaccional en %: %', 'fun_c_color', SQLERRM;
        RETURN FALSE;
END;
$$ LANGUAGE plpgsql;
