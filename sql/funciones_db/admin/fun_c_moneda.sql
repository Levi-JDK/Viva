-- Generado automáticamente aplicando Skill: create-sql-function
CREATE OR REPLACE FUNCTION fun_c_moneda(
    p_id_moneda tab_monedas.id_moneda%TYPE,
    p_nom_moneda tab_monedas.nom_moneda%TYPE,
    p_simbolo tab_monedas.simbolo%TYPE
) RETURNS BOOLEAN AS $$
BEGIN

    -- Validaciones en caliente
    IF p_id_moneda IS NULL THEN
        RAISE NOTICE 'El parámetro p_id_moneda es inválido o nulo.';
        RETURN FALSE;
    END IF;

    IF p_nom_moneda IS NULL THEN
        RAISE NOTICE 'El parámetro p_nom_moneda es inválido o nulo.';
        RETURN FALSE;
    END IF;

    IF p_simbolo IS NULL THEN
        RAISE NOTICE 'El parámetro p_simbolo es inválido o nulo.';
        RETURN FALSE;
    END IF;

    -- Operación DML Pura
    PERFORM 1 FROM tab_monedas WHERE id_moneda = p_id_moneda;
    IF FOUND THEN 
        RAISE NOTICE 'El registro en tab_monedas ya existe.';
        RETURN FALSE; 
    END IF;

    INSERT INTO tab_monedas (id_moneda, nom_moneda, simbolo)
    VALUES (p_id_moneda, p_nom_moneda, p_simbolo);

    RETURN TRUE;
EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error transaccional en %: %', 'fun_c_moneda', SQLERRM;
        RETURN FALSE;
END;
$$ LANGUAGE plpgsql;
