-- Actualización de fun_softdel_tab_productos para manejar el CHECK constraint
-- (stock_productor > 0 AND is_active = TRUE) OR (stock_productor = 0 AND is_active = FALSE)
CREATE OR REPLACE FUNCTION fun_softdel_tab_productos(
  p_id tab_productos.id_producto%TYPE,
  p_deleted tab_productos.is_deleted%TYPE
) RETURNS BOOLEAN AS $$
DECLARE w_id tab_productos.id_producto%TYPE;
BEGIN
  SELECT id_producto INTO w_id FROM tab_productos WHERE id_producto = p_id;
  IF NOT FOUND THEN
    RAISE NOTICE 'No existe producto %', p_id;
    RETURN FALSE;
  ELSE
    -- Al archivar (is_deleted=TRUE) también se desactiva y stock a 0 por CHECK constraint
    -- Al restaurar (is_deleted=FALSE) solo se activa; el stock lo maneja el productor
    UPDATE tab_productos 
    SET is_deleted = p_deleted,
        is_active = CASE WHEN p_deleted = TRUE THEN FALSE ELSE is_active END,
        stock_productor = CASE WHEN p_deleted = TRUE THEN 0 ELSE stock_productor END
    WHERE id_producto = p_id;
    RETURN TRUE;
  END IF;
END; $$ LANGUAGE plpgsql;
