-- Creacion de las funciones de borrado logico para cada tabla
CREATE OR REPLACE FUNCTION fun_softdel_tab_pmtros(
  p_id tab_pmtros.id_parametro%TYPE,
  p_deleted tab_pmtros.is_deleted%TYPE
) RETURNS BOOLEAN AS $$
DECLARE w_id tab_pmtros.id_parametro%TYPE;
BEGIN
  SELECT id_parametro INTO w_id FROM tab_pmtros WHERE id_parametro = p_id;
  IF NOT FOUND THEN
    RAISE NOTICE 'No existe pmtros %', p_id;
    RETURN FALSE;
  ELSE
    UPDATE tab_pmtros SET is_deleted = p_deleted WHERE id_parametro = p_id;
    RETURN TRUE;
  END IF;
END; $$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION fun_softdel_tab_users(
  p_id tab_users.id_user%TYPE,
  p_deleted tab_users.is_deleted%TYPE
) RETURNS BOOLEAN AS $$
DECLARE w_id tab_users.id_user%TYPE;
BEGIN
  SELECT id_user INTO w_id FROM tab_users WHERE id_user = p_id;
  IF NOT FOUND THEN
    RAISE NOTICE 'No existe user %', p_id;
    RETURN FALSE;
  ELSE
    UPDATE tab_users SET is_deleted = p_deleted WHERE id_user = p_id;
    RETURN TRUE;
  END IF;
END; $$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION fun_softdel_tab_menu(
  p_id tab_menu.id_menu%TYPE,
  p_deleted tab_menu.is_deleted%TYPE
) RETURNS BOOLEAN AS $$
DECLARE w_id tab_menu.id_menu%TYPE;
BEGIN
  SELECT id_menu INTO w_id FROM tab_menu WHERE id_menu = p_id;
  IF NOT FOUND THEN
    RAISE NOTICE 'No existe menu %', p_id;
    RETURN FALSE;
  ELSE
    UPDATE tab_menu SET is_deleted = p_deleted WHERE id_menu = p_id;
    RETURN TRUE;
  END IF;
END; $$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION fun_softdel_tab_bancos(
  p_id tab_bancos.id_banco%TYPE,
  p_deleted tab_bancos.is_deleted%TYPE
) RETURNS BOOLEAN AS $$
DECLARE w_id tab_bancos.id_banco%TYPE;
BEGIN
  SELECT id_banco INTO w_id FROM tab_bancos WHERE id_banco = p_id;
  IF NOT FOUND THEN
    RAISE NOTICE 'No existe banco %', p_id;
    RETURN FALSE;
  ELSE
    UPDATE tab_bancos SET is_deleted = p_deleted WHERE id_banco = p_id;
    RETURN TRUE;
  END IF;
END; $$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION fun_softdel_tab_color(
  p_id tab_color.id_color%TYPE,
  p_deleted tab_color.is_deleted%TYPE
) RETURNS BOOLEAN AS $$
DECLARE w_id tab_color.id_color%TYPE;
BEGIN
  SELECT id_color INTO w_id FROM tab_color WHERE id_color = p_id;
  IF NOT FOUND THEN
    RAISE NOTICE 'No existe color %', p_id;
    RETURN FALSE;
  ELSE
    UPDATE tab_color SET is_deleted = p_deleted WHERE id_color = p_id;
    RETURN TRUE;
  END IF;
END; $$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION fun_softdel_tab_tipos_doc(
  p_id tab_tipos_doc.id_tipo_doc%TYPE,
  p_deleted tab_tipos_doc.is_deleted%TYPE
) RETURNS BOOLEAN AS $$
DECLARE w_id tab_tipos_doc.id_tipo_doc%TYPE;
BEGIN
  SELECT id_tipo_doc INTO w_id FROM tab_tipos_doc WHERE id_tipo_doc = p_id;
  IF NOT FOUND THEN
    RAISE NOTICE 'No existe tipo_doc %', p_id;
    RETURN FALSE;
  ELSE
    UPDATE tab_tipos_doc SET is_deleted = p_deleted WHERE id_tipo_doc = p_id;
    RETURN TRUE;
  END IF;
