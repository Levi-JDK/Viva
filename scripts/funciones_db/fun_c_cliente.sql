CREATE OR REPLACE FUNCTION fun_c_cliente(
  p_id_user     tab_clientes.id_user%TYPE,
  p_tipo_doc    tab_clientes.tipo_doc_client%TYPE,
  p_id_client   tab_clientes.id_client%TYPE,
  p_id_pais     tab_clientes.id_pais%TYPE,
  p_id_ciudad   tab_clientes.id_ciudad%TYPE,
  p_id_idioma   tab_clientes.id_idioma%TYPE,
  p_id_moneda   tab_clientes.id_moneda%TYPE
) RETURNS BOOLEAN AS $$
BEGIN
  -- Requeridos
  -- Validacion: id_user requerido
  IF p_id_user   IS NULL THEN RETURN FALSE; END IF;
  -- Validacion: tipo_doc requerido
  IF p_tipo_doc  IS NULL THEN RETURN FALSE; END IF;
  -- Validacion: id_client requerido
  IF p_id_client IS NULL THEN RETURN FALSE; END IF;
  -- Validacion: id_pais requerido
  IF p_id_pais   IS NULL THEN RETURN FALSE; END IF;
  -- Validacion: id_ciudad requerido
  IF p_id_ciudad IS NULL THEN RETURN FALSE; END IF;
  -- Validacion: id_idioma requerido
  IF p_id_idioma IS NULL THEN RETURN FALSE; END IF;
  -- Validacion: id_moneda requerido
  IF p_id_moneda IS NULL THEN RETURN FALSE; END IF;

  -- Unicidad del cliente
  PERFORM 1 FROM tab_clientes c WHERE c.id_client = p_id_client;
  IF FOUND THEN
    -- Validacion: Cliente ya existe
    RETURN FALSE;
  END IF;

  -- Usuario válido (no eliminado)
  PERFORM 1 FROM tab_users u WHERE u.id_user = p_id_user AND u.is_deleted = FALSE;
  IF NOT FOUND THEN
    -- Validacion: Usuario no existe o está eliminado
    RETURN FALSE;
  END IF;

  -- Tipo doc permitido en país
  PERFORM 1
    FROM tab_pais_tipos_doc ptd
   WHERE ptd.id_pais = p_id_pais
     AND ptd.id_tipo_doc = p_tipo_doc
     AND ptd.is_deleted = FALSE;
  IF NOT FOUND THEN
    -- Validacion: Tipo de documento no permitido para país
    RETURN FALSE;
  END IF;

  -- Ciudad pertenece al país (y no eliminada)
  PERFORM 1
    FROM tab_ciudades c
   WHERE c.id_ciudad = p_id_ciudad
     AND c.id_pais   = p_id_pais
     AND c.is_deleted = FALSE;
  IF NOT FOUND THEN
    -- Validacion: Ciudad no pertenece al país o está eliminada
    RETURN FALSE;
  END IF;

  -- Idioma válido
  PERFORM 1 FROM tab_idiomas i WHERE i.id_idioma = p_id_idioma AND i.is_deleted = FALSE;
  IF NOT FOUND THEN
    -- Validacion: Idioma no válido
    RETURN FALSE;
  END IF;

  -- Moneda válida
  PERFORM 1 FROM tab_monedas m WHERE m.id_moneda = p_id_moneda AND m.is_deleted = FALSE;
  IF NOT FOUND THEN
    -- Validacion: Moneda no válida
    RETURN FALSE;
  END IF;

  -- Insert (created_by/created_at/updated_* los manejan triggers o defaults)
  INSERT INTO tab_clientes(
    id_user, tipo_doc_client, id_client, id_pais, id_ciudad, id_idioma, id_moneda
  ) VALUES (
    p_id_user, p_tipo_doc, p_id_client, p_id_pais, p_id_ciudad, p_id_idioma, p_id_moneda
  );

  RETURN TRUE;
END;
$$ LANGUAGE plpgsql;

-- SELECT fun_ins_cliente(8,4, 'AA12345678 ',2, 4,'en','USD');
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