CREATE OR REPLACE FUNCTION fun_c_transito(
  p_id_producto tab_transito.id_producto%TYPE,
  p_cantidad    tab_transito.val_entrada%TYPE
) RETURNS BOOLEAN AS $$
DECLARE
  w_id_prod tab_productos.id_producto%TYPE;
  w_new_id  tab_transito.id_entrada%TYPE;
BEGIN
  -- Validacion: id_producto requerido
  IF p_id_producto IS NULL THEN RETURN FALSE; END IF;
  IF p_cantidad    IS NULL OR p_cantidad < 1 THEN
    -- Validacion: cantidad inválida
    RETURN FALSE;
  END IF;

  -- Producto válido
  SELECT id_producto INTO w_id_prod
  FROM tab_productos
  WHERE id_producto = p_id_producto
    AND is_deleted = FALSE;
  IF NOT FOUND THEN
    -- Validacion: Producto no existe o está eliminado
    RETURN FALSE;
  END IF;

  SELECT COALESCE(MAX(id_entrada),0)+1 INTO w_new_id FROM tab_transito;

  INSERT INTO tab_transito(id_entrada, id_producto, val_entrada)
  VALUES (w_new_id, p_id_producto, p_cantidad);

  IF NOT FOUND THEN
    -- Error: Fallo insert en transito
    RETURN FALSE;
  END IF;

  RETURN TRUE;
END;
$$ LANGUAGE plpgsql;
