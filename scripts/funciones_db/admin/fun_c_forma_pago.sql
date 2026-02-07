CREATE OR REPLACE FUNCTION fun_c_forma_pago(
    p_id_pago  tab_formas_pago.id_pago%TYPE,
    p_nom_pago tab_formas_pago.nom_pago%TYPE
) RETURNS BOOLEAN AS $$
BEGIN
    -- Validacion: ID pago requerido
    IF p_id_pago IS NULL OR TRIM(p_id_pago) = '' THEN RETURN FALSE; END IF;
    -- Validacion: Nombre pago requerido
    IF p_nom_pago IS NULL OR TRIM(p_nom_pago) = '' THEN RETURN FALSE; END IF;

    PERFORM 1 FROM tab_formas_pago WHERE id_pago = p_id_pago;
    -- Validacion: Forma pago ya existe
    IF FOUND THEN RETURN FALSE; END IF;

    INSERT INTO tab_formas_pago (id_pago, nom_pago)
    VALUES (p_id_pago, p_nom_pago);

    RETURN TRUE;
END;
$$ LANGUAGE plpgsql;
