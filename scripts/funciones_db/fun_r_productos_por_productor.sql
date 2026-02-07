-- SELECT fun_listar_productos_por_productor();
CREATE OR REPLACE FUNCTION fun_r_productos_por_productor(
  p_id_productor tab_productores.id_productor%TYPE DEFAULT NULL
) RETURNS BOOLEAN AS
$$
DECLARE
  wcur_prod REFCURSOR;
  wreg_prod RECORD;
  wcur_pp   REFCURSOR;
  wreg_pp   RECORD;
  wq_prod   TEXT;
  wq_pp     TEXT;
BEGIN
  -- Productores (filtra por p_id_productor si se envía)
  wq_prod = '
    SELECT pr.id_productor, pr.nom_prod, pr.ape_prod
    FROM tab_productores pr
    WHERE pr.is_deleted = FALSE ' ||
    CASE WHEN p_id_productor IS NULL
         THEN ''
         ELSE format(' AND pr.id_productor = %L ', p_id_productor)
    END ||
    ' ORDER BY pr.id_productor ';

  OPEN wcur_prod FOR EXECUTE wq_prod;
  FETCH wcur_prod INTO wreg_prod;
  WHILE FOUND LOOP
    RAISE NOTICE 'Productor %: % %',
      wreg_prod.id_productor, wreg_prod.nom_prod, wreg_prod.ape_prod;

    -- Productos del productor con stock y precio
    wq_pp := format($q$
      SELECT pp.id_producto,
             p.nom_producto,
             pp.stock_prod,
             pp.precio_prod,
             pp.activo
      FROM tab_producto_productor pp
      JOIN tab_productos p ON p.id_producto = pp.id_producto
      WHERE pp.is_deleted = FALSE
        AND p.is_deleted  = FALSE
        AND pp.id_productor = %L
      ORDER BY pp.id_producto
    $q$, wreg_prod.id_productor);

    OPEN wcur_pp FOR EXECUTE wq_pp;
    FETCH wcur_pp INTO wreg_pp;
    IF NOT FOUND THEN
      RAISE NOTICE '    (sin productos asociados)';
    END IF;

    WHILE FOUND LOOP
      RAISE NOTICE '    Prod %: % | Stock: % | Precio: % | Activo: %',
        wreg_pp.id_producto,
        wreg_pp.nom_producto,
        wreg_pp.stock_prod,
        wreg_pp.precio_prod,
        wreg_pp.activo;
      FETCH wcur_pp INTO wreg_pp;
    END LOOP;
    CLOSE wcur_pp;

    FETCH wcur_prod INTO wreg_prod;
  END LOOP;
  CLOSE wcur_prod;

  RETURN TRUE;
END;
$$
LANGUAGE plpgsql;
