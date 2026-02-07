CREATE OR REPLACE FUNCTION fun_u_cliente(
  p_id_user         tab_clientes.id_user%TYPE,
  p_tipo_doc_client tab_clientes.tipo_doc_client%TYPE,
  p_id_client       tab_clientes.id_client%TYPE,
  p_id_pais         tab_clientes.id_pais%TYPE,
  p_id_ciudad       tab_clientes.id_ciudad%TYPE,
  p_id_idioma       tab_clientes.id_idioma%TYPE,
  p_id_moneda       tab_clientes.id_moneda%TYPE
) RETURNS BOOLEAN AS $$
DECLARE
  w_old_id_client tab_clientes.id_client%TYPE;
  w_cnt           integer;
BEGIN
  -- Reglas de presencia
  IF p_id_user IS NULL OR p_tipo_doc_client IS NULL OR p_id_client IS NULL
     OR btrim(p_id_client) = '' OR p_id_pais IS NULL OR p_id_ciudad IS NULL
     OR p_id_idioma IS NULL OR btrim(p_id_idioma) = '' 
     OR p_id_moneda IS NULL OR btrim(p_id_moneda) = '' THEN
     -- Validacion: Parámetros obligatorios nulos o vacíos
     RETURN FALSE;
  END IF;

  -- User válido
  PERFORM 1 FROM tab_users u WHERE u.id_user = p_id_user AND u.is_deleted = FALSE;
  IF NOT FOUND THEN
    -- Validacion: Usuario no existe o está eliminado
    RETURN FALSE;
  END IF;

  -- Ubicar único cliente activo por user
  SELECT COUNT(*) INTO w_cnt
  FROM tab_clientes c
  WHERE c.id_user = p_id_user AND c.is_deleted = FALSE;
  IF w_cnt = 0 THEN
    -- Validacion: El usuario no tiene cliente activo que actualizar
    RETURN FALSE;
  ELSIF w_cnt > 1 THEN
    -- Validacion: El usuario tiene múltiples clientes activos; no se puede actualizar por id_user
    RETURN FALSE;
  END IF;

  SELECT c.id_client
    INTO w_old_id_client
  FROM tab_clientes c
  WHERE c.id_user = p_id_user AND c.is_deleted = FALSE
  LIMIT 1;

  -- Catálogos válidos
  PERFORM 1 FROM tab_tipos_doc td WHERE td.id_tipo_doc = p_tipo_doc_client AND td.is_deleted = FALSE;
  -- Validacion: Tipo de documento no válido
  IF NOT FOUND THEN RETURN FALSE; END IF;

  PERFORM 1 FROM tab_pais_tipos_doc ptd
   WHERE ptd.id_pais = p_id_pais AND ptd.id_tipo_doc = p_tipo_doc_client AND ptd.is_deleted = FALSE;
  -- Validacion: Tipo doc no permitido para país
  IF NOT FOUND THEN RETURN FALSE; END IF;

  PERFORM 1 FROM tab_ciudades ci WHERE ci.id_ciudad = p_id_ciudad AND ci.id_pais = p_id_pais AND ci.is_deleted = FALSE;
  -- Validacion: La ciudad no pertenece al país o está eliminada
  IF NOT FOUND THEN RETURN FALSE; END IF;

  PERFORM 1 FROM tab_idiomas i WHERE i.id_idioma = p_id_idioma AND i.is_deleted = FALSE;
  -- Validacion: Idioma no válido
  IF NOT FOUND THEN RETURN FALSE; END IF;

  PERFORM 1 FROM tab_monedas m WHERE m.id_moneda = p_id_moneda AND m.is_deleted = FALSE;
  -- Validacion: Moneda no válida
  IF NOT FOUND THEN RETURN FALSE; END IF;

  -- Longitud razonable para id_client (evitar basura)
  IF length(btrim(p_id_client)) < 2 OR length(btrim(p_id_client)) > 30 THEN
    -- Validacion: id_client longitud inválida (2..30)
    RETURN FALSE;
  END IF;

  -- Si cambió el id_client, validar que no esté tomado por otro
  IF btrim(p_id_client) <> w_old_id_client THEN
    PERFORM 1 FROM tab_clientes c WHERE c.id_client = btrim(p_id_client);
    -- Validacion: id_client ya existe
    IF FOUND THEN RETURN FALSE; END IF;
  END IF;

  -- Update
  UPDATE tab_clientes
     SET tipo_doc_client = p_tipo_doc_client,
         id_client       = btrim(p_id_client),
         id_pais         = p_id_pais,
         id_ciudad       = p_id_ciudad,
         id_idioma       = p_id_idioma,
         id_moneda       = p_id_moneda,
         updated_by      = current_user,
         updated_at      = CURRENT_TIMESTAMP
   WHERE id_user = p_id_user
     AND is_deleted = FALSE;

  IF NOT FOUND THEN
    -- Error: No se actualizó ningún registro para usuario
    RETURN FALSE;
  END IF;

  RETURN TRUE;
END;
$$ LANGUAGE plpgsql;

-- SELECT fun_upd_cliente(8,4, 'AA12345678 ',2, 41,'en','USD');
--SELECT * FROM tab_clientes WHERE id_user = 8;
-- SELECT
--   u.nom_user,
--   u.ape_user,
--   td.nom_tipo_doc,
--   cli.id_client,
--   p.nom_pais,
--   ciu.nom_ciudad,
--   m.nom_moneda
-- FROM tab_clientes AS cli
-- JOIN tab_users        AS u   ON u.id_user = cli.id_user
--                              AND u.is_deleted   = FALSE
-- JOIN tab_paises       AS p   ON p.id_pais = cli.id_pais
--                              AND p.is_deleted   = FALSE
-- JOIN tab_tipos_doc    AS td  ON td.id_tipo_doc = cli.tipo_doc_client
--                              AND td.is_deleted  = FALSE
-- JOIN tab_ciudades     AS ciu ON ciu.id_ciudad = cli.id_ciudad
--                              AND ciu.id_pais   = cli.id_pais
--                              AND ciu.is_deleted = FALSE
-- JOIN tab_monedas      AS m   ON m.id_moneda = cli.id_moneda
--                              AND m.is_deleted   = FALSE
-- WHERE cli.is_deleted = FALSE
--   AND cli.id_user = 8;


