-- =============================================================================
-- fun_c_det_fact.sql (v6 — fix definitivo del overflow de id_productor)
-- El problema era que tab_kardex.id_productor era DECIMAL(5,0) y el ID real
-- del productor superaba 99999. Ahora ambos son DECIMAL(10,0).
-- =============================================================================
CREATE OR REPLACE FUNCTION fun_c_det_fact(
    p_id_factura  tab_det_fact.id_factura%TYPE,
    p_id_producto tab_det_fact.id_producto%TYPE,
    p_cantidad    tab_det_fact.val_cantidad%TYPE
) RETURNS BOOLEAN AS $$
DECLARE
    w_id_productor tab_det_fact.id_productor%TYPE;  -- DECIMAL(10,0)
    w_precio       NUMERIC;
    w_stock        NUMERIC;
    w_val_total    NUMERIC;
BEGIN
    IF p_id_factura  IS NULL THEN RETURN FALSE; END IF;
    IF p_id_producto IS NULL THEN RETURN FALSE; END IF;
    IF p_cantidad IS NULL OR p_cantidad < 1 THEN RETURN FALSE; END IF;

    -- Factura vigente
    PERFORM 1 FROM tab_enc_fact
    WHERE id_factura = p_id_factura AND is_deleted = FALSE AND ind_estado = TRUE;
    IF NOT FOUND THEN
        RAISE WARNING '[fun_c_det_fact] Factura % no válida', p_id_factura;
        RETURN FALSE;
    END IF;

    -- Datos del producto
    SELECT id_productor, precio_producto, stock_productor
      INTO w_id_productor, w_precio, w_stock
    FROM tab_productos
    WHERE id_producto = p_id_producto
      AND is_deleted  = FALSE
      AND is_active   = TRUE;

    IF NOT FOUND THEN
        RAISE WARNING '[fun_c_det_fact] Producto % no encontrado o inactivo', p_id_producto;
        RETURN FALSE;
    END IF;

    IF p_cantidad > w_stock THEN
        RAISE WARNING '[fun_c_det_fact] Stock insuficiente: disp=%, sol=%', w_stock, p_cantidad;
        RETURN FALSE;
    END IF;

    w_val_total := w_precio * p_cantidad;

    -- Insertar línea de detalle
    INSERT INTO tab_det_fact (id_factura, id_producto, id_productor, val_cantidad, val_bruto, val_neto)
    VALUES (p_id_factura, p_id_producto, w_id_productor,
            p_cantidad, w_val_total, w_val_total);

    -- Acumular total en encabezado
    UPDATE tab_enc_fact
       SET val_tot_fact = val_tot_fact + w_val_total
     WHERE id_factura   = p_id_factura;

    -- Registrar salida en kardex. El trigger fun_trg_kardex_stock es la única fuente
    -- de descuento de stock para evitar doble deducción.
    INSERT INTO tab_kardex (id_producto, id_productor, tipo_movim, cantidad, ref_factura)
    VALUES (p_id_producto, w_id_productor, FALSE, p_cantidad, p_id_factura);

    RETURN TRUE;

EXCEPTION WHEN OTHERS THEN
    RAISE WARNING '[fun_c_det_fact] %', SQLERRM;
    RETURN FALSE;
END;
$$ LANGUAGE plpgsql;
