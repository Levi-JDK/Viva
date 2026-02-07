-- SELECT fun_listar_facturas();
CREATE OR REPLACE FUNCTION fun_r_facturas()
RETURNS BOOLEAN AS
$$
DECLARE
  wcur_enc  REFCURSOR;
  wreg_enc  RECORD;
  wcur_det  REFCURSOR;
  wreg_det  RECORD;
  wq_enc    TEXT;
  wq_det    TEXT;
BEGIN
  -- Encabezados de facturas con datos del cliente (usuario)
  wq_enc := '
    SELECT  e.id_factura,
            e.fec_factura,
            e.val_hora_fact,
            e.id_client,
            u.nom_user,
            u.ape_user,
            e.id_pais,
            e.id_ciudad,
            e.val_tot_fact
    FROM tab_enc_fact e
    JOIN tab_clientes  c ON c.id_client = e.id_client AND c.is_deleted = FALSE
    JOIN tab_users     u ON u.id_user   = c.id_user   AND u.is_deleted = FALSE
    WHERE e.is_deleted = FALSE
    ORDER BY e.id_factura
  ';

  OPEN wcur_enc FOR EXECUTE wq_enc;
  FETCH wcur_enc INTO wreg_enc;
  WHILE FOUND LOOP
    RAISE NOTICE 'Factura #%, Cliente: % %, Fecha: % %, Total: %',
      wreg_enc.id_factura, wreg_enc.nom_user, wreg_enc.ape_user,
      wreg_enc.fec_factura, wreg_enc.val_hora_fact, wreg_enc.val_tot_fact;

    -- Detalles de la factura
    wq_det := format($q$
      SELECT d.id_producto,
             p.nom_producto,
             d.id_productor,
             pr.nom_prod,
             pr.ape_prod,
             d.val_cantidad,
             d.val_bruto,
             d.val_neto
      FROM tab_det_fact d
      JOIN tab_productos   p  ON p.id_producto  = d.id_producto  AND p.is_deleted  = FALSE
      JOIN tab_productores pr ON pr.id_productor = d.id_productor AND pr.is_deleted = FALSE
      WHERE d.is_deleted = FALSE
        AND d.id_factura = %L
      ORDER BY d.id_producto
    $q$, wreg_enc.id_factura);

    OPEN wcur_det FOR EXECUTE wq_det;
    FETCH wcur_det INTO wreg_det;
    WHILE FOUND LOOP
      RAISE NOTICE '    Prod(%:%) % x%  -> Bruto: %, Neto: %  (Productor: % %:%)',
        wreg_det.id_producto,
        wreg_det.id_productor,
        wreg_det.nom_producto,
        wreg_det.val_cantidad,
        wreg_det.val_bruto,
        wreg_det.val_neto,
        wreg_det.nom_prod, wreg_det.ape_prod, wreg_det.id_productor;

      FETCH wcur_det INTO wreg_det;
    END LOOP;
    CLOSE wcur_det;

    FETCH wcur_enc INTO wreg_enc;
  END LOOP;
  CLOSE wcur_enc;

  RETURN TRUE;
END;
$$
LANGUAGE plpgsql;
