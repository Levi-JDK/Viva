CREATE OR REPLACE FUNCTION fun_c_det_fact(
  p_id_factura   tab_det_fact.id_factura%TYPE,
  p_id_producto  tab_det_fact.id_producto%TYPE,
  p_id_productor tab_det_fact.id_productor%TYPE,
  p_cantidad     tab_det_fact.val_cantidad%TYPE
) RETURNS BOOLEAN AS $$
DECLARE
  w_arancel   tab_paises.arancel_pct%TYPE;
  w_precio    tab_producto_productor.precio_prod%TYPE;
  w_stock     tab_producto_productor.stock_prod%TYPE;
  w_val_bruto tab_det_fact.val_bruto%TYPE;
  w_val_neto  tab_det_fact.val_neto%TYPE;
  w_id_pais   tab_enc_fact.id_pais%TYPE;
BEGIN
  -- Validacion: id_factura requerido
  IF p_id_factura  IS NULL THEN RETURN FALSE; END IF;
  -- Validacion: id_producto requerido
  IF p_id_producto IS NULL THEN RETURN FALSE; END IF;
  -- Validacion: id_productor requerido
  IF p_id_productor IS NULL THEN RETURN FALSE; END IF;
  IF p_cantidad    IS NULL OR p_cantidad < 1 THEN
    -- Validacion: cantidad inválida
    RETURN FALSE;
  END IF;

  -- Factura válida
  SELECT e.id_pais
    INTO w_id_pais
  FROM tab_enc_fact e
  WHERE e.id_factura = p_id_factura
    AND e.is_deleted = FALSE
    AND e.ind_estado = TRUE;
  IF NOT FOUND THEN
    -- Validacion: Factura no existe / anulada / eliminada
    RETURN FALSE;
  END IF;

  -- Precio y stock del productor para el producto
  SELECT pp.precio_prod, pp.stock_prod
    INTO w_precio, w_stock
  FROM tab_producto_productor pp
  WHERE pp.id_producto  = p_id_producto
    AND pp.id_productor = p_id_productor
    AND pp.is_deleted   = FALSE
    AND pp.activo       = TRUE;
  IF NOT FOUND THEN
    -- Validacion: No existe relación producto x productor activa
    RETURN FALSE;
  END IF;

  -- Stock suficiente
  IF p_cantidad > w_stock THEN
    -- Validacion: Stock insuficiente
    RETURN FALSE;
  END IF;

  -- Arancel por país destino
  SELECT p.arancel_pct INTO w_arancel
  FROM tab_paises p
  WHERE p.id_pais = w_id_pais
    AND p.is_deleted = FALSE;
  IF NOT FOUND THEN
    -- Validacion: País no válido para arancel
    RETURN FALSE;
  END IF;

  -- Cálculos
  w_val_bruto := w_precio * p_cantidad;
  w_val_neto  := w_val_bruto * (1 + (w_arancel/100));

  -- Insert detalle
  INSERT INTO tab_det_fact(
    id_factura, id_producto, id_productor, val_cantidad, val_bruto, val_neto
  ) VALUES (
    p_id_factura, p_id_producto, p_id_productor, p_cantidad, w_val_bruto, w_val_neto
  );

  IF NOT FOUND THEN
    -- Error: Fallo insert detalle
    RETURN FALSE;
  END IF;

  -- Actualizar total del encabezado
  UPDATE tab_enc_fact
     SET val_tot_fact = val_tot_fact + w_val_neto
   WHERE id_factura = p_id_factura;
  IF NOT FOUND THEN
    -- Error: No se pudo actualizar total de factura
    RETURN FALSE;
  END IF;

  -- Kardex salida por venta (reduce stock del productor)
  IF (SELECT fun_kardex_mov(p_id_producto, p_id_productor, p_cantidad, FALSE)) IS FALSE THEN
    -- Error: Kardex salida no aplicado
    RETURN FALSE;
  END IF;

  RETURN TRUE;
END;
$$ LANGUAGE plpgsql;
