CREATE OR REPLACE FUNCTION fun_u_producto_productor(
  p_id_producto  tab_producto_productor.id_producto%TYPE,
  p_id_productor tab_producto_productor.id_productor%TYPE,
  p_precio       tab_producto_productor.precio_prod%TYPE,
  p_stock        tab_producto_productor.stock_prod%TYPE,
  p_desc         tab_producto_productor.desc_prod_personal%TYPE,
  p_img          tab_producto_productor.img_personal%TYPE,
  p_activo       tab_producto_productor.activo%TYPE
) RETURNS BOOLEAN AS $$
BEGIN
  IF p_id_producto  IS NULL OR p_id_productor IS NULL THEN
    -- Validacion: ids compuestos requeridos
    RETURN FALSE;
  END IF;

  PERFORM 1 FROM tab_producto_productor pp WHERE pp.id_producto = p_id_producto AND pp.id_productor = p_id_productor;
  -- Validacion: Relación no existe
  IF NOT FOUND THEN RETURN FALSE; END IF;

  -- Validacion: precio inválido
  IF p_precio IS NULL OR p_precio < 0 THEN RETURN FALSE; END IF;
  -- Validacion: stock inválido
  IF p_stock  IS NULL OR p_stock  < 0 THEN RETURN FALSE; END IF;
  -- Validacion: desc requerido
  IF p_desc   IS NULL OR length(btrim(p_desc))=0 THEN RETURN FALSE; END IF;
  -- Validacion: img requerida
  IF p_img    IS NULL OR length(btrim(p_img))=0  THEN RETURN FALSE; END IF;
  -- Validacion: activo requerido
  IF p_activo IS NULL THEN RETURN FALSE; END IF;

  IF (p_stock = 0 AND p_activo) OR (p_stock > 0 AND NOT p_activo) THEN
    -- Validacion: Regla de estado inválida: stock=0 => activo=FALSE; stock>0 => activo=TRUE
    RETURN FALSE;
  END IF;

  UPDATE tab_producto_productor
     SET precio_prod = p_precio,
         stock_prod  = p_stock,
         desc_prod_personal = p_desc,
         img_personal       = p_img,
         activo      = p_activo,
         updated_by  = current_user,
         updated_at  = CURRENT_TIMESTAMP
   WHERE id_producto = p_id_producto
     AND id_productor = p_id_productor;

  RETURN TRUE;
END;
$$ LANGUAGE plpgsql;

-- PRUEBAS
-- SELECT fun_ins_producto_productor(1,123456789,150000,20,'Desc X','img_x.jpg',TRUE);
-- SELECT fun_upd_producto_productor(1,123456789,155000,18,'Desc Y','img_y.jpg',TRUE);