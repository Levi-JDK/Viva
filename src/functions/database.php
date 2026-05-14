<?php
class Database {
    private static $instance = null;
    public $connection;
    private $statements = [];
    private array $consultas;

    private function cargarConsultas(): void {
        $queriesFile = __DIR__ . '/queries.php';
        if (!file_exists($queriesFile)) {
            throw new Exception("Archivo de consultas no encontrado: queries.php");
        }
        $this->consultas = require $queriesFile;
        if (!is_array($this->consultas) || empty($this->consultas)) {
            throw new Exception("El archivo queries.php no retornó un array válido de consultas.");
        }
    }

    private function __construct() {
        $this->cargarConsultas();

        // Cargar variables de entorno si no han sido cargadas por index.php
        if (!isset($_ENV['DB_HOST'])) {
            require_once __DIR__ . '/../../vendor/autoload.php';
            $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2));
            $dotenv->safeLoad();
        }

        if (empty($_ENV['DB_HOST']) || empty($_ENV['DB_NAME']) || empty($_ENV['DB_USERNAME']) || empty($_ENV['DB_PASSWORD'])) {
            throw new Exception("Error de configuración: Faltan credenciales de base de datos en el entorno (.env). Fail fast.");
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

        if ($this->connection->inTransaction()) {
            $this->connection->rollBack();
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self(); 
        }
        return self::$instance;
    }

    public function ejecutar($nombre, $params = []) {
        if (!isset($this->statements[$nombre])) {
            if (!isset($this->consultas[$nombre])) {
                throw new Exception("Consulta preparada '$nombre' no encontrada.");
            }

            $this->statements[$nombre] = $this->connection->prepare($this->consultas[$nombre]);
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
                s.id_stand,
                s.nom_stand,
                s.img_stand,
                (SELECT url_imagen FROM tab_imagenes WHERE id_producto = p.id_producto ORDER BY id_imagen LIMIT 1) as primera_imagen
            FROM tab_productos p
            LEFT JOIN tab_stand s ON p.id_productor = s.id_productor
            WHERE p.is_deleted = FALSE
              AND p.is_active = TRUE
              AND (p.validation_status = 'approved' OR p.validation_status IS NULL)
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

    public const ADMIN_ENTITIES = [
        'banco'          => ['tabla' => 'tab_bancos',           'pk' => 'id_banco',         'fun_c' => 'fun_c_banco',          'fun_u' => 'fun_u_banco',          'fun_d' => 'fun_softdel_tab_bancos'],
        'categoria'      => ['tabla' => 'tab_categorias',       'pk' => 'id_categoria',     'fun_c' => 'fun_c_categoria',      'fun_u' => 'fun_u_categoria',      'fun_d' => 'fun_softdel_tab_categorias'],
        'ciudad'         => ['tabla' => 'tab_ciudades',         'pk' => 'id_ciudad',        'fun_c' => 'fun_c_ciudad',         'fun_u' => 'fun_u_ciudad',         'fun_d' => 'fun_softdel_tab_ciudades'],
        'color'          => ['tabla' => 'tab_color',            'pk' => 'id_color',         'fun_c' => 'fun_c_color',          'fun_u' => 'fun_u_color',          'fun_d' => 'fun_softdel_tab_color'],
        'departamento'   => ['tabla' => 'tab_departamentos',    'pk' => 'id_departamento',  'fun_c' => 'fun_c_departamento',   'fun_u' => 'fun_u_departamento',   'fun_d' => 'fun_softdel_tab_departamentos'],
        'forma_pago'     => ['tabla' => 'tab_formas_pago',      'pk' => 'id_pago',          'fun_c' => 'fun_c_forma_pago',     'fun_u' => 'fun_u_forma_pago',     'fun_d' => 'fun_softdel_tab_formas_pago'],
        'grupo'          => ['tabla' => 'tab_grupos',           'pk' => 'id_grupo',         'fun_c' => 'fun_c_grupo',          'fun_u' => 'fun_u_grupo',          'fun_d' => 'fun_softdel_tab_grupos'],
        'idioma'         => ['tabla' => 'tab_idiomas',          'pk' => 'id_idioma',        'fun_c' => 'fun_c_idioma',         'fun_u' => 'fun_u_idioma',         'fun_d' => 'fun_softdel_tab_idiomas'],
        'materia'        => ['tabla' => 'tab_materia_prima',    'pk' => 'id_materia',       'fun_c' => 'fun_c_materia',        'fun_u' => 'fun_u_materia',        'fun_d' => 'fun_softdel_tab_materia_prima'],
        'moneda'         => ['tabla' => 'tab_monedas',          'pk' => 'id_moneda',        'fun_c' => 'fun_c_moneda',         'fun_u' => 'fun_u_moneda',         'fun_d' => 'fun_softdel_tab_monedas'],
        'oficio'         => ['tabla' => 'tab_oficios',          'pk' => 'id_oficio',        'fun_c' => 'fun_c_oficio',         'fun_u' => 'fun_u_oficio',         'fun_d' => 'fun_softdel_tab_oficios'],
        'pais'           => ['tabla' => 'tab_paises',           'pk' => 'id_pais',          'fun_c' => 'fun_c_pais',           'fun_u' => 'fun_u_pais',           'fun_d' => 'fun_softdel_tab_paises'],
        'parametros'     => ['tabla' => 'tab_pmtros',           'pk' => 'id_parametro',     'fun_c' => 'fun_c_parametros',     'fun_u' => 'fun_u_parametros',     'fun_d' => 'fun_softdel_tab_pmtros'],
        'tipo_doc'       => ['tabla' => 'tab_tipos_doc',        'pk' => 'id_tipo_doc',      'fun_c' => 'fun_c_tipo_doc',       'fun_u' => 'fun_u_tipo_doc',       'fun_d' => 'fun_softdel_tab_tipos_doc'],
    ];

    public function gestionarCRUDAdmin(string $accion, string $entidad, array $datos = []) {
        if (!isset(self::ADMIN_ENTITIES[$entidad])) {
            throw new Exception("Entidad no válida.");
        }
        $conf = self::ADMIN_ENTITIES[$entidad];
        $tabla = $conf['tabla'];
        $pk = $conf['pk']; // PK primario visual (puede ser compósita localmente pero lo sacamos del scheme)

        if ($accion === 'read') {
            $stmtCols = $this->connection->prepare("SELECT column_name FROM information_schema.columns WHERE table_name = :tabla AND column_name NOT IN ('created_by', 'created_at', 'updated_by', 'updated_at', 'is_deleted') ORDER BY ordinal_position");
            $stmtCols->execute([':tabla' => $tabla]);
            $cols = $stmtCols->fetchAll(PDO::FETCH_COLUMN);
            
            $sql = "SELECT " . implode(', ', $cols) . " FROM $tabla WHERE is_deleted = FALSE ORDER BY 1 ASC";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            return ['columnas' => $cols, 'filas' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        }

        if ($accion === 'create' || $accion === 'update' || $accion === 'delete') {
            $fun_name = '';
            if ($accion === 'create') $fun_name = $conf['fun_c'];
            if ($accion === 'update') $fun_name = $conf['fun_u'];
            if ($accion === 'delete') $fun_name = $conf['fun_d'];
            
            $stmtArgs = $this->connection->prepare("
                SELECT p.pronargs, pg_get_function_identity_arguments(p.oid) as ident 
                FROM pg_proc p WHERE p.proname = :fun LIMIT 1
            ");
            $stmtArgs->execute([':fun' => $fun_name]);
            $funcInfo = $stmtArgs->fetch(PDO::FETCH_ASSOC);
            
            if (!$funcInfo) throw new Exception("Función de base de datos no encontrada: $fun_name.");
            
            $argNamesStr = $funcInfo['ident'];
            $paramsArray = $argNamesStr ? explode(',', $argNamesStr) : [];
            $bindParams = [];
            
            foreach ($paramsArray as $paramDef) {
                $pName = trim(explode(' ', trim($paramDef))[0]);
                if ($pName === 'p_deleted') {
                    $bindParams[] = 'TRUE';
                } elseif ($pName === 'p_id') {
                    $bindParams[] = isset($datos[$pk]) && $datos[$pk] !== '' ? $datos[$pk] : null;
                } else {
                    $colName = preg_replace('/^p_/', '', $pName); 
                    if (isset($datos[$colName]) && $datos[$colName] !== '') {
                        $bindParams[] = $datos[$colName];
                    } elseif (isset($datos[$pName]) && $datos[$pName] !== '') {
                        $bindParams[] = $datos[$pName];
                    } else {
                        $bindParams[] = null;
                    }
                }
            }
            
            $placeholders = implode(',', array_fill(0, count($bindParams), '?'));
            $stmt = $this->connection->prepare("SELECT $fun_name($placeholders)");
            $stmt->execute($bindParams);
            return $stmt->fetchColumn();
        }
        
        throw new Exception("Acción CRUD no soportada.");
    }

    private function __clone() {}
}
