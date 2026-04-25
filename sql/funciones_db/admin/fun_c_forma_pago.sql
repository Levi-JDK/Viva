-- Generado automáticamente aplicando Skill: create-sql-function
CREATE OR REPLACE FUNCTION fun_c_forma_pago(
    p_id_pago tab_formas_pago.id_pago%TYPE,
    p_nom_pago tab_formas_pago.nom_pago%TYPE
) RETURNS BOOLEAN AS $$
BEGIN

    -- Validaciones en caliente
    IF p_id_pago IS NULL THEN
        RAISE NOTICE 'El parámetro p_id_pago es inválido o nulo.';
        RETURN FALSE;
    END IF;

    IF p_nom_pago IS NULL THEN
        RAISE NOTICE 'El parámetro p_nom_pago es inválido o nulo.';
        RETURN FALSE;
    END IF;

    -- Operación DML Pura
    PERFORM 1 FROM tab_formas_pago WHERE id_pago = p_id_pago;
    IF FOUND THEN 
        RAISE NOTICE 'El registro en tab_formas_pago ya existe.';
        RETURN FALSE; 
    END IF;

    INSERT INTO tab_formas_pago (id_pago, nom_pago)
    VALUES (p_id_pago, p_nom_pago);

    RETURN TRUE;
EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error transaccional en %: %', 'fun_c_forma_pago', SQLERRM;
        RETURN FALSE;
END;
$$ LANGUAGE plpgsql;
