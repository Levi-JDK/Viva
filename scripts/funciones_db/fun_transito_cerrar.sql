-- Cerrar tránsito y acreditar stock al productor (entrada en kardex)
CREATE OR REPLACE FUNCTION fun_transito_cerrar(
  p_id_entrada   tab_transito.id_entrada%TYPE,
  p_id_producto  tab_transito.id_producto%TYPE,
  p_id_productor tab_kardex.id_productor%TYPE
) RETURNS BOOLEAN AS $$
DECLARE
  w_cant tab_transito.val_entrada%TYPE;
BEGIN
  -- Validacion: id_entrada requerido
  IF p_id_entrada   IS NULL THEN RETURN FALSE; END IF;
  -- Validacion: id_producto requerido
  IF p_id_producto  IS NULL THEN RETURN FALSE; END IF;
  -- Validacion: id_productor requerido
  IF p_id_productor IS NULL THEN RETURN FALSE; END IF;

  -- Registro abierto en tránsito
  SELECT t.val_entrada
    INTO w_cant
  FROM tab_transito t
  WHERE t.id_entrada = p_id_entrada
    AND t.id_producto = p_id_producto
    AND t.fec_salida IS NULL
    AND t.is_deleted = FALSE;

  IF NOT FOUND THEN
    -- Validacion: Transito no encontrado o ya cerrado
    RETURN FALSE;
  END IF;

  -- Marcar fecha de salida (no tocamos stock aquí)
  UPDATE tab_transito
     SET fec_salida = CURRENT_TIMESTAMP,
         updated_by = current_user,
         updated_at = CURRENT_TIMESTAMP
   WHERE id_entrada = p_id_entrada
     AND fec_salida IS NULL
     AND is_deleted = FALSE;

  IF NOT FOUND THEN
    -- Error: No se pudo cerrar tránsito
    RETURN FALSE;
  END IF;

  -- Soft delete del tránsito (si tienes la función por tabla)
  PERFORM fun_softdel_tab_transito(p_id_entrada, TRUE);

  -- Ahora sí: ENTRADA a kardex y aumento de stock del productor
  IF (SELECT fun_kardex_mov(p_id_producto, p_id_productor, w_cant, TRUE)) IS FALSE THEN
    -- Error: No se aplicó kardex de entrada para devolución
    RETURN FALSE;
  END IF;

  RETURN TRUE;
END;
$$ LANGUAGE plpgsql;
