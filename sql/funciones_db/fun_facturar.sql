-- =============================================================================
-- fun_facturar.sql  (v4 — sin fun_carrito, el controlador PHP limpia el carrito)
-- =============================================================================
CREATE OR REPLACE FUNCTION fun_facturar(
    p_id_user        tab_clientes.id_user%TYPE,
    p_id_pago        tab_enc_fact.id_pago%TYPE,
    p_id_dpto        tab_enc_fact.id_departamento%TYPE,
    p_id_ciudad      tab_enc_fact.id_ciudad%TYPE,
    p_dir_envio      tab_enc_fact.dir_envio%TYPE,
    p_epayco_ref     tab_enc_fact.epayco_ref%TYPE,
    p_epayco_txn     tab_enc_fact.epayco_txn_id%TYPE,
    p_epayco_estado  tab_enc_fact.epayco_estado%TYPE,
    p_ids_producto   INTEGER[],
    p_cantidades     INTEGER[]
) RETURNS tab_enc_fact.id_factura%TYPE AS $$
DECLARE
    w_id_client tab_clientes.id_client%TYPE;
    w_factura   tab_enc_fact.id_factura%TYPE;
    w_len       INT;
    w_ok_items  INT := 0;
BEGIN
    -- Validar arrays
    IF p_ids_producto IS NULL OR array_length(p_ids_producto, 1) IS NULL THEN
        RAISE WARNING '[fun_facturar] Array productos vacío'; RETURN NULL;
    END IF;
    IF p_cantidades IS NULL OR array_length(p_cantidades, 1) IS NULL THEN
        RAISE WARNING '[fun_facturar] Array cantidades vacío'; RETURN NULL;
    END IF;
    IF array_length(p_ids_producto, 1) <> array_length(p_cantidades, 1) THEN
        RAISE WARNING '[fun_facturar] Arrays de distinto tamaño'; RETURN NULL;
    END IF;

    w_len := array_length(p_ids_producto, 1);

    -- Obtener id_client actual del usuario
    SELECT id_client INTO w_id_client
    FROM tab_clientes
    WHERE id_user = p_id_user AND is_deleted = FALSE;

    IF NOT FOUND THEN
        RAISE WARNING '[fun_facturar] Cliente no encontrado para id_user=%', p_id_user;
        RETURN NULL;
    END IF;

    -- 1. Encabezado (total=0, se acumula con cada detalle)
    w_factura := fun_c_enc_fact(
        w_id_client, p_id_pago,
        p_id_dpto, p_id_ciudad, p_dir_envio,
        0,
        p_epayco_ref, p_epayco_txn, p_epayco_estado
    );

    IF w_factura IS NULL THEN
        RAISE WARNING '[fun_facturar] fun_c_enc_fact devolvió NULL'; RETURN NULL;
    END IF;

    -- 2. Detalles + kardex (INSERT directo, trigger maneja stock)
    FOR i IN 1..w_len LOOP
        IF (SELECT fun_c_det_fact(
                w_factura,
                p_ids_producto[i],
                p_cantidades[i]
            )) THEN
            w_ok_items := w_ok_items + 1;
        ELSE
            RAISE WARNING '[fun_facturar] Detalle fallido: producto=%', p_ids_producto[i];
        END IF;
    END LOOP;

    -- Sin detalles → anular encabezado
    IF w_ok_items = 0 THEN
        RAISE WARNING '[fun_facturar] Ningún detalle válido, anulando factura %', w_factura;
        UPDATE tab_enc_fact SET ind_estado = FALSE WHERE id_factura = w_factura;
        RETURN NULL;
    END IF;

    -- Nota: la limpieza del carrito la hace el controlador PHP después de recibir este valor
    RETURN w_factura;

EXCEPTION WHEN OTHERS THEN
    RAISE WARNING '[fun_facturar] %: %', SQLSTATE, SQLERRM;
    IF w_factura IS NOT NULL AND w_ok_items = 0 THEN
        UPDATE tab_enc_fact SET ind_estado = FALSE WHERE id_factura = w_factura;
    END IF;
    RETURN NULL;
END;
$$ LANGUAGE plpgsql;
