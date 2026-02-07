CREATE OR REPLACE FUNCTION fun_u_moneda(
    p_id_moneda  tab_monedas.id_moneda%TYPE,
    p_nom_moneda tab_monedas.nom_moneda%TYPE,
    p_simbolo    tab_monedas.simbolo%TYPE
) RETURNS BOOLEAN AS $$
DECLARE
    v_nom_moneda tab_monedas.nom_moneda%TYPE;
    v_simbolo    tab_monedas.simbolo%TYPE;
BEGIN
    -- Validacion: ID moneda requerido
    IF p_id_moneda IS NULL OR TRIM(p_id_moneda) = '' THEN RETURN FALSE; END IF;

    SELECT nom_moneda, simbolo INTO v_nom_moneda, v_simbolo 
      FROM tab_monedas WHERE id_moneda = p_id_moneda AND is_deleted = FALSE;
    -- Validacion: Moneda no encontrada
    IF NOT FOUND THEN RETURN FALSE; END IF;

    IF p_nom_moneda IS NOT NULL AND TRIM(p_nom_moneda) <> '' THEN v_nom_moneda := p_nom_moneda; END IF;
    IF p_simbolo IS NOT NULL AND TRIM(p_simbolo) <> '' THEN v_simbolo := p_simbolo; END IF;

    UPDATE tab_monedas
       SET nom_moneda = v_nom_moneda,
           simbolo    = v_simbolo,
           updated_by = current_user,
           updated_at = CURRENT_TIMESTAMP
     WHERE id_moneda  = p_id_moneda;

    RETURN TRUE;
END;
$$ LANGUAGE plpgsql;
