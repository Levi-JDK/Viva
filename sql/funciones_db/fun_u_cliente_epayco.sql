-- =============================================================================
-- fun_u_cliente_epayco.sql
-- Actualiza tab_clientes con los datos del cliente que entrega ePayco
-- tras confirmar el pago (documento, teléfono, banco, referencia, etc.).
--
-- Retorna: TRUE si se actualizó al menos 1 fila, FALSE si el cliente no existe.
-- =============================================================================
CREATE OR REPLACE FUNCTION fun_u_cliente_epayco(
    p_id_user         tab_clientes.id_user%TYPE,
    p_id_client       tab_clientes.id_client%TYPE,    -- x_customer_document
    p_id_tipo_doc     tab_clientes.id_tipo_doc%TYPE,  -- 1=CC 2=NIT 3=CE 4=PP
    p_tel_client      tab_clientes.tel_client%TYPE,   -- x_customer_phone
    p_epayco_ref      tab_clientes.epayco_ref%TYPE,   -- x_ref_payco
    p_epayco_txn_id   tab_clientes.epayco_txn_id%TYPE,-- x_transaction_id
    p_epayco_banco    tab_clientes.epayco_banco%TYPE,  -- x_bank_name
    p_epayco_cod_resp tab_clientes.epayco_cod_resp%TYPE-- x_cod_response
) RETURNS BOOLEAN AS $$
DECLARE
    w_rows INTEGER;
BEGIN
    IF p_id_user IS NULL THEN RETURN FALSE; END IF;

    UPDATE tab_clientes SET
        nro_doc         = COALESCE(NULLIF(TRIM(p_id_client), ''), nro_doc),
        id_tipo_doc     = COALESCE(p_id_tipo_doc, id_tipo_doc),
        tel_client      = COALESCE(NULLIF(TRIM(p_tel_client), ''), tel_client),
        epayco_ref      = p_epayco_ref,
        epayco_txn_id   = p_epayco_txn_id,
        epayco_banco    = p_epayco_banco,
        epayco_cod_resp = p_epayco_cod_resp,
        updated_at      = NOW()
    WHERE id_user = p_id_user
      AND is_deleted = FALSE;

    GET DIAGNOSTICS w_rows = ROW_COUNT;
    RETURN w_rows > 0;

EXCEPTION WHEN OTHERS THEN
    RAISE WARNING '[fun_u_cliente_epayco] %', SQLERRM;
    RETURN FALSE;
END;
$$ LANGUAGE plpgsql;
