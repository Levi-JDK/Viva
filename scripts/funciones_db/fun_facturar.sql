
CREATE OR REPLACE FUNCTION fun_facturar(
  p_id_client      tab_clientes.id_client%TYPE,
  p_id_pago        tab_enc_fact.id_pago%TYPE,
  p_ids_producto   INTEGER[],   -- ids de tab_productos
  p_ids_productor  INTEGER[],   -- ids de tab_productores
  p_cantidades     INTEGER[]    -- cantidades (enteras positivas)
) RETURNS BOOLEAN AS $$
DECLARE
  w_id_user   tab_clientes.id_user%TYPE;
  w_factura   tab_enc_fact.id_factura%TYPE;
  w_len       INT;
  w_ok_items  INT := 0;
BEGIN
  -- Cliente válido (y no eliminado)
  SELECT c.id_user INTO w_id_user
  FROM tab_clientes c
  WHERE c.id_client = p_id_client
    AND c.is_deleted = FALSE;
  IF NOT FOUND THEN
    -- Validacion: Cliente no existe o está eliminado
    RETURN FALSE;
  END IF;

  -- Validaciones de arrays
  IF p_ids_producto  IS NULL OR array_length(p_ids_producto,1)  IS NULL THEN
    -- Validacion: Lista de productos vacía
    RETURN FALSE;
  END IF;
  IF p_ids_productor IS NULL OR array_length(p_ids_productor,1) IS NULL THEN
    -- Validacion: Lista de productores vacía
    RETURN FALSE;
  END IF;
  IF p_cantidades    IS NULL OR array_length(p_cantidades,1)    IS NULL THEN
    -- Validacion: Lista de cantidades vacía
    RETURN FALSE;
  END IF;

  IF array_length(p_ids_producto,1) <> array_length(p_ids_productor,1)
     OR array_length(p_ids_producto,1) <> array_length(p_cantidades,1) THEN
    -- Validacion: Las listas no tienen la misma longitud
    RETURN FALSE;
  END IF;

  w_len := array_length(p_ids_producto,1);

  -- Encabezado
  w_factura := fun_ins_enc_fact(p_id_client, p_id_pago);
  IF w_factura IS NULL THEN
    -- Error: No fue posible crear el encabezado
    RETURN FALSE;
  END IF;

  -- Iterar detalles
  FOR i IN 1..w_len LOOP
    IF (SELECT fun_ins_det_fact(
              w_factura,
              p_ids_producto[i]::NUMERIC,
              p_ids_productor[i]::NUMERIC,
              p_cantidades[i]::NUMERIC
        )) IS TRUE
    THEN
      w_ok_items := w_ok_items + 1;
    ELSE
      -- Warning: Detalle no insertado
      NULL;
    END IF;
  END LOOP;

  -- Validar que haya al menos un detalle
  IF w_ok_items = 0 THEN
    -- Error: Ningún detalle válido: se revierte la operación
    RAISE EXCEPTION USING ERRCODE='P0001';
  END IF;

  RETURN TRUE;

EXCEPTION
  WHEN SQLSTATE 'P0001' THEN
    -- Si quieres anular el encabezado en un futuro, podrías marcar ind_estado = FALSE aquí.
    RETURN FALSE;
END;
$$ LANGUAGE plpgsql;
