CREATE OR REPLACE FUNCTION fun_c_moneda(
    p_id_moneda  tab_monedas.id_moneda%TYPE,
    p_nom_moneda tab_monedas.nom_moneda%TYPE,
    p_simbolo    tab_monedas.simbolo%TYPE
) RETURNS BOOLEAN AS $$
BEGIN
    -- Validacion: ID moneda requerido
    IF p_id_moneda IS NULL OR TRIM(p_id_moneda) = '' THEN RETURN FALSE; END IF;
    -- Validacion: Nombre moneda requerido
    IF p_nom_moneda IS NULL OR TRIM(p_nom_moneda) = '' THEN RETURN FALSE; END IF;
    -- Validacion: Símbolo requerido
    IF p_simbolo IS NULL OR TRIM(p_simbolo) = '' THEN RETURN FALSE; END IF;

    PERFORM 1 FROM tab_monedas WHERE id_moneda = p_id_moneda;
    -- Validacion: Moneda ya existe
    IF FOUND THEN RETURN FALSE; END IF;

    INSERT INTO tab_monedas (id_moneda, nom_moneda, simbolo)
    VALUES (p_id_moneda, p_nom_moneda, p_simbolo);

    RETURN TRUE;
END;
$$ LANGUAGE plpgsql;
