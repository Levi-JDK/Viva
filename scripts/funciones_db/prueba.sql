SELECT * FROM tab_users;
SELECT * FROM tab_ciudades;
SELECT fun_ins_cliente(
  12,          -- id_user existente
  1,          -- tipo_doc (CC)
  '1004912',  -- id_client (VARCHAR)
  1,          -- id_pais (Colombia)
  2,          -- id_ciudad (Bogotá)
  'es',       -- id_idioma
  'COP'       -- id_moneda
);
SELECT cl.id_client,c.nom_ciudad FROM tab_clientes cl, tab_ciudades c 
WHERE c.id_ciudad = cl.id_ciudad 
AND id_user = 12;
SELECT * FROM tab_clientes WHERE id_user = 12;
SELECT fun_upd_cliente( 12,          -- id_user existente
  1,          -- tipo_doc 
  '1004912',  -- id_client 
  1,          -- id_pais 
  1,          -- id_ciudad 
  'es',       -- id_idioma
  'COP'       -- id_moneda
  );
SELECT p.id_producto, p.nom_producto AS Nombre_Producto, pr.id_productor AS Identificacion, pr.nom_prod || ' ' || pr.ape_prod AS Productor, pp.stock_prod AS Stock
FROM tab_producto_productor AS pp
JOIN tab_productos  AS p  ON p.id_producto   = pp.id_producto   AND p.is_deleted  = FALSE
JOIN tab_productores AS pr ON pr.id_productor = pp.id_productor AND pr.is_deleted = FALSE
WHERE pp.is_deleted = FALSE
  AND pp.activo     = TRUE
ORDER BY p.nom_producto, pr.nom_prod, pr.ape_prod;

SELECT fun_facturar(
  '1004912',               -- p_id_client
  'TRF',                  -- p_id_pago
  ARRAY[1,2],             -- p_ids_producto
  ARRAY[123456789,987654321], -- p_ids_productor
  ARRAY[1,2]              -- p_cantidades
);

SELECT * FROM tab_enc_fact WHERE id_factura = 1000;
SELECT * FROM tab_det_fact WHERE id_factura = 1000;

SELECT fun_kardex_mov(1, 123456789, 10, TRUE);

SELECT fun_listar_facturas();
SELECT fun_listar_productos_por_productor();