END; $$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION fun_softdel_tab_paises(
  p_id tab_paises.id_pais%TYPE,
  p_deleted tab_paises.is_deleted%TYPE
) RETURNS BOOLEAN AS $$
DECLARE w_id tab_paises.id_pais%TYPE;
BEGIN
  SELECT id_pais INTO w_id FROM tab_paises WHERE id_pais = p_id;
  IF NOT FOUND THEN
    RAISE NOTICE 'No existe pais %', p_id;
    RETURN FALSE;
  ELSE
    UPDATE tab_paises SET is_deleted = p_deleted WHERE id_pais = p_id;
    RETURN TRUE;
  END IF;
END; $$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION fun_softdel_tab_grupos(
  p_id tab_grupos.id_grupo%TYPE,
  p_deleted tab_grupos.is_deleted%TYPE
) RETURNS BOOLEAN AS $$
DECLARE w_id tab_grupos.id_grupo%TYPE;
BEGIN
  SELECT id_grupo INTO w_id FROM tab_grupos WHERE id_grupo = p_id;
  IF NOT FOUND THEN
    RAISE NOTICE 'No existe grupo %', p_id;
    RETURN FALSE;
  ELSE
    UPDATE tab_grupos SET is_deleted = p_deleted WHERE id_grupo = p_id;
    RETURN TRUE;
  END IF;
END; $$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION fun_softdel_tab_regiones(
  p_id tab_regiones.id_region%TYPE,
  p_deleted tab_regiones.is_deleted%TYPE
) RETURNS BOOLEAN AS $$
DECLARE w_id tab_regiones.id_region%TYPE;
BEGIN
  SELECT id_region INTO w_id FROM tab_regiones WHERE id_region = p_id;
  IF NOT FOUND THEN
    RAISE NOTICE 'No existe region %', p_id;
    RETURN FALSE;
  ELSE
    UPDATE tab_regiones SET is_deleted = p_deleted WHERE id_region = p_id;
    RETURN TRUE;
  END IF;
END; $$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION fun_softdel_tab_productores(
  p_id tab_productores.id_productor%TYPE,
  p_deleted tab_productores.is_deleted%TYPE
) RETURNS BOOLEAN AS $$
DECLARE w_id tab_productores.id_productor%TYPE;
BEGIN
  SELECT id_productor INTO w_id FROM tab_productores WHERE id_productor = p_id;
  IF NOT FOUND THEN
    RAISE NOTICE 'No existe productor %', p_id;
    RETURN FALSE;
  ELSE
    UPDATE tab_productores SET is_deleted = p_deleted WHERE id_productor = p_id;
    RETURN TRUE;
  END IF;
END; $$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION fun_softdel_tab_idiomas(
  p_id tab_idiomas.id_idioma%TYPE,
  p_deleted tab_idiomas.is_deleted%TYPE
) RETURNS BOOLEAN AS $$
DECLARE w_id tab_idiomas.id_idioma%TYPE;
BEGIN
  SELECT id_idioma INTO w_id FROM tab_idiomas WHERE id_idioma = p_id;
  IF NOT FOUND THEN
    RAISE NOTICE 'No existe idioma %', p_id;
    RETURN FALSE;
  ELSE
    UPDATE tab_idiomas SET is_deleted = p_deleted WHERE id_idioma = p_id;
    RETURN TRUE;
  END IF;
END; $$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION fun_softdel_tab_monedas(
  p_id tab_monedas.id_moneda%TYPE,
  p_deleted tab_monedas.is_deleted%TYPE
) RETURNS BOOLEAN AS $$
DECLARE w_id tab_monedas.id_moneda%TYPE;
BEGIN
  SELECT id_moneda INTO w_id FROM tab_monedas WHERE id_moneda = p_id;
  IF NOT FOUND THEN
    RAISE NOTICE 'No existe moneda %', p_id;
    RETURN FALSE;
  ELSE
    UPDATE tab_monedas SET is_deleted = p_deleted WHERE id_moneda = p_id;
    RETURN TRUE;
  END IF;
