CREATE OR REPLACE FUNCTION fun_c_producto_productor(
  p_id_producto  tab_producto_productor.id_producto%TYPE,
  p_id_productor tab_producto_productor.id_productor%TYPE,
  p_precio       tab_producto_productor.precio_prod%TYPE,
  p_stock        tab_producto_productor.stock_prod%TYPE,
  p_desc         tab_producto_productor.desc_prod_personal%TYPE,
  p_img          tab_producto_productor.img_personal%TYPE,
  p_activo       tab_producto_productor.activo%TYPE
) RETURNS BOOLEAN AS $$
BEGIN
  -- Validacion: id_producto requerido
  IF p_id_producto  IS NULL THEN RETURN FALSE; END IF;
  -- Validacion: id_productor requerido
  IF p_id_productor IS NULL THEN RETURN FALSE; END IF;
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

  PERFORM 1 FROM tab_producto_productor pp WHERE pp.id_producto = p_id_producto AND pp.id_productor = p_id_productor;
  -- Validacion: Relación producto / productor ya existe
  IF FOUND THEN RETURN FALSE; END IF;

  PERFORM 1 FROM tab_productos t  WHERE t.id_producto  = p_id_producto  AND t.is_deleted  = FALSE;
  -- Validacion: Producto no válido
  IF NOT FOUND THEN RETURN FALSE; END IF;

  PERFORM 1 FROM tab_productores pr WHERE pr.id_productor = p_id_productor AND pr.is_deleted = FALSE;
  -- Validacion: Productor no válido
  IF NOT FOUND THEN RETURN FALSE; END IF;

  -- Regla de activo-stock
  IF (p_stock = 0 AND p_activo) OR (p_stock > 0 AND NOT p_activo) THEN
    -- Validacion: Regla de estado inválida: stock=0 => activo=FALSE; stock>0 => activo=TRUE
    RETURN FALSE;
  END IF;

  INSERT INTO tab_producto_productor(id_producto,id_productor,precio_prod,stock_prod,desc_prod_personal,img_personal,activo)
  VALUES (p_id_producto,p_id_productor,p_precio,p_stock,p_desc,p_img,p_activo);

  RETURN TRUE;
END;
$$ LANGUAGE plpgsql;