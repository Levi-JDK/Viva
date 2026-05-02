-- Add PuntoEnvio tracking number to invoice header.
ALTER TABLE tab_enc_fact ADD COLUMN IF NOT EXISTS num_guia VARCHAR;
COMMENT ON COLUMN tab_enc_fact.num_guia IS 'PuntoEnvio package ID (tracking/guía number)';
