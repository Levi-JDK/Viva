-- =============================================================================
-- fun_kardex_mov.sql  (simplificada — el trigger maneja el stock)
-- Solo valida + registra el movimiento en tab_kardex.
-- El trigger trg_kardex_actualizar_stock se encarga de mover el stock.
--
-- p_tipo_movim: TRUE=entrada, FALSE=salida (venta)
-- p_ref_factura: opcional, para trazabilidad con la factura
-- Retorna: TRUE si ok, FALSE si falla validación.
-- =============================================================================
CREATE OR REPLACE FUNCTION fun_kardex_mov(
    p_id_producto  tab_kardex.id_producto%TYPE,
    p_id_productor tab_kardex.id_productor%TYPE,
    p_cantidad     tab_kardex.cantidad%TYPE,
    p_tipo_movim   tab_kardex.tipo_movim%TYPE,
    p_ref_factura  tab_kardex.ref_factura%TYPE DEFAULT NULL
) RETURNS BOOLEAN AS $$
DECLARE
    w_stock tab_productos.stock_productor%TYPE;
BEGIN
    IF p_id_producto  IS NULL THEN RETURN FALSE; END IF;
    IF p_id_productor IS NULL THEN RETURN FALSE; END IF;
    IF p_cantidad IS NULL OR p_cantidad < 1 THEN RETURN FALSE; END IF;
    IF p_tipo_movim   IS NULL THEN RETURN FALSE; END IF;

    -- Solo para salidas: verificar stock suficiente antes de insertar
    IF p_tipo_movim = FALSE THEN
        SELECT stock_productor INTO w_stock
        FROM tab_productos
        WHERE id_producto  = p_id_producto
          AND id_productor = p_id_productor
          AND is_deleted   = FALSE
          AND is_active    = TRUE;

        IF NOT FOUND THEN
            RAISE WARNING '[fun_kardex_mov] Producto %/Productor % no encontrado', p_id_producto, p_id_productor;
            RETURN FALSE;
        END IF;

        IF p_cantidad > w_stock THEN
            RAISE WARNING '[fun_kardex_mov] Stock insuficiente (disp: %, sol: %)', w_stock, p_cantidad;
            RETURN FALSE;
        END IF;
    END IF;

    -- Insert: el TRIGGER trg_kardex_actualizar_stock actualiza tab_productos automáticamente
    INSERT INTO tab_kardex (id_producto, id_productor, tipo_movim, cantidad, ref_factura)
    VALUES (p_id_producto, p_id_productor, p_tipo_movim, p_cantidad, p_ref_factura);

    RETURN TRUE;

EXCEPTION WHEN OTHERS THEN
    RAISE WARNING '[fun_kardex_mov] %', SQLERRM;
    RETURN FALSE;
END;
$$ LANGUAGE plpgsql;
