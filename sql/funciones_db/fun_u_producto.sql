CREATE OR REPLACE FUNCTION fun_u_producto(
  p_id_producto  tab_productos.id_producto%TYPE,
  p_nom_producto tab_productos.nom_producto%TYPE,
  p_stock        tab_productos.stock_productor%TYPE,
  p_id_categoria tab_productos.id_categoria%TYPE,
  p_id_color     tab_productos.id_color%TYPE,
  p_id_oficio    tab_productos.id_oficio%TYPE,
  p_id_materia   tab_productos.id_materia%TYPE
) RETURNS BOOLEAN AS $$
BEGIN
  -- Validacion: id_producto requerido
  IF p_id_producto IS NULL THEN RETURN FALSE; END IF;
  PERFORM 1 FROM tab_productos t WHERE t.id_producto = p_id_producto;
  -- Validacion: Producto no existe
  IF NOT FOUND THEN RETURN FALSE; END IF;

--Validaciones
  -- Validacion: nom_producto requerido
  IF p_nom_producto IS NULL OR length(btrim(p_nom_producto))=0 THEN RETURN FALSE; END IF;
  -- Validacion: stock inválido
  IF p_stock IS NULL OR p_stock < 0 THEN RETURN FALSE; END IF;
  -- Validacion: id_categoria requerido
  IF p_id_categoria IS NULL THEN RETURN FALSE; END IF;
  -- Validacion: id_color requerido
  IF p_id_color IS NULL OR length(btrim(p_id_color))=0 THEN RETURN FALSE; END IF;
  -- Validacion: id_oficio requerido
  IF p_id_oficio IS NULL THEN RETURN FALSE; END IF;
  -- Validacion: id_materia requerido
  IF p_id_materia IS NULL THEN RETURN FALSE; END IF;

  PERFORM 1 FROM tab_categorias c WHERE c.id_categoria = p_id_categoria AND c.is_deleted = FALSE;
  -- Validacion: Categoría no válida
  IF NOT FOUND THEN RETURN FALSE; END IF;

  PERFORM 1 FROM tab_color co WHERE co.id_color = p_id_color AND co.is_deleted = FALSE;
  -- Validacion: Color no válido
  IF NOT FOUND THEN RETURN FALSE; END IF;

  PERFORM 1 FROM tab_oficios o WHERE o.id_oficio = p_id_oficio AND o.is_deleted = FALSE;
  -- Validacion: Oficio no válido
  IF NOT FOUND THEN RETURN FALSE; END IF;

  PERFORM 1 FROM tab_materia_prima m WHERE m.id_materia = p_id_materia AND m.is_deleted = FALSE;
  -- Validacion: Materia no válida
  IF NOT FOUND THEN RETURN FALSE; END IF;

  UPDATE tab_productos
     SET nom_producto = p_nom_producto,
         stock_productor = p_stock,
         is_active    = (p_stock > 0),
         id_categoria = p_id_categoria,
         id_color     = p_id_color,
         id_oficio    = p_id_oficio,
         id_materia   = p_id_materia
   WHERE id_producto  = p_id_producto;

  RETURN TRUE;
END;
$$ LANGUAGE plpgsql;

-- PRUEBAS
-- SELECT fun_c_producto(101,'Tapete Fique',5,2,'#008000',1,1);
-- SELECT fun_u_producto(1,'Mochila Wayúu Premium',40,1,'#FF0000',2,1);