<?php
/**
 * Mapa de consultas SQL para Database.
 *
 * Cada entrada es 'nombre' => "SQL string".
 * Los prepares se hacen lazy en Database::ejecutar().
 */

return [
    'validarEmail' => "SELECT fun_val_mail(:email)",
    'crearUsuario' => "SELECT fun_c_user(:email, :contrasena, :nombre, :apellido)",
    'obtenerHashLogin' => "SELECT fun_val_log(:email)",
    'obtenerUsuarioPorEmail' => "SELECT id_user, nom_user FROM tab_users WHERE mail_user = :email",
    'obtenerUsuarioPorId' => "SELECT nom_user, ape_user, mail_user, foto_user, created_at FROM tab_users WHERE id_user = :id",
    'actualizarPerfil' => "UPDATE tab_users SET nom_user = :nombre, ape_user = :apellido WHERE id_user = :id",
    'obtenerIdPorEmail' => "SELECT id_user FROM tab_users WHERE mail_user = :email",
    'actualizarFotoUsuario' => "SELECT fun_u_foto_user(:id, :foto) as resultado",
    'obtenerTiposDocumento' => "SELECT id, nombre FROM tipos_col_view",
    'obtenerDepartamentos' => "SELECT id, nombre FROM departamentos_col_view",
    'obtenerCiudades' => "SELECT id, nombre FROM  obtener_ciudades(:id_depto) ORDER BY nombre ASC;",
    'obtenerGrupos' => "SELECT id, nombre FROM grupos_view",
    'obtenerBancos' => "SELECT id, nombre FROM bancos_view",
    'crearProductor' => "SELECT fun_c_productor(:tipo_doc, :id_prod, :id_user, :dir, :pais, :dpto, :ciudad, :grupo, :banco, :cuenta, :tipo_cuenta)",
    'validarProductor' => "SELECT fun_val_productor(:id_user)",
    'obtenerCategorias' => "SELECT id_categoria, nom_categoria FROM categorias_view",
    'obtenerColores' => "SELECT id_color, nom_color FROM colores_view",
    'obtenerOficios' => "SELECT id_oficio, nom_oficio FROM oficios_view",
    'obtenerMaterias' => "SELECT id_materia, nom_materia FROM materias_view",
    'obtenerIdProductor' => "SELECT id_productor FROM tab_productores WHERE id_user = :id_user",
    'eliminarProductoLogicamente' => "
        UPDATE tab_productos 
        SET is_deleted = TRUE, is_active = FALSE, stock_productor = 0 
        WHERE id_producto = :id_producto AND id_productor = :id_productor
    ",
    'registrarProducto' => "
        SELECT fun_c_producto(
            :id_productor, :nom_producto, :stock_productor, 
            :id_categoria, :id_color, :id_oficio, :id_materia, 
            :precio_producto, :descripcion_producto, :is_active
        )
    ",
    'registrarImagen' => "
        SELECT fun_c_imagen(:id_producto, :url_imagen)
    ",
    'obtenerProductos' => "
        SELECT 
            id_producto,
            nom_producto,
            precio_producto,
            stock_productor,
            nom_categoria,
            activo,
            vistas,
            imagenes
        FROM fun_obtener_productos(:id_productor)
    ",
    'incrementarVistasProducto' => "
        SELECT fun_u_vista_producto(:id_producto) AS resultado
    ",
    'obtenerVistasProducto' => "
        SELECT vistas
        FROM tab_productos
        WHERE id_producto = :id_producto AND is_deleted = FALSE
        LIMIT 1
    ",
    'obtenerProductoPorId' => "
        SELECT 
            id_producto, nom_producto, precio_producto, stock_productor, 
            descripcion_producto, id_categoria, nom_categoria, id_oficio, nom_oficio, 
            id_materia, nom_materia, id_color, nom_color, id_productor, 
            nom_productor, ubicacion, imagenes 
        FROM fun_obtener_producto_por_id(:id_producto)
    ",
    'registrarStand' => "
        SELECT fun_c_stand(
            :id_productor, :nom_stand, :slogan_stand, 
            :descripcion_stand, :img_stand, :portada_stand
        )
    ",
    'verificarStand' => "SELECT id_stand FROM tab_stand WHERE id_productor = :id_p",
    'obtenerStand' => "
        SELECT id_productor, id_stand, nom_stand, slogan_stand, descripcion_stand, img_stand, portada_stand 
        FROM tab_stand WHERE id_stand = :id_s
    ",
    'obtenerStandPrivado' => "
        SELECT id_productor, id_stand, nom_stand, slogan_stand, descripcion_stand, img_stand, portada_stand 
        FROM tab_stand WHERE id_productor = :id_p
    ",
    'obtenerIdStandPorUser' => "
        SELECT s.id_stand 
        FROM tab_stand s
        INNER JOIN tab_productores p ON s.id_productor = p.id_productor
        WHERE p.id_user = :id_user AND s.is_deleted = FALSE
        LIMIT 1
    ",
    'actualizarStand' => "
        SELECT fun_u_stand(
            :id_productor, :id_stand, :nom_stand, :slogan_stand, 
            :descripcion_stand, :img_stand, :portada_stand
        )
    ",
    'obtenerConfiguracionGlobal' => "
        SELECT 
            nom_plataforma, dir_contacto, correo_contacto,
            val_inifact, val_finfact, val_actfact, val_observa,
            foto_hero, landing_hero_titulo, landing_hero_subtitulo, landing_hero_btn,
            landing_conf_1_tit, landing_conf_1_sub, landing_conf_2_tit, landing_conf_2_sub,
            landing_conf_3_tit, landing_conf_3_sub, landing_filosofia_tit, landing_filosofia_p1, landing_filosofia_p2
        FROM tab_pmtros 
        WHERE id_parametro = 1 AND is_deleted = FALSE 
        LIMIT 1
    ",
    'actualizarParametrosGlob' => "
        SELECT fun_u_parametros(
            :id_parametro, :nom_plataforma, :dir_contacto, :correo_contacto,
            :val_inifact, :val_finfact, :val_actfact, :val_observa, :foto_hero,
            :landing_hero_titulo, :landing_hero_subtitulo, :landing_hero_btn,
            :landing_conf_1_tit, :landing_conf_1_sub, :landing_conf_2_tit, :landing_conf_2_sub,
            :landing_conf_3_tit, :landing_conf_3_sub, :landing_filosofia_tit, :landing_filosofia_p1, :landing_filosofia_p2
        )
    ",
    'obtenerProductosDestacados' => "
        SELECT 
            p.id_producto,
            p.id_productor,
            p.nom_producto,
            p.precio_producto,
            p.descripcion_producto,
            s.id_stand,
            s.nom_stand,
            s.img_stand,
            (SELECT url_imagen FROM tab_imagenes WHERE id_producto = p.id_producto ORDER BY id_imagen LIMIT 1) as primera_imagen
        FROM tab_productos p
        LEFT JOIN tab_stand s ON p.id_productor = s.id_productor
        WHERE p.is_deleted = FALSE AND p.is_active = TRUE
        ORDER BY p.created_at ASC
        LIMIT :limit
    ",
    'obtenerMenuPublico' => "
        SELECT id_menu, nom_menu, url_menu, icono_menu 
        FROM tab_menu 
        WHERE id_menu IN (1, 2, 3) 
        ORDER BY id_menu ASC
    ",
    'obtenerNavegacionUsuario' => "
        SELECT id_menu, nom_menu, url_menu, icono_menu 
        FROM fun_obtener_navegacion_usuario(:id_user)
    ",
    'asignarMenuUsuario' => "
        SELECT fun_asignar_menu(:id_user, :id_menu)
    ",
    'revocarMenuUsuario' => "
        SELECT fun_revocar_menu(:id_user, :id_menu)
    ",
    'crearResetToken' => "
        SELECT fun_c_reset_token(:mail_user, :minutos)
    ",
    'validarResetToken' => "
        SELECT fun_v_reset_token(:mail_user, :token_reset)
    ",
    'actualizarPassword' => "
        SELECT fun_u_password(:id_user, :pass_user)
    ",
    'obtenerProductosCatalogo' => "
        SELECT 
            p.id_producto,
            p.id_productor,
            p.nom_producto,
            p.precio_producto,
            p.descripcion_producto,
            s.id_stand,
            s.nom_stand,
            s.img_stand,
            (SELECT url_imagen FROM tab_imagenes WHERE id_producto = p.id_producto ORDER BY id_imagen LIMIT 1) as primera_imagen
        FROM tab_productos p
        LEFT JOIN tab_stand s ON p.id_productor = s.id_productor
        WHERE p.is_deleted = FALSE AND p.is_active = TRUE
        ORDER BY p.created_at DESC
    ",
    'obtenerDetalleProducto' => "
        SELECT id_producto, nom_producto, precio_producto, descripcion_producto, stock_productor, 
               is_active, id_categoria, nom_categoria, id_color, nom_color, id_oficio, nom_oficio, 
               id_materia, nom_materia, id_productor, nom_productor, id_stand, nom_stand, img_stand, 
               slogan_stand, descripcion_stand, portada_stand, ubicacion, imagenes, foto_user
        FROM fun_obtener_detalle_producto(:id_producto)
    ",
    'obtenerStandsActivos' => "
        SELECT id_productor, id_stand, nom_stand, slogan_stand, descripcion_stand, img_stand, portada_stand
        FROM tab_stand
        WHERE is_deleted = FALSE
        ORDER BY nom_stand ASC
    ",
    'obtenerStandsDestacados' => "
        SELECT id_productor, id_stand, nom_stand, slogan_stand, descripcion_stand, img_stand, portada_stand
        FROM tab_stand
        WHERE is_deleted = FALSE
        ORDER BY RANDOM()
        LIMIT :limit
    ",
    'buscarStandsActivos' => "
        SELECT id_productor, id_stand, nom_stand, slogan_stand, descripcion_stand, img_stand, portada_stand
        FROM tab_stand
        WHERE is_deleted = FALSE AND (nom_stand ILIKE :search OR descripcion_stand ILIKE :search)
        ORDER BY nom_stand ASC
    ",
    'obtenerFiltrosCategorias' => "
        SELECT c.id_categoria, c.nom_categoria, c.img_cat, COUNT(p.id_producto) as total
        FROM categorias_view c
        INNER JOIN tab_productos p ON p.id_categoria = c.id_categoria
        WHERE p.is_deleted = FALSE AND p.is_active = TRUE
        GROUP BY c.id_categoria, c.nom_categoria, c.img_cat
        ORDER BY c.nom_categoria ASC
    ",
    'obtenerFiltrosOficios' => "
        SELECT o.id_oficio, o.nom_oficio, COUNT(p.id_producto) as total
        FROM oficios_view o
        INNER JOIN tab_productos p ON p.id_oficio = o.id_oficio
        WHERE p.is_deleted = FALSE AND p.is_active = TRUE
        GROUP BY o.id_oficio, o.nom_oficio
        ORDER BY o.nom_oficio ASC
    ",
    'obtenerFiltrosMaterias' => "
        SELECT m.id_materia, m.nom_materia, COUNT(p.id_producto) as total
        FROM materias_view m
        INNER JOIN tab_productos p ON p.id_materia = m.id_materia
        WHERE p.is_deleted = FALSE AND p.is_active = TRUE
        GROUP BY m.id_materia, m.nom_materia
        ORDER BY m.nom_materia ASC
    ",
    'gestionarCarrito' => "SELECT fun_carrito(:id_user, :accion, :id_producto, :cantidad)",
    'agregarFavorito' => "SELECT fun_c_favorito(:id_user, :id_producto)",
    'eliminarFavorito' => "SELECT fun_d_favoritos(:id_user, :id_producto)",
    'eliminarResena' => "DELETE FROM tab_resenas WHERE id_user = :id_user AND id_producto = :id_producto",
    'obtenerPedidosCliente' => "
        SELECT 
            f.id_factura,
            f.fec_factura,
            f.val_tot_fact,
            f.epayco_estado,
            p.nom_pago,
            COALESCE(
                (SELECT sum(val_cantidad) FROM tab_det_fact WHERE id_factura = f.id_factura), 0
            ) as total_productos,
            (
                SELECT url_imagen 
                FROM tab_imagenes i 
                JOIN tab_det_fact d ON i.id_producto = d.id_producto 
                WHERE d.id_factura = f.id_factura 
                ORDER BY i.id_imagen ASC LIMIT 1
            ) as primera_imagen
        FROM tab_enc_fact f
        JOIN tab_clientes c ON f.id_client = c.id_client
        JOIN tab_formas_pago p ON f.id_pago = p.id_pago
        WHERE c.id_user = :id_user
        ORDER BY f.fec_factura DESC, f.val_hora_fact DESC
    ",
    'obtenerFavoritosUsuario' => "
        SELECT 
            p.id_producto, p.id_productor, p.nom_producto, p.precio_producto, p.descripcion_producto, 
            s.id_stand, s.nom_stand, s.img_stand, 
            (SELECT url_imagen FROM tab_imagenes WHERE id_producto = p.id_producto ORDER BY id_imagen LIMIT 1) as primera_imagen
        FROM tab_favoritos f
        INNER JOIN tab_productos p ON f.id_producto = p.id_producto
        LEFT JOIN tab_stand s ON p.id_productor = s.id_productor
        WHERE f.id_user = :id_user AND f.is_deleted = FALSE AND p.is_deleted = FALSE AND p.is_active = TRUE
        ORDER BY f.created_at DESC
    ",
    'agregarResena' => "
        SELECT fun_c_resena(:id_user, :id_producto, :calificacion, :texto)
    ",
    'obtenerResenasProducto' => "
        SELECT 
            r.calificacion, 
            r.texto_resena, 
            r.created_at,
            u.nom_user, 
            u.ape_user, 
            u.foto_user
        FROM 
            tab_resenas r, 
            tab_users u
        WHERE 
            r.id_user = u.id_user 
            AND r.id_producto = :id_producto 
            AND r.is_deleted = FALSE
        ORDER BY 
            r.created_at DESC
    ",
    'obtenerPromedioEstrellasProducto' => "
        SELECT 
            COALESCE(AVG(calificacion), 0) as promedio,
            COUNT(id_producto) as total_resenas
        FROM tab_resenas
        WHERE id_producto = :id_producto AND is_deleted = FALSE
    ",
    'obtenerPromedioEstrellasStand' => "
        SELECT 
            COALESCE(AVG(r.calificacion), 0) as promedio,
            COUNT(r.id_producto) as total_resenas
        FROM tab_resenas r
        INNER JOIN tab_productos p ON r.id_producto = p.id_producto
        INNER JOIN tab_stand s ON p.id_productor = s.id_productor
        WHERE s.id_stand = :id_stand AND r.is_deleted = FALSE
    ",
    'guardarCliente' => "SELECT fun_c_cliente(:id_user, :id_client, :nom, :mail, :dpto, :ciudad, :dir, :barrio)",
    'actualizarClienteEpayco' => "SELECT fun_u_cliente_epayco(:id_user, :id_client, :id_tipo_doc, :tel, :ref, :txn, :banco, :cod_resp)",
    'obtenerDireccionCliente' => "SELECT id_departamento, id_ciudad, dir_envio, barrio_envio
           FROM tab_clientes WHERE id_user = :id_user AND is_deleted = FALSE LIMIT 1",
    'facturar' => "SELECT fun_facturar(
            :id_user, :id_pago,
            :dpto, :ciudad, :dir,
            :epayco_ref, :epayco_txn, :epayco_estado,
            :ids_producto::INTEGER[],
            :cantidades::INTEGER[]
        )",
    'obtenerUltimoIdProducto' => "
        SELECT MAX(id_producto) FROM tab_productos
    ",
    'verificarPropiedadProducto' => "
        SELECT id_producto FROM tab_productos 
        WHERE id_producto = :id_p AND id_productor = :id_prod
    ",
    'obtenerImagenesProducto' => "
        SELECT id_imagen, url_imagen FROM tab_imagenes WHERE id_producto = :id
    ",
    'actualizarDescripcionPrecio' => "
        UPDATE tab_productos 
        SET descripcion_producto = :desc, precio_producto = :precio 
        WHERE id_producto = :id
    ",
    'eliminarImagen' => "
        DELETE FROM tab_imagenes WHERE id_imagen = :id
    ",
    'obtenerNombreUsuarioPorEmail' => "
        SELECT nom_user FROM tab_users 
        WHERE mail_user = :email AND is_deleted = FALSE 
        LIMIT 1
    ",
    'actualizarProducto' => "
        SELECT fun_u_producto(
            :id_producto, :nom_producto, :stock, :id_categoria, :id_color, :id_oficio, :id_materia
        )
    ",
    'obtenerFacturaPorId' => "
        SELECT 
            f.id_factura, f.fec_factura, f.val_hora_fact, f.val_tot_fact, f.epayco_estado,
            f.epayco_ref, f.epayco_txn_id, f.dir_envio, p.nom_pago,
            dep.nom_departamento, ciu.nom_ciudad
        FROM tab_enc_fact f
        JOIN tab_clientes c ON f.id_client = c.id_client
        JOIN tab_formas_pago p ON f.id_pago = p.id_pago
        LEFT JOIN tab_departamentos dep ON f.id_pais = dep.id_pais AND f.id_departamento = dep.id_departamento
        LEFT JOIN tab_ciudades ciu ON f.id_pais = ciu.id_pais AND f.id_departamento = ciu.id_departamento AND f.id_ciudad = ciu.id_ciudad
        WHERE f.id_factura = :id_factura AND c.id_user = :id_user
    ",
    'obtenerDetallesFactura' => "
        SELECT 
            d.val_cantidad, d.val_neto, prod.nom_producto, prod.id_producto,
            (SELECT url_imagen FROM tab_imagenes i WHERE i.id_producto = prod.id_producto ORDER BY id_imagen ASC LIMIT 1) as imagen
        FROM tab_det_fact d
        JOIN tab_productos prod ON d.id_producto = prod.id_producto
        WHERE d.id_factura = :id_factura
    ",
    'registrarCarritoItem' => "
        SELECT fun_c_carrito_item(:usuario_id, :producto_id, :cantidad, :precio)
    ",
    'cambiarEstadoCarrito' => "
        UPDATE carrito SET status = :status WHERE id = :id
    ",
    'registrarCarrito' => "
        SELECT fun_c_carrito(:id_user, :items, :total)
    ",
    'contarUsuarios' => "
        SELECT COUNT(*) FROM tab_users WHERE is_deleted = FALSE
    ",
    'contarProductos' => "
        SELECT COUNT(*) FROM tab_productos WHERE is_deleted = FALSE
    ",
    'contarPedidos' => "
        SELECT COUNT(*) FROM tab_enc_fact
    ",
    'contarArtesanos' => "
        SELECT COUNT(*) FROM tab_productores WHERE is_deleted = FALSE
    ",
    'sumarIngresosMes' => "
        SELECT COALESCE(SUM(val_tot_fact), 0) FROM tab_enc_fact
        WHERE EXTRACT(MONTH FROM fec_factura) = EXTRACT(MONTH FROM CURRENT_DATE)
          AND EXTRACT(YEAR FROM fec_factura) = EXTRACT(YEAR FROM CURRENT_DATE)
    ",
    'listarUsuariosAdmin' => "
        SELECT 
            u.id_user, u.nom_user, u.ape_user, u.mail_user, u.foto_user,
            NOT u.is_deleted AS is_active, u.created_at,
            CASE 
                WHEN EXISTS (SELECT 1 FROM tab_menu_user mu WHERE mu.id_user = u.id_user AND mu.id_menu = 8 AND mu.is_deleted = FALSE) THEN 'Admin'
                WHEN EXISTS (SELECT 1 FROM tab_productores p WHERE p.id_user = u.id_user AND p.is_deleted = FALSE) THEN 'Vendedor'
                ELSE 'Cliente'
            END as nom_grupo
        FROM tab_users u
        ORDER BY u.id_user DESC
    ",
    'listarProductosAdmin' => "
        SELECT 
            p.id_producto, p.nom_producto, p.precio_producto, p.stock_productor,
            p.is_active, p.is_deleted, p.created_at,
            c.nom_categoria,
            s.nom_stand,
            (SELECT url_imagen FROM tab_imagenes i WHERE i.id_producto = p.id_producto ORDER BY id_imagen ASC LIMIT 1) as primera_imagen
        FROM tab_productos p
        LEFT JOIN tab_categorias c ON p.id_categoria = c.id_categoria
        LEFT JOIN tab_productores pr ON p.id_productor = pr.id_productor
        LEFT JOIN tab_stand s ON pr.id_productor = s.id_productor
        ORDER BY p.is_deleted ASC, p.id_producto DESC
    ",
    'toggleUsuarioActivo' => "
        SELECT fun_softdel_tab_users(:id_user, :is_deleted)
    ",
    'toggleProductoActivo' => "
        SELECT fun_softdel_tab_productos(:id_producto, :is_deleted)
    ",
    'listarTodosMenus' => "
        SELECT id_menu, nom_menu, icono_menu, url_menu FROM tab_menu WHERE is_deleted = FALSE ORDER BY id_menu ASC
    ",
    'listarMenusUsuario' => "
        SELECT m.id_menu, m.nom_menu, m.icono_menu,
               CASE WHEN mu.is_deleted = FALSE THEN TRUE ELSE FALSE END AS tiene_acceso
        FROM tab_menu m
        LEFT JOIN tab_menu_user mu ON m.id_menu = mu.id_menu AND mu.id_user = :id_user
        WHERE m.is_deleted = FALSE
        ORDER BY m.id_menu ASC
    ",
];
