CREATE OR REPLACE FUNCTION fun_u_forma_pago(
    p_id_pago  tab_formas_pago.id_pago%TYPE,
    p_nom_pago tab_formas_pago.nom_pago%TYPE
) RETURNS BOOLEAN AS $$
DECLARE
    v_nom_pago tab_formas_pago.nom_pago%TYPE;
BEGIN
    -- Validacion: ID pago requerido
    IF p_id_pago IS NULL OR TRIM(p_id_pago) = '' THEN RETURN FALSE; END IF;

    SELECT nom_pago INTO v_nom_pago FROM tab_formas_pago WHERE id_pago = p_id_pago AND is_deleted = FALSE;
    -- Validacion: Forma pago no encontrada
    IF NOT FOUND THEN RETURN FALSE; END IF;

    IF p_nom_pago IS NOT NULL AND TRIM(p_nom_pago) <> '' THEN v_nom_pago := p_nom_pago; END IF;

    UPDATE tab_formas_pago
       SET nom_pago   = v_nom_pago,
           updated_by = current_user,
           updated_at = CURRENT_TIMESTAMP
     WHERE id_pago    = p_id_pago;

    RETURN TRUE;
END;
$$ LANGUAGE plpgsql;
