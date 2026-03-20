-- Generado automáticamente aplicando Skill: create-sql-function
CREATE OR REPLACE FUNCTION fun_c_transito(
        p_cantidad tab_transito.val_entrada%TYPE
) RETURNS BOOLEAN AS $$
DECLARE
    v_id_producto tab_transito.id_producto%TYPE;
BEGIN

    -- Validaciones en caliente
    

    IF p_cantidad IS NULL THEN
        RAISE NOTICE 'El parámetro p_cantidad es inválido o nulo.';
        RETURN FALSE;
    END IF;

    -- Operación DML Pura

    
    v_id_producto := COALESCE((SELECT MAX(id_producto) FROM tab_transito), 0) + 1;

    INSERT INTO tab_transito (id_producto, val_entrada)
    VALUES (v_id_producto, p_cantidad);

    RETURN TRUE;
EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error transaccional en %: %', 'fun_c_transito', SQLERRM;
        RETURN FALSE;
END;
$$ LANGUAGE plpgsql;
