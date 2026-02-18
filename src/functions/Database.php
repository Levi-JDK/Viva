<?php

class Database {
    private static $instance = null;
    public $connection;
    private $statements = []; 

    private function __construct() {
        // Environment variables are loaded in index.php
        
        
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
        // Consultas para registrar productos
        $this->statements['obtenerIdProductor'] = $this->connection->prepare("SELECT id_productor FROM tab_productores WHERE id_user = :id_user");
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
                imagenes
            FROM fun_obtener_productos(:id_productor)
        ");

        $this->statements['obtenerProductoPorId'] = $this->connection->prepare("
            SELECT * FROM fun_obtener_producto_por_id(:id_producto)
        ");
        $this->statements['registrarStand'] = $this->connection->prepare("
            SELECT fun_c_stand(
                :id_productor, :nom_stand, :slogan_stand, 
                :descripcion_stand, :img_stand, :portada_stand
            )
        ");
        
        $this->statements['verificarStand'] = $this->connection->prepare("SELECT id_stand FROM tab_stand WHERE id_productor = :id_p");
        
        $this->statements['obtenerStand'] = $this->connection->prepare("SELECT * FROM tab_stand WHERE id_productor = :id_p");

        $this->statements['actualizarStand'] = $this->connection->prepare("
            SELECT fun_u_stand(
                :id_productor, :id_stand, :nom_stand, :slogan_stand, 
                :descripcion_stand, :img_stand, :portada_stand
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

    private function __clone() {}
}