END; $$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION fun_softdel_tab_clientes(
  p_id tab_clientes.id_client%TYPE,
  p_deleted tab_clientes.is_deleted%TYPE
) RETURNS BOOLEAN AS $$
DECLARE w_id tab_clientes.id_client%TYPE;
BEGIN
  SELECT id_client INTO w_id FROM tab_clientes WHERE id_client = p_id;
  IF NOT FOUND THEN
    RAISE NOTICE 'No existe cliente %', p_id;
    RETURN FALSE;
  ELSE
    UPDATE tab_clientes SET is_deleted = p_deleted WHERE id_client = p_id;
    RETURN TRUE;
  END IF;
END; $$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION fun_softdel_tab_oficios(
  p_id tab_oficios.id_oficio%TYPE,
  p_deleted tab_oficios.is_deleted%TYPE
) RETURNS BOOLEAN AS $$
DECLARE w_id tab_oficios.id_oficio%TYPE;
BEGIN
  SELECT id_oficio INTO w_id FROM tab_oficios WHERE id_oficio = p_id;
  IF NOT FOUND THEN
    RAISE NOTICE 'No existe oficio %', p_id;
    RETURN FALSE;
  ELSE
    UPDATE tab_oficios SET is_deleted = p_deleted WHERE id_oficio = p_id;
    RETURN TRUE;
  END IF;
END; $$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION fun_softdel_tab_materia_prima(
  p_id tab_materia_prima.id_materia%TYPE,
  p_deleted tab_materia_prima.is_deleted%TYPE
) RETURNS BOOLEAN AS $$
DECLARE w_id tab_materia_prima.id_materia%TYPE;
BEGIN
  SELECT id_materia INTO w_id FROM tab_materia_prima WHERE id_materia = p_id;
  IF NOT FOUND THEN
    RAISE NOTICE 'No existe materia_prima %', p_id;
    RETURN FALSE;
  ELSE
    UPDATE tab_materia_prima SET is_deleted = p_deleted WHERE id_materia = p_id;
    RETURN TRUE;
  END IF;
END; $$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION fun_softdel_tab_categorias(
  p_id tab_categorias.id_categoria%TYPE,
  p_deleted tab_categorias.is_deleted%TYPE
) RETURNS BOOLEAN AS $$
DECLARE w_id tab_categorias.id_categoria%TYPE;
BEGIN
  SELECT id_categoria INTO w_id FROM tab_categorias WHERE id_categoria = p_id;
  IF NOT FOUND THEN
    RAISE NOTICE 'No existe categoria %', p_id;
    RETURN FALSE;
  ELSE
    UPDATE tab_categorias SET is_deleted = p_deleted WHERE id_categoria = p_id;
    RETURN TRUE;
  END IF;
END; $$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION fun_softdel_tab_productos(
  p_id tab_productos.id_producto%TYPE,
  p_deleted tab_productos.is_deleted%TYPE
) RETURNS BOOLEAN AS $$
DECLARE w_id tab_productos.id_producto%TYPE;
BEGIN
  SELECT id_producto INTO w_id FROM tab_productos WHERE id_producto = p_id;
  IF NOT FOUND THEN
    RAISE NOTICE 'No existe producto %', p_id;
    RETURN FALSE;
  ELSE
    UPDATE tab_productos SET is_deleted = p_deleted WHERE id_producto = p_id;
    RETURN TRUE;
  END IF;
END; $$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION fun_softdel_tab_transportadoras(
  p_id tab_transportadoras.id_transportador%TYPE,
  p_deleted tab_transportadoras.is_deleted%TYPE
) RETURNS BOOLEAN AS $$
DECLARE w_id tab_transportadoras.id_transportador%TYPE;
BEGIN
  SELECT id_transportador INTO w_id FROM tab_transportadoras WHERE id_transportador = p_id;
  IF NOT FOUND THEN
    RAISE NOTICE 'No existe transportador %', p_id;
    RETURN FALSE;
  ELSE
    UPDATE tab_transportadoras SET is_deleted = p_deleted WHERE id_transportador = p_id;
    RETURN TRUE;
  END IF;
END; $$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION fun_softdel_tab_formas_pago(
  p_id tab_formas_pago.id_pago%TYPE,
  p_deleted tab_formas_pago.is_deleted%TYPE
) RETURNS BOOLEAN AS $$
DECLARE w_id tab_formas_pago.id_pago%TYPE;
BEGIN
  SELECT id_pago INTO w_id FROM tab_formas_pago WHERE id_pago = p_id;
  IF NOT FOUND THEN
    RAISE NOTICE 'No existe forma_pago %', p_id;
    RETURN FALSE;
  ELSE
    UPDATE tab_formas_pago SET is_deleted = p_deleted WHERE id_pago = p_id;
    RETURN TRUE;
  END IF;
END; $$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION fun_softdel_tab_transito(
  p_id tab_transito.id_entrada%TYPE,
  p_deleted tab_transito.is_deleted%TYPE
) RETURNS BOOLEAN AS $$
DECLARE w_id tab_transito.id_entrada%TYPE;
BEGIN
  SELECT id_entrada INTO w_id FROM tab_transito WHERE id_entrada = p_id;
  IF NOT FOUND THEN
    RAISE NOTICE 'No existe transito %', p_id;
    RETURN FALSE;
  ELSE
    UPDATE tab_transito SET is_deleted = p_deleted WHERE id_entrada = p_id;
    RETURN TRUE;
  END IF;
END; $$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION fun_softdel_tab_enc_fact(
  p_id tab_enc_fact.id_factura%TYPE,
  p_deleted tab_enc_fact.is_deleted%TYPE
) RETURNS BOOLEAN AS $$
DECLARE w_id tab_enc_fact.id_factura%TYPE;
BEGIN
  SELECT id_factura INTO w_id FROM tab_enc_fact WHERE id_factura = p_id;
  IF NOT FOUND THEN
    RAISE NOTICE 'No existe factura %', p_id;
    RETURN FALSE;
  ELSE
    UPDATE tab_enc_fact SET is_deleted = p_deleted WHERE id_factura = p_id;
    RETURN TRUE;
  END IF;
END; $$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION fun_softdel_tab_envios(
  p_id tab_envios.id_envio%TYPE,
  p_deleted tab_envios.is_deleted%TYPE
) RETURNS BOOLEAN AS $$
DECLARE w_id tab_envios.id_envio%TYPE;
BEGIN
  SELECT id_envio INTO w_id FROM tab_envios WHERE id_envio = p_id;
  IF NOT FOUND THEN
    RAISE NOTICE 'No existe envio %', p_id;
    RETURN FALSE;
  ELSE
    UPDATE tab_envios SET is_deleted = p_deleted WHERE id_envio = p_id;
    RETURN TRUE;
  END IF;
END; $$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION fun_softdel_tab_kardex(
  p_id tab_kardex.id_kardex%TYPE,
  p_deleted tab_kardex.is_deleted%TYPE
) RETURNS BOOLEAN AS $$
DECLARE w_id tab_kardex.id_kardex%TYPE;
BEGIN
  SELECT id_kardex INTO w_id FROM tab_kardex WHERE id_kardex = p_id;
  IF NOT FOUND THEN
    RAISE NOTICE 'No existe kardex %', p_id;
    RETURN FALSE;
  ELSE
    UPDATE tab_kardex SET is_deleted = p_deleted WHERE id_kardex = p_id;
    RETURN TRUE;
  END IF;
END; $$ LANGUAGE plpgsql;


-- ===================== PK COMPUESTA =====================

CREATE OR REPLACE FUNCTION fun_softdel_tab_menu_user(
  p_id_user tab_menu_user.id_user%TYPE,
  p_id_menu tab_menu_user.id_menu%TYPE,
  p_deleted tab_menu_user.is_deleted%TYPE
) RETURNS BOOLEAN AS $$
DECLARE w_u tab_menu_user.id_user%TYPE;
BEGIN
  SELECT id_user INTO w_u
    FROM tab_menu_user
   WHERE id_user = p_id_user AND id_menu = p_id_menu;
  IF NOT FOUND THEN
    RAISE NOTICE 'No existe menu_user (user %, menu %)', p_id_user, p_id_menu;
    RETURN FALSE;
  ELSE
    UPDATE tab_menu_user
       SET is_deleted = p_deleted
     WHERE id_user = p_id_user AND id_menu = p_id_menu;
    RETURN TRUE;
  END IF;
