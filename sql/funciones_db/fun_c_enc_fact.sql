-- =============================================================================
-- fun_c_enc_fact.sql
-- Crea el encabezado de factura con dirección de envío y datos ePayco.
-- val_tot_fact inicia en 0 cuando se llama desde fun_facturar (fun_c_det_fact acumula).
-- =============================================================================
CREATE OR REPLACE FUNCTION fun_c_enc_fact(
    p_id_client       tab_enc_fact.id_client%TYPE,
    p_id_pago         tab_enc_fact.id_pago%TYPE,
    p_id_departamento tab_enc_fact.id_departamento%TYPE,
    p_id_ciudad       tab_enc_fact.id_ciudad%TYPE,
    p_dir_envio       tab_enc_fact.dir_envio%TYPE,
    p_val_tot_fact    tab_enc_fact.val_tot_fact%TYPE,   -- puede ser 0 (se acumula después)
    p_epayco_ref      tab_enc_fact.epayco_ref%TYPE,
    p_epayco_txn_id   tab_enc_fact.epayco_txn_id%TYPE,
    p_epayco_estado   tab_enc_fact.epayco_estado%TYPE
) RETURNS tab_enc_fact.id_factura%TYPE AS $$
DECLARE
    w_id_factura tab_enc_fact.id_factura%TYPE;
    w_inifact    tab_pmtros.val_inifact%TYPE;
    w_finfact    tab_pmtros.val_finfact%TYPE;
    w_actfact    tab_pmtros.val_actfact%TYPE;
BEGIN
    -- Validaciones básicas (se acepta val_tot_fact = 0 para acumulación posterior)
    IF p_id_client IS NULL OR TRIM(p_id_client) = '' THEN
        RAISE WARNING '[fun_c_enc_fact] id_client vacío';
        RETURN NULL;
    END IF;
    IF p_id_pago IS NULL OR TRIM(p_id_pago) = '' THEN
        RAISE WARNING '[fun_c_enc_fact] id_pago vacío';
        RETURN NULL;
    END IF;
    IF p_id_departamento IS NULL OR p_id_ciudad IS NULL THEN
        RAISE WARNING '[fun_c_enc_fact] departamento o ciudad nulos';
        RETURN NULL;
    END IF;
    IF p_val_tot_fact IS NULL OR p_val_tot_fact < 0 THEN
        RAISE WARNING '[fun_c_enc_fact] val_tot_fact inválido: %', p_val_tot_fact;
        RETURN NULL;
    END IF;

    -- Forma de pago válida
    PERFORM 1 FROM tab_formas_pago WHERE id_pago = p_id_pago AND is_deleted = FALSE;
    IF NOT FOUND THEN
        RAISE WARNING '[fun_c_enc_fact] Forma de pago % no encontrada en tab_formas_pago', p_id_pago;
        RETURN NULL;
    END IF;

    -- Consecutivo de factura desde tab_pmtros
    SELECT val_inifact, val_finfact, val_actfact
      INTO w_inifact, w_finfact, w_actfact
    FROM tab_pmtros WHERE id_parametro = 1;

    IF NOT FOUND THEN
        RAISE WARNING '[fun_c_enc_fact] No se encontró fila en tab_pmtros (id_parametro=1)';
        RETURN NULL;
    END IF;

    IF w_actfact < w_inifact OR w_actfact > w_finfact THEN
        RAISE WARNING '[fun_c_enc_fact] Consecutivo agotado o fuera de rango (act=%, ini=%, fin=%)',
                      w_actfact, w_inifact, w_finfact;
        RETURN NULL;
    END IF;

    w_id_factura := w_actfact;

    INSERT INTO tab_enc_fact (
        id_factura, fec_factura, val_hora_fact,
        id_client, id_pais, id_departamento, id_ciudad, dir_envio,
        val_tot_fact, ind_estado, id_pago,
        epayco_ref, epayco_txn_id, epayco_estado
    ) VALUES (
        w_id_factura, CURRENT_DATE, CURRENT_TIME,
        p_id_client, 1, p_id_departamento, p_id_ciudad, p_dir_envio,
        COALESCE(p_val_tot_fact, 0), TRUE, p_id_pago,
        p_epayco_ref, p_epayco_txn_id, p_epayco_estado
    );

    -- Avanzar consecutivo
    UPDATE tab_pmtros SET val_actfact = val_actfact + 1 WHERE id_parametro = 1;

    RETURN w_id_factura;

EXCEPTION WHEN OTHERS THEN
    RAISE WARNING '[fun_c_enc_fact] %', SQLERRM;
    RETURN NULL;
END;
$$ LANGUAGE plpgsql;
