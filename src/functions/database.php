<?php
class Database {
    private static $instance = null;
    public $connection;
    private $statements = [];

    private function __construct() {
        // Cargar variables de entorno si no han sido cargadas por index.php
        if (!isset($_ENV['DB_HOST'])) {
            require_once __DIR__ . '/../../vendor/autoload.php';
            $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2));
            $dotenv->safeLoad();
        }

        $config = [
            'host' => $_ENV['DB_HOST'],
            'port' => $_ENV['DB_PORT'],
            'dbname' => $_ENV['DB_NAME']
        ];
        $username = $_ENV['DB_USERNAME'];
        $password = $_ENV['DB_PASSWORD'];

        $dsn = 'pgsql:' . http_build_query($config, '', ';');
        
        $this->connection = new PDO($dsn, $username, $password, [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION 
        ]);
        
        $this->prepararConsultas();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self(); 
        }
        return self::$instance;
    }

    private function prepararConsultas() {
        $this->statements['validarEmail'] = $this->connection->prepare("SELECT fun_val_mail(:email)");
        $this->statements['crearUsuario'] = $this->connection->prepare("SELECT fun_c_user(:email, :contrasena, :nombre, :apellido)");
        $this->statements['obtenerHashLogin'] = $this->connection->prepare("SELECT fun_val_log(:email)");
        $this->statements['obtenerUsuarioPorEmail'] = $this->connection->prepare("SELECT id_user, nom_user FROM tab_users WHERE mail_user = :email");
        $this->statements['obtenerUsuarioPorId'] = $this->connection->prepare("SELECT nom_user, ape_user, mail_user, foto_user, created_at FROM tab_users WHERE id_user = :id");
        $this->statements['actualizarPerfil'] = $this->connection->prepare("UPDATE tab_users SET nom_user = :nombre, ape_user = :apellido WHERE id_user = :id");
        $this->statements['obtenerIdPorEmail'] = $this->connection->prepare("SELECT id_user FROM tab_users WHERE mail_user = :email");
        $this->statements['actualizarFotoUsuario'] = $this->connection->prepare("SELECT fun_u_foto_user(:id, :foto) as resultado");
        $this->statements['obtenerTiposDocumento'] = $this->connection->prepare("SELECT id, nombre FROM tipos_col_view");
        $this->statements['obtenerDepartamentos'] = $this->connection->prepare("SELECT id, nombre FROM departamentos_col_view");
        $this->statements['obtenerCiudades'] = $this->connection->prepare("SELECT id, nombre FROM  obtener_ciudades(:id_depto) ORDER BY nombre ASC;");
        $this->statements['obtenerGrupos'] = $this->connection->prepare("SELECT id, nombre FROM grupos_view");
        $this->statements['obtenerBancos'] = $this->connection->prepare("SELECT id, nombre FROM bancos_view");
        $this->statements['crearProductor'] = $this->connection->prepare(
            "SELECT fun_c_productor(
                :tipo_doc, :id_prod, :id_user,
                :dir, :pais, :dpto, :ciudad, :grupo,
                :banco, :cuenta, :tipo_cuenta
            )"
        );
        $this->statements['validarProductor'] = $this->connection->prepare("SELECT fun_val_productor(:id_user)");
        // Consultas para el formulario de productos
        $this->statements['obtenerCategorias'] = $this->connection->prepare("SELECT id_categoria, nom_categoria FROM categorias_view");
        $this->statements['obtenerColores'] = $this->connection->prepare("SELECT id_color, nom_color FROM colores_view");
        $this->statements['obtenerOficios'] = $this->connection->prepare("SELECT id_oficio, nom_oficio FROM oficios_view");
        $this->statements['obtenerMaterias'] = $this->connection->prepare("SELECT id_materia, nom_materia FROM materias_view");
        // Consultas para registrar/modificar productos
        $this->statements['obtenerIdProductor'] = $this->connection->prepare("SELECT id_productor FROM tab_productores WHERE id_user = :id_user");
        $this->statements['eliminarProductoLogicamente'] = $this->connection->prepare("
            UPDATE tab_productos 
            SET is_deleted = TRUE, is_active = FALSE, stock_productor = 0 
            WHERE id_producto = :id_producto AND id_productor = :id_productor
        ");
        $this->statements['registrarProducto'] = $this->connection->prepare("
            SELECT fun_c_producto(
                :id_productor, :nom_producto, :stock_productor, 
                :id_categoria, :id_color, :id_oficio, :id_materia, 
                :precio_producto, :descripcion_producto, :is_active
            )
        ");
        $this->statements['registrarImagen'] = $this->connection->prepare("
            SELECT fun_c_imagen(:id_producto, :url_imagen)
        ");
        // Obtiene los datos de los productos asociados al productor
        $this->statements['obtenerProductos'] = $this->connection->prepare("
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
        ");

        $this->statements['incrementarVistasProducto'] = $this->connection->prepare("
            SELECT * FROM fun_u_vista_producto(:id_producto)
        ");

        $this->statements['obtenerProductoPorId'] = $this->connection->prepare("
            SELECT 
                id_producto, nom_producto, precio_producto, stock_productor, 
                descripcion_producto, id_categoria, nom_categoria, id_oficio, nom_oficio, 
                id_materia, nom_materia, id_color, nom_color, id_productor, 
                nom_productor, ubicacion, imagenes 
            FROM fun_obtener_producto_por_id(:id_producto)
        ");
        $this->statements['registrarStand'] = $this->connection->prepare("
            SELECT fun_c_stand(
                :id_productor, :nom_stand, :slogan_stand, 
                :descripcion_stand, :img_stand, :portada_stand
            )
        ");
        
        $this->statements['verificarStand'] = $this->connection->prepare("SELECT id_stand FROM tab_stand WHERE id_productor = :id_p");
        
        $this->statements['obtenerStand'] = $this->connection->prepare("
            SELECT id_productor, id_stand, nom_stand, slogan_stand, descripcion_stand, img_stand, portada_stand 
            FROM tab_stand WHERE id_stand = :id_s
        ");

        $this->statements['obtenerStandPrivado'] = $this->connection->prepare("
            SELECT id_productor, id_stand, nom_stand, slogan_stand, descripcion_stand, img_stand, portada_stand 
            FROM tab_stand WHERE id_productor = :id_p
        ");

        $this->statements['obtenerIdStandPorUser'] = $this->connection->prepare("
            SELECT s.id_stand 
            FROM tab_stand s
            INNER JOIN tab_productores p ON s.id_productor = p.id_productor
            WHERE p.id_user = :id_user AND s.is_deleted = FALSE
            LIMIT 1
        ");

        $this->statements['actualizarStand'] = $this->connection->prepare("
            SELECT fun_u_stand(
                :id_productor, :id_stand, :nom_stand, :slogan_stand, 
                :descripcion_stand, :img_stand, :portada_stand
            )
        ");

        // Configuracion y Textos Landing
        $this->statements['obtenerConfiguracionGlobal'] = $this->connection->prepare("
            SELECT * FROM tab_pmtros WHERE id_parametro = 1 AND is_deleted = FALSE LIMIT 1
        ");

        $this->statements['actualizarParametrosGlob'] = $this->connection->prepare("
            SELECT fun_u_parametros(
                :id_parametro, :nom_plataforma, :dir_contacto, :correo_contacto,
                :val_inifact, :val_finfact, :val_actfact, :val_observa,
                :landing_hero_titulo, :landing_hero_subtitulo, :landing_hero_btn,
                :landing_conf_1_tit, :landing_conf_1_sub, :landing_conf_2_tit, :landing_conf_2_sub,
                :landing_conf_3_tit, :landing_conf_3_sub, :landing_filosofia_tit, :landing_filosofia_p1, :landing_filosofia_p2
            )
        ");
        
        // Obtener productos destacados con info de stand y primera imagen
        $this->statements['obtenerProductosDestacados'] = $this->connection->prepare("
            SELECT 
                p.id_producto,
                p.id_productor,
                p.nom_producto,
                p.precio_producto,
                p.descripcion_producto,
                s.nom_stand,
                s.img_stand,
                (SELECT url_imagen FROM tab_imagenes WHERE id_producto = p.id_producto ORDER BY id_imagen LIMIT 1) as primera_imagen
            FROM tab_productos p
            LEFT JOIN tab_stand s ON p.id_productor = s.id_productor
            WHERE p.is_deleted = FALSE AND p.is_active = TRUE
            ORDER BY p.created_at ASC
            LIMIT :limit
        ");

        $this->statements['obtenerMenuPublico'] = $this->connection->prepare("
            SELECT id_menu, nom_menu, url_menu, icono_menu 
            FROM tab_menu 
            WHERE id_menu IN (1, 2, 3) 
            ORDER BY id_menu ASC
        ");

        // Funciones de Seguridad - Control de Accesos (Navegación Dinámica)
        $this->statements['obtenerNavegacionUsuario'] = $this->connection->prepare("
            SELECT id_menu, nom_menu, url_menu, icono_menu 
            FROM fun_obtener_navegacion_usuario(:id_user)
        ");
        $this->statements['asignarMenuUsuario'] = $this->connection->prepare("
            SELECT fun_asignar_menu(:id_user, :id_menu)
        ");
        $this->statements['revocarMenuUsuario'] = $this->connection->prepare("
            SELECT fun_revocar_menu(:id_user, :id_menu)
        "); 

        // ── Recuperación de contraseña (OTP) ─────────────────────────────────
        $this->statements['crearResetToken'] = $this->connection->prepare("
            SELECT fun_c_reset_token(:mail_user, :minutos)
        ");
        $this->statements['validarResetToken'] = $this->connection->prepare("
            SELECT fun_v_reset_token(:mail_user, :token_reset)
        ");
        $this->statements['actualizarPassword'] = $this->connection->prepare("
            SELECT fun_u_password(:id_user, :pass_user)
        ");
        
        // Obtener todos los productos para catálogo
        $this->statements['obtenerProductosCatalogo'] = $this->connection->prepare("
            SELECT 
                p.id_producto,
                p.id_productor,
                p.nom_producto,
                p.precio_producto,
                p.descripcion_producto,
                s.nom_stand,
                s.img_stand,
                (SELECT url_imagen FROM tab_imagenes WHERE id_producto = p.id_producto ORDER BY id_imagen LIMIT 1) as primera_imagen
            FROM tab_productos p
            LEFT JOIN tab_stand s ON p.id_productor = s.id_productor
            WHERE p.is_deleted = FALSE AND p.is_active = TRUE
            ORDER BY p.created_at DESC
        ");

        // Obtener detalle completo del producto (Producto + Stand + Imágenes + Ubicación)
        $this->statements['obtenerDetalleProducto'] = $this->connection->prepare("
            SELECT id_producto, nom_producto, precio_producto, descripcion_producto, stock_productor, 
                   is_active, id_categoria, nom_categoria, id_color, nom_color, id_oficio, nom_oficio, 
                   id_materia, nom_materia, id_productor, nom_productor, id_stand, nom_stand, img_stand, 
                   slogan_stand, descripcion_stand, portada_stand, ubicacion, imagenes 
            FROM fun_obtener_detalle_producto(:id_producto)
        ");

        // Obtener stands activos (Directorio)
        $this->statements['obtenerStandsActivos'] = $this->connection->prepare("
            SELECT id_productor, id_stand, nom_stand, slogan_stand, descripcion_stand, img_stand, portada_stand
            FROM tab_stand
            WHERE is_deleted = FALSE
            ORDER BY nom_stand ASC
        ");
        $this->statements['obtenerStandsDestacados'] = $this->connection->prepare("
            SELECT id_productor, id_stand, nom_stand, slogan_stand, descripcion_stand, img_stand, portada_stand
            FROM tab_stand
            WHERE is_deleted = FALSE
            ORDER BY RANDOM()
            LIMIT :limit
        ");

        // Buscar stands activos por nombre o descripción
        $this->statements['buscarStandsActivos'] = $this->connection->prepare("
            SELECT id_productor, id_stand, nom_stand, slogan_stand, descripcion_stand, img_stand, portada_stand
            FROM tab_stand
            WHERE is_deleted = FALSE AND (nom_stand ILIKE :search OR descripcion_stand ILIKE :search)
            ORDER BY nom_stand ASC
        ");

        // Nuevas consultas para conteos dinámicos en los filtros del catálogo
        // Nota: Solo se cuentan productos que están activos y no borrados
        $this->statements['obtenerFiltrosCategorias'] = $this->connection->prepare("
            SELECT c.id_categoria, c.nom_categoria, c.img_cat, COUNT(p.id_producto) as total
            FROM categorias_view c
            INNER JOIN tab_productos p ON p.id_categoria = c.id_categoria
            WHERE p.is_deleted = FALSE AND p.is_active = TRUE
            GROUP BY c.id_categoria, c.nom_categoria, c.img_cat
            ORDER BY c.nom_categoria ASC
        ");

        $this->statements['obtenerFiltrosOficios'] = $this->connection->prepare("
            SELECT o.id_oficio, o.nom_oficio, COUNT(p.id_producto) as total
            FROM oficios_view o
            INNER JOIN tab_productos p ON p.id_oficio = o.id_oficio
            WHERE p.is_deleted = FALSE AND p.is_active = TRUE
            GROUP BY o.id_oficio, o.nom_oficio
            ORDER BY o.nom_oficio ASC
        ");

        $this->statements['obtenerFiltrosMaterias'] = $this->connection->prepare("
            SELECT m.id_materia, m.nom_materia, COUNT(p.id_producto) as total
            FROM materias_view m
            INNER JOIN tab_productos p ON p.id_materia = m.id_materia
            WHERE p.is_deleted = FALSE AND p.is_active = TRUE
            GROUP BY m.id_materia, m.nom_materia
            ORDER BY m.nom_materia ASC
        ");

        // Consulta del carrito de compras
        $this->statements['gestionarCarrito'] = $this->connection->prepare(
            "SELECT fun_carrito(:id_user, :accion, :id_producto, :cantidad)"
        );

        // Consultar Favoritos
        $this->statements['agregarFavorito'] = $this->connection->prepare(
            "SELECT fun_c_favorito(:id_user, :id_producto)"
        );

        $this->statements['eliminarFavorito'] = $this->connection->prepare(
            "SELECT fun_d_favoritos(:id_user, :id_producto)"
        );

        $this->statements['eliminarResena'] = $this->connection->prepare(
            "DELETE FROM tab_resenas WHERE id_user = :id_user AND id_producto = :id_producto"
        );
        
        $this->statements['obtenerPedidosCliente'] = $this->connection->prepare("
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
        ");

        $this->statements['obtenerFavoritosUsuario'] = $this->connection->prepare("
            SELECT 
                p.id_producto, p.nom_producto, p.precio_producto, p.descripcion_producto, 
                s.nom_stand, s.img_stand, 
                (SELECT url_imagen FROM tab_imagenes WHERE id_producto = p.id_producto ORDER BY id_imagen LIMIT 1) as primera_imagen
            FROM tab_favoritos f
            INNER JOIN tab_productos p ON f.id_producto = p.id_producto
            LEFT JOIN tab_stand s ON p.id_productor = s.id_productor
            WHERE f.id_user = :id_user AND f.is_deleted = FALSE AND p.is_deleted = FALSE AND p.is_active = TRUE
            ORDER BY f.created_at DESC
        ");

        // Reseñas de Productos
        $this->statements['agregarResena'] = $this->connection->prepare("
            SELECT fun_c_resena(:id_user, :id_producto, :calificacion, :texto)
        ");

        $this->statements['obtenerResenasProducto'] = $this->connection->prepare("
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
        ");

        $this->statements['obtenerPromedioEstrellasProducto'] = $this->connection->prepare("
            SELECT 
                COALESCE(AVG(calificacion), 0) as promedio,
                COUNT(id_producto) as total_resenas
            FROM tab_resenas
            WHERE id_producto = :id_producto AND is_deleted = FALSE
        ");

        $this->statements['obtenerPromedioEstrellasStand'] = $this->connection->prepare("
            SELECT 
                COALESCE(AVG(r.calificacion), 0) as promedio,
                COUNT(r.id_producto) as total_resenas
            FROM tab_resenas r
            INNER JOIN tab_productos p ON r.id_producto = p.id_producto
            INNER JOIN tab_stand s ON p.id_productor = s.id_productor
            WHERE s.id_stand = :id_stand AND r.is_deleted = FALSE
        ");

        // ── Clientes y Facturación ────────────────────────────────────────────

        // UPSERT de dirección de envío del cliente (desde form de checkout)
        $this->statements['guardarCliente'] = $this->connection->prepare(
            "SELECT fun_c_cliente(:id_user, :id_client, :nom, :mail, :dpto, :ciudad, :dir, :barrio)"
        );

        // Actualizar datos ePayco del cliente tras confirmación de pago
        $this->statements['actualizarClienteEpayco'] = $this->connection->prepare(
            "SELECT fun_u_cliente_epayco(:id_user, :id_client, :id_tipo_doc, :tel, :ref, :txn, :banco, :cod_resp)"
        );

        // Obtener dirección de envío guardada del cliente (para pre-llenar form y para la factura)
        $this->statements['obtenerDireccionCliente'] = $this->connection->prepare(
            "SELECT id_departamento, id_ciudad, dir_envio, barrio_envio
               FROM tab_clientes WHERE id_user = :id_user AND is_deleted = FALSE LIMIT 1"
        );

        // Facturación completa: enc_fact + det_fact + kardex + limpiar carrito
        // fun_facturar recibe id_user (busca id_client internamente) y NO necesita ids_productor
        $this->statements['facturar'] = $this->connection->prepare(
            "SELECT fun_facturar(
                :id_user, :id_pago,
                :dpto, :ciudad, :dir,
                :epayco_ref, :epayco_txn, :epayco_estado,
                :ids_producto::INTEGER[],
                :cantidades::INTEGER[]
            )"
        );
    }

    public function ejecutar($nombre, $params = []) {
        if (!isset($this->statements[$nombre])) {
            throw new Exception("Consulta preparada '$nombre' no encontrada.");
        }
        $stmt = $this->statements[$nombre];
        
        // Bind dinámico respetando tipos
        foreach ($params as $key => $value) {
            if (is_int($value)) {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } elseif (is_bool($value)) {
                $stmt->bindValue($key, $value, PDO::PARAM_BOOL);
            } elseif (is_null($value)) {
                $stmt->bindValue($key, $value, PDO::PARAM_NULL);
            } else {
                // Strings o cualquier otra cosa se pasa como string
                $stmt->bindValue($key, $value, PDO::PARAM_STR);
            }
        }

        $stmt->execute();
        return $stmt;
    }

    /**
     * Obtiene los productos del catálogo aplicando filtros dinámicos.
     * Esta función construye la consulta de manera segura utilizando statements no pre-registrados,
     * dado que la cantidad de parámetros es altamente dinámica.
     */
    public function obtenerProductosCatalogoFiltrado($filtros = []) {
        $sql = "
            SELECT 
                p.id_producto,
                p.id_productor,
                p.nom_producto,
                p.precio_producto,
                p.descripcion_producto,
                s.nom_stand,
                s.img_stand,
                (SELECT url_imagen FROM tab_imagenes WHERE id_producto = p.id_producto ORDER BY id_imagen LIMIT 1) as primera_imagen
            FROM tab_productos p
            LEFT JOIN tab_stand s ON p.id_productor = s.id_productor
            WHERE p.is_deleted = FALSE AND p.is_active = TRUE
        ";
        
        $params = [];
        $condiciones = [];

        // Filtro por Texto (búsqueda en nombre y descripción)
        if (!empty($filtros['search'])) {
            $condiciones[] = "(p.nom_producto ILIKE :search OR p.descripcion_producto ILIKE :search)";
            $params[':search'] = '%' . $filtros['search'] . '%';
        }

        // Filtro por Categoría
        if (!empty($filtros['categoria']) && is_numeric($filtros['categoria'])) {
            $condiciones[] = "p.id_categoria = :categoria";
            $params[':categoria'] = (int)$filtros['categoria'];
        }

        // Filtro por Oficio
        if (!empty($filtros['oficio']) && is_numeric($filtros['oficio'])) {
            $condiciones[] = "p.id_oficio = :oficio";
            $params[':oficio'] = (int)$filtros['oficio'];
        }

        // Filtro por Materia
        if (!empty($filtros['materia']) && is_numeric($filtros['materia'])) {
            $condiciones[] = "p.id_materia = :materia";
            $params[':materia'] = (int)$filtros['materia'];
        }

        // Filtro por Precio Mínimo
        if (isset($filtros['min_price']) && is_numeric($filtros['min_price']) && $filtros['min_price'] > 0) {
            $condiciones[] = "p.precio_producto >= :min_price";
            $params[':min_price'] = (float)$filtros['min_price'];
        }

        // Filtro por Precio Máximo
        if (isset($filtros['max_price']) && is_numeric($filtros['max_price']) && $filtros['max_price'] > 0) {
            $condiciones[] = "p.precio_producto <= :max_price";
            $params[':max_price'] = (float)$filtros['max_price'];
        }

        // Agregar condiciones al WHERE
        if (count($condiciones) > 0) {
            $sql .= " AND " . implode(" AND ", $condiciones);
        }

        // Orden (por defecto, los más recientes)
        $sql .= " ORDER BY p.created_at DESC";

        $stmt = $this->connection->prepare($sql);
        
        // Bind parameters dinámicos
        foreach ($params as $key => $value) {
            if (is_int($value)) {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value, PDO::PARAM_STR);
            }
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- Obtener Parámetros Globales (Landing y generales) ---
    public function obtenerConfiguracion() {
        $stmt = $this->ejecutar('obtenerConfiguracionGlobal');
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function __clone() {}
}