END; $$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION fun_softdel_tab_ciudades(
  p_id_ciudad tab_ciudades.id_ciudad%TYPE,
  p_id_pais   tab_ciudades.id_pais%TYPE,
  p_deleted   tab_ciudades.is_deleted%TYPE
) RETURNS BOOLEAN AS $$
DECLARE w_c tab_ciudades.id_ciudad%TYPE;
BEGIN
  SELECT id_ciudad INTO w_c
    FROM tab_ciudades
   WHERE id_ciudad = p_id_ciudad AND id_pais = p_id_pais;
  IF NOT FOUND THEN
    RAISE NOTICE 'No existe ciudad (ciudad %, pais %)', p_id_ciudad, p_id_pais;
    RETURN FALSE;
  ELSE
    UPDATE tab_ciudades
       SET is_deleted = p_deleted
     WHERE id_ciudad = p_id_ciudad AND id_pais = p_id_pais;
    RETURN TRUE;
  END IF;
END; $$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION fun_softdel_tab_pais_tipos_doc(
  p_id_pais     tab_pais_tipos_doc.id_pais%TYPE,
  p_id_tipo_doc tab_pais_tipos_doc.id_tipo_doc%TYPE,
  p_deleted     tab_pais_tipos_doc.is_deleted%TYPE
) RETURNS BOOLEAN AS $$
DECLARE w_p tab_pais_tipos_doc.id_pais%TYPE;
BEGIN
  SELECT id_pais INTO w_p
    FROM tab_pais_tipos_doc
   WHERE id_pais = p_id_pais AND id_tipo_doc = p_id_tipo_doc;
  IF NOT FOUND THEN
    RAISE NOTICE 'No existe pais_tipo_doc (pais %, tipo_doc %)', p_id_pais, p_id_tipo_doc;
    RETURN FALSE;
  ELSE
    UPDATE tab_pais_tipos_doc
       SET is_deleted = p_deleted
     WHERE id_pais = p_id_pais AND id_tipo_doc = p_id_tipo_doc;
    RETURN TRUE;
  END IF;
END; $$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION fun_softdel_tab_producto_productor(
  p_id_producto  tab_producto_productor.id_producto%TYPE,
  p_id_productor tab_producto_productor.id_productor%TYPE,
  p_deleted      tab_producto_productor.is_deleted%TYPE
) RETURNS BOOLEAN AS $$
DECLARE w_p tab_producto_productor.id_producto%TYPE;
BEGIN
  SELECT id_producto INTO w_p
    FROM tab_producto_productor
   WHERE id_producto = p_id_producto AND id_productor = p_id_productor;
  IF NOT FOUND THEN
    RAISE NOTICE 'No existe producto_productor (producto %, productor %)', p_id_producto, p_id_productor;
    RETURN FALSE;
  ELSE
    UPDATE tab_producto_productor
       SET is_deleted = p_deleted
     WHERE id_producto = p_id_producto AND id_productor = p_id_productor;
    RETURN TRUE;
  END IF;
END; $$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION fun_softdel_tab_det_fact(
  p_id_factura  tab_det_fact.id_factura%TYPE,
  p_id_producto tab_det_fact.id_producto%TYPE,
  p_deleted     tab_det_fact.is_deleted%TYPE
) RETURNS BOOLEAN AS $$
DECLARE w_f tab_det_fact.id_factura%TYPE;
BEGIN
  SELECT id_factura INTO w_f
    FROM tab_det_fact
   WHERE id_factura = p_id_factura AND id_producto = p_id_producto;
  IF NOT FOUND THEN
    RAISE NOTICE 'No existe det_fact (factura %, producto %)', p_id_factura, p_id_producto;
    RETURN FALSE;
  ELSE
    UPDATE tab_det_fact
       SET is_deleted = p_deleted
     WHERE id_factura = p_id_factura AND id_producto = p_id_producto;
    RETURN TRUE;
  END IF;
END; $$ LANGUAGE plpgsql;
