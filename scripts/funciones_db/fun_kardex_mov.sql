CREATE OR REPLACE FUNCTION fun_kardex_mov(
  p_id_producto  tab_kardex.id_producto%TYPE,
  p_id_productor tab_kardex.id_productor%TYPE,
  p_cantidad     tab_kardex.cantidad%TYPE,
  p_tipo_movim   tab_kardex.tipo_movim%TYPE
) RETURNS BOOLEAN AS $$
DECLARE
  w_stock  tab_producto_productor.stock_prod%TYPE;
  w_id_kdx tab_kardex.id_kardex%TYPE;
BEGIN
  -- Validacion: id_producto requerido
  IF p_id_producto  IS NULL THEN RETURN FALSE; END IF;
  -- Validacion: id_productor requerido
  IF p_id_productor IS NULL THEN RETURN FALSE; END IF;
  IF p_cantidad     IS NULL OR p_cantidad < 1 THEN
    -- Validacion: cantidad inválida
    RETURN FALSE;
  END IF;
  IF p_tipo_movim IS NULL THEN
    -- Validacion: tipo_movim requerido
    RETURN FALSE;
  END IF;

  -- Par producto×productor válido y activo
  SELECT stock_prod INTO w_stock
  FROM tab_producto_productor
  WHERE id_producto = p_id_producto
    AND id_productor = p_id_productor
    AND is_deleted = FALSE
    AND activo = TRUE;

  IF NOT FOUND THEN
    -- Validacion: No existe relación producto x productor activa
    RETURN FALSE;
  END IF;

  -- Si es salida, validar stock
  IF p_tipo_movim = FALSE AND p_cantidad > w_stock THEN
    -- Validacion: Stock insuficiente
    RETURN FALSE;
  END IF;

  -- id_kardex nuevo
  SELECT COALESCE(MAX(id_kardex),0)+1 INTO w_id_kdx FROM tab_kardex;

  INSERT INTO tab_kardex(
    id_kardex, id_producto, id_productor, tipo_movim, cantidad
  ) VALUES (
    w_id_kdx, p_id_producto, p_id_productor, p_tipo_movim, p_cantidad
  );

  IF NOT FOUND THEN
    -- Error: Fallo insert en kardex
    RETURN FALSE;
  END IF;

  -- Ajuste de stock del productor
  IF p_tipo_movim = TRUE THEN
    UPDATE tab_producto_productor
       SET stock_prod = stock_prod + p_cantidad
     WHERE id_producto = p_id_producto
       AND id_productor = p_id_productor;
  ELSE
    UPDATE tab_producto_productor
       SET stock_prod = stock_prod - p_cantidad
     WHERE id_producto = p_id_producto
       AND id_productor = p_id_productor;
  END IF;

  IF NOT FOUND THEN
    -- Error: No se pudo actualizar stock del productor
    RETURN FALSE;
  END IF;

  RETURN TRUE;
END;
$$ LANGUAGE plpgsql;
