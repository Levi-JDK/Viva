-- =============================================================================
-- fun_c_cliente.sql
-- Inserta o actualiza la dirección de envío de un cliente en tab_clientes.
-- Se usa desde el checkout al guardar/editar la dirección antes de pagar.
--
-- Retorna: TRUE si ok, FALSE si falla validación básica.
-- =============================================================================
CREATE OR REPLACE FUNCTION fun_c_cliente(
    p_id_user         tab_clientes.id_user%TYPE,
    p_id_client       tab_clientes.id_client%TYPE,
    p_nom_client      tab_clientes.nom_client%TYPE,
    p_mail_client     tab_clientes.mail_client%TYPE,
    p_id_departamento tab_clientes.id_departamento%TYPE,
    p_id_ciudad       tab_clientes.id_ciudad%TYPE,
    p_dir_envio       tab_clientes.dir_envio%TYPE,
    p_barrio_envio    tab_clientes.barrio_envio%TYPE
) RETURNS BOOLEAN AS $$
BEGIN
    -- Validaciones básicas de campos obligatorios
    IF p_id_user IS NULL                                THEN RETURN FALSE; END IF;
    IF p_nom_client  IS NULL OR TRIM(p_nom_client)  = '' THEN RETURN FALSE; END IF;
    IF p_mail_client IS NULL OR TRIM(p_mail_client) = '' THEN RETURN FALSE; END IF;
    IF p_id_departamento IS NULL                        THEN RETURN FALSE; END IF;
    IF p_id_ciudad IS NULL                              THEN RETURN FALSE; END IF;
    IF p_dir_envio IS NULL OR LENGTH(TRIM(p_dir_envio)) < 5 THEN RETURN FALSE; END IF;

    -- UPSERT por id_user (un usuario = una dirección de envío principal)
    -- id_client se usa como placeholder del doc hasta que ePayco lo entregue
    INSERT INTO tab_clientes
        (id_user, id_client, nom_client, mail_client,
         id_pais, id_departamento, id_ciudad, dir_envio, barrio_envio)
    VALUES
        (p_id_user,
         COALESCE(NULLIF(TRIM(p_id_client), ''), p_id_user::VARCHAR),
         p_nom_client,
         p_mail_client,
         1,
         p_id_departamento,
         p_id_ciudad,
         TRIM(p_dir_envio),
         NULLIF(TRIM(COALESCE(p_barrio_envio, '')), ''))
    ON CONFLICT (id_client) DO UPDATE SET
        id_departamento = EXCLUDED.id_departamento,
        id_ciudad       = EXCLUDED.id_ciudad,
        dir_envio       = EXCLUDED.dir_envio,
        barrio_envio    = EXCLUDED.barrio_envio,
        updated_at      = NOW();

    RETURN TRUE;

EXCEPTION WHEN OTHERS THEN
    RAISE WARNING '[fun_c_cliente] %', SQLERRM;
    RETURN FALSE;
END;
$$ LANGUAGE plpgsql;
