-- Generado automáticamente aplicando Skill: create-sql-function
CREATE OR REPLACE FUNCTION fun_c_transito(
    p_id_producto tab_transito.id_producto%TYPE,
    p_cantidad tab_transito.val_entrada%TYPE
) RETURNS BOOLEAN AS $$
BEGIN

    -- Validaciones en caliente
    IF p_id_producto IS NULL THEN
        RAISE NOTICE 'El parámetro p_id_producto es inválido o nulo.';
        RETURN FALSE;
    END IF;

    IF p_cantidad IS NULL THEN
        RAISE NOTICE 'El parámetro p_cantidad es inválido o nulo.';
        RETURN FALSE;
    END IF;

    -- Operación DML Pura
    PERFORM 1 FROM tab_transito WHERE id_producto = p_id_producto;
    IF FOUND THEN 
        RAISE NOTICE 'El registro en tab_transito ya existe.';
        RETURN FALSE; 
    END IF;

    INSERT INTO tab_transito (id_producto, val_entrada)
    VALUES (p_id_producto, p_cantidad);

    RETURN TRUE;
EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error transaccional en %: %', 'fun_c_transito', SQLERRM;
        RETURN FALSE;
END;
$$ LANGUAGE plpgsql;
