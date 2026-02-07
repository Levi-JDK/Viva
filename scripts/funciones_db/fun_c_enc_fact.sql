CREATE OR REPLACE FUNCTION fun_c_enc_fact(
  p_id_client tab_enc_fact.id_client%TYPE,
  p_id_pago   tab_enc_fact.id_pago%TYPE
) RETURNS tab_enc_fact.id_factura%TYPE AS $$
DECLARE
  w_id_factura tab_enc_fact.id_factura%TYPE;
  w_id_pais    tab_clientes.id_pais%TYPE;
  w_id_ciudad  tab_clientes.id_ciudad%TYPE;
  w_inifact    tab_pmtros.val_inifact%TYPE;
  w_finfact    tab_pmtros.val_finfact%TYPE;
  w_actfact    tab_pmtros.val_actfact%TYPE;
BEGIN
  -- Validacion: id_client requerido
  IF p_id_client IS NULL THEN RETURN NULL; END IF;
  -- Validacion: id_pago requerido
  IF p_id_pago   IS NULL THEN RETURN NULL; END IF;

  -- Cliente válido
  SELECT c.id_pais, c.id_ciudad
    INTO w_id_pais, w_id_ciudad
  FROM tab_clientes c
  WHERE c.id_client = p_id_client
    AND c.is_deleted = FALSE;
  IF NOT FOUND THEN
    -- Validacion: Cliente no existe o está eliminado
    RETURN NULL;
  END IF;

  -- Forma de pago válida
  PERFORM 1 FROM tab_formas_pago fp WHERE fp.id_pago = p_id_pago AND fp.is_deleted = FALSE;
  IF NOT FOUND THEN
    -- Validacion: Forma de pago no válida
    RETURN NULL;
  END IF;

  -- Consecutivo
  SELECT val_inifact, val_finfact, val_actfact
    INTO w_inifact, w_finfact, w_actfact
  FROM tab_pmtros
  WHERE id_parametro = 1;

  IF NOT FOUND THEN
    -- Error: No hay registro en tab_pmtros
    RETURN NULL;
  END IF;

  IF w_actfact < w_inifact OR w_actfact > w_finfact THEN
    -- Validacion: Consecutivo fuera de rango
    RETURN NULL;
  END IF;

  w_id_factura := w_actfact;

  -- Insert encabezado
  INSERT INTO tab_enc_fact(
    id_factura, fec_factura, val_hora_fact,
    id_client, id_pais, id_ciudad,
    val_tot_fact, ind_estado, id_pago
  )
  VALUES (
    w_id_factura, CURRENT_DATE, CURRENT_TIME,
    p_id_client, w_id_pais, w_id_ciudad,
    0.00, TRUE, p_id_pago
  );

  IF NOT FOUND THEN
    -- Error: Fallo al insertar encabezado
    RETURN NULL;
  END IF;

  -- Incrementar consecutivo
  UPDATE tab_pmtros
     SET val_actfact = val_actfact + 1
   WHERE id_parametro = 1;

  RETURN w_id_factura;
END;
$$ LANGUAGE plpgsql;
