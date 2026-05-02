# Spec: Lazy Prepare Database Refactor

> Refactor `database.php` from eager prepare (58 PDOStatements en constructor) a lazy prepare (prepare on first use).
> Date: 2026-04-30 | Change: `lazy-prepare-database`

---

## Motivation

`Database::__construct()` prepara ~58 PDOStatements inmediatamente al crear la instancia (patrón singleton via `getInstance()`). Un request típico ejecuta solo 5-10 de esas queries — ~85% del trabajo de `prepare()` es desperdiciado. Esto agrega:

- **~5.8ms overhead local** y hasta **~116ms remoto** por request
- **60-170 KB** de objetos PDOStatement muertos en memoria
- **~58 llamadas a `prepare()`** innecesarias en cada request

El patrón lazy ya existe en el mismo archivo (`obtenerProductosCatalogoFiltrado`, `gestionarCRUDAdmin`) — las queries nombradas deben seguir el mismo enfoque.

---

## Requisitos Funcionales

### REQ-1: Mapa de SQL strings sin `prepare()` inmediato

`prepararConsultas()` (o su equivalente) DEBE retornar un mapa `nombre → SQL string` sin invocar `$this->connection->prepare()`. Cada entrada que hoy es:

```php
$this->statements['validarEmail'] = $this->connection->prepare("SELECT fun_val_mail(:email)");
```

DEBE convertirse en:

```php
'validarEmail' => "SELECT fun_val_mail(:email)",
```

**Rationale**: Eliminar el costo de `prepare()` en el constructor. El SQL string ya existe — solo se elimina el wrapper `prepare()`.

### REQ-2: Lazy prepare en `ejecutar()`

`ejecutar($nombre, $params)` DEBE implementar lazy prepare con este algoritmo:

```
1. Si $this->statements[$nombre] es un PDOStatement → usarlo directamente (ya preparado)
2. Si $this->statements[$nombre] es un string SQL → preparar y cachear:
   a. $this->statements[$nombre] = $this->connection->prepare($sql)
   b. Continuar con bind + execute normal
3. Si $nombre no existe en el mapa → lanzar Exception("Consulta preparada '$nombre' no encontrada.")
```

**Rationale**: El primer uso de cada query prepara el statement y lo cachea. Usos subsecuentes reutilizan el PDOStatement preparado.

### REQ-3: Constructor NO llama a `prepararConsultas()`

`__construct()` NO DEBE invocar `$this->prepararConsultas()` ni ningún método que prepare statements. El constructor solo DEBE:

1. Cargar variables de entorno si no están cargadas
2. Validar credenciales (fail fast si faltan)
3. Crear la conexión PDO
4. Configurar atributos PDO (`FETCH_MODE`, `ERRMODE`)

**Rationale**: El constructor debe ser liviano. La preparación de statements se delega al primer uso.

### REQ-4: Firma de `ejecutar()` inmutable

La firma pública `ejecutar($nombre, $params = [])` NO DEBE cambiar. Los parámetros, tipos de retorno, y comportamiento observable (retorno de PDOStatement, excepciones) DEBEN permanecer idénticos.

**Rationale**: Hay ~105 call sites en el codebase. Ningún consumidor debe ser modificado.

### REQ-5: Métodos ad-hoc lazy permanecen intactos

`obtenerProductosCatalogoFiltrado()` y `gestionarCRUDAdmin()` NO DEBEN ser modificados. Estos métodos ya hacen `prepare()` inline y no usan `ejecutar()`.

**Rationale**: Estas queries tienen SQL dinámico (filtros variables, entidades dinámicas) — el patrón lazy ad-hoc es correcto para ellas.

---

## Requisitos No Funcionales

### REQ-NF-1: Zero breaking changes para consumidores

Ningún controller, service, API endpoint, o script que invoque `ejecutar()` DEBE requerir modificación. El cambio es 100% interno a `Database`.

### REQ-NF-2: Transacciones de `CartService.php` deben funcionar

Las transacciones que usan múltiples calls a `ejecutar()` dentro de `beginTransaction()` / `commit()` / `rollBack()` DEBEN comportarse idénticamente. El lazy prepare NO debe interferir con el manejo de transacciones.

### REQ-NF-3: No activar `PDO::ATTR_PERSISTENT`

Este cambio NO DEBE activar conexiones persistentes. Eso se evaluará en un change separado.

### REQ-NF-4: Memory footprint por request

El memory footprint máximo de statements preparados en un request NO DEBE exceder el actual (58 statements). En la práctica, será menor porque solo se preparan los statements usados.

### REQ-NF-5: Performance del primer request

El primer request que usa una query específica incurrirá en el costo de `prepare()` (igual que hoy). Requests subsecuentes dentro del mismo request reutilizan el statement cacheado (igual que hoy). No hay degradación de performance.

---

## Escenarios de Comportamiento

### ESC-1: Primer uso de una query nombrada

**Given** una instancia de `Database` recién creada via `getInstance()`
**When** se llama `ejecutar('validarEmail', [':email' => 'test@example.com'])`
**Then**:
- Se prepara el statement `SELECT fun_val_mail(:email)` en ese momento
- Se hace bind del parámetro `:email`
- Se ejecuta el statement
- Se retorna el PDOStatement
- El statement queda cacheado en `$this->statements['validarEmail']`

### ESC-2: Uso subsecuente de la misma query

**Given** `ejecutar('validarEmail', ...)` ya fue llamado una vez en el mismo request
**When** se llama `ejecutar('validarEmail', [':email' => 'otro@example.com'])`
**Then**:
- NO se prepara el statement (ya está en cache)
- Se reutiliza el PDOStatement cacheado
- Se hace bind del nuevo parámetro
- Se ejecuta y retorna

### ESC-3: Query nombrada inexistente

**Given** una instancia de `Database`
**When** se llama `ejecutar('nombreInexistente', [])`
**Then**:
- Se lanza `Exception("Consulta preparada 'nombreInexistente' no encontrada.")`
- El mensaje de error es idéntico al comportamiento actual

### ESC-4: Múltiples queries en una transacción (CartService pattern)

**Given** una transacción activa via `$db->connection->beginTransaction()`
**When** se ejecutan múltiples calls:
```php
$db->ejecutar('gestionarCarrito', [...]);
$db->ejecutar('registrarCarritoItem', [...]);
$db->ejecutar('registrarCarritoItem', [...]);
```
**Then**:
- Cada query se prepara lazy en su primer uso
- Los statements se cachean y reutilizan
- La transacción funciona idénticamente (commit/rollback sin cambios)

### ESC-5: Request con 8 queries diferentes

**Given** un request típico que usa 8 queries nombradas diferentes
**When** el request se completa
**Then**:
- Exactamente 8 statements fueron preparados (no 58)
- Los 8 statements están cacheados en `$this->statements`
- Los otros ~50 statements NO fueron preparados
- El request funciona correctamente

### ESC-6: `obtenerProductosCatalogoFiltrado()` no se ve afectado

**Given** una llamada a `obtenerProductosCatalogoFiltrado(['categoria' => 5])`
**When** el método se ejecuta
**Then**:
- El método hace su propio `prepare()` inline (sin cambios)
- NO usa `ejecutar()` ni el mapa de statements nombrados
- Retorna los resultados correctamente

### ESC-7: `gestionarCRUDAdmin()` no se ve afectado

**Given** una llamada a `gestionarCRUDAdmin('read', 'categoria')`
**When** el método se ejecuta
**Then**:
- El método hace sus propios `prepare()` inline (sin cambios)
- NO usa `ejecutar()` ni el mapa de statements nombrados
- Retorna los resultados correctamente

### ESC-8: `obtenerConfiguracion()` usa `ejecutar()` internamente

**Given** una llamada a `obtenerConfiguracion()`
**When** el método se ejecuta (internamente llama `ejecutar('obtenerConfiguracionGlobal')`)
**Then**:
- `ejecutar()` prepara lazy el statement si es primera vez
- Retorna el PDOStatement
- `obtenerConfiguracion()` hace `fetch()` y retorna el array

---

## Criterios de Aceptación

### AC-1: Todos los call sites existentes funcionan sin cambios

- [ ] Los ~105 call sites de `ejecutar()` en el codebase funcionan sin modificación
- [ ] Ningún controller, service, o script requiere cambios
- [ ] Verificar con búsqueda: `grep -r "ejecutar(" src/` lista todos los callers

### AC-2: `prepararConsultas()` ya no se llama en el constructor

- [ ] `__construct()` NO contiene `$this->prepararConsultas()` ni equivalente
- [ ] El constructor solo crea la conexión PDO y valida credenciales

### AC-3: Mapa de SQL strings contiene todas las queries nombradas

- [ ] El mapa contiene exactamente las mismas keys que el `prepararConsultas()` actual
- [ ] Cada SQL string es idéntico al que se pasaba a `prepare()` originalmente
- [ ] No hay SQL strings duplicados (verificar `crearProductor` que aparece 2 veces en el código actual — líneas 58-64 y 403-405)

### AC-4: Lazy prepare funciona correctamente

- [ ] Primera llamada a una query prepara el statement
- [ ] Segunda llamada reutiliza el statement cacheado
- [ ] Query inexistente lanza la misma excepción que hoy

### AC-5: Transacciones funcionan correctamente

- [ ] `CartService.php` transacciones funcionan sin alteración
- [ ] `beginTransaction()` / `commit()` / `rollBack()` no se ven afectados
- [ ] Múltiples `ejecutar()` dentro de una transacción preparan lazy correctamente

### AC-6: No hay PDO errors por SQL inválido

- [ ] Todas las queries del mapa compilan correctamente al primer uso
- [ ] No hay errores de sintaxis SQL introducidos por el refactor

### AC-7: `PDO::ATTR_PERSISTENT` NO está activado

- [ ] Las opciones de PDO en el constructor son exactamente las actuales:
  - `PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC`
  - `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION`
- [ ] No se agrega `PDO::ATTR_PERSISTENT`

---

## Restricciones

### NO cambiar:
- La firma de `ejecutar($nombre, $params = [])`
- El comportamiento de `obtenerProductosCatalogoFiltrado()`
- El comportamiento de `gestionarCRUDAdmin()`
- El constante `ADMIN_ENTITIES`
- El método `getInstance()`
- El método `obtenerConfiguracion()`
- La visibilidad de `$connection` (public) ni `$statements` (private)
- El patrón singleton
- La validación de credenciales (fail fast)
- El loading de dotenv

### NO agregar:
- `PDO::ATTR_PERSISTENT`
- Nuevas dependencias
- Nuevos métodos públicos
- Logging o debugging code permanente

### NO eliminar:
- Ninguna query nombrada existente
- El manejo de tipos en bind (PARAM_INT, PARAM_BOOL, PARAM_NULL, PARAM_STR)
- La excepción para queries no encontradas

---

## Mapa de Statements Nombrados

El refactor DEBE preservar todas las siguientes keys en el mapa de SQL strings:

| # | Key | SQL (primeras 60 chars) |
|---|-----|------------------------|
| 1 | `validarEmail` | `SELECT fun_val_mail(:email)` |
| 2 | `crearUsuario` | `SELECT fun_c_user(:email, :contrasena, :nombre, :apellido)` |
| 3 | `obtenerHashLogin` | `SELECT fun_val_log(:email)` |
| 4 | `obtenerUsuarioPorEmail` | `SELECT id_user, nom_user FROM tab_users WHERE mail_user = :email` |
| 5 | `obtenerUsuarioPorId` | `SELECT nom_user, ape_user, mail_user, foto_user, created_at...` |
| 6 | `actualizarPerfil` | `UPDATE tab_users SET nom_user = :nombre, ape_user = :apellido...` |
| 7 | `obtenerIdPorEmail` | `SELECT id_user FROM tab_users WHERE mail_user = :email` |
| 8 | `actualizarFotoUsuario` | `SELECT fun_u_foto_user(:id, :foto) as resultado` |
| 9 | `obtenerTiposDocumento` | `SELECT id, nombre FROM tipos_col_view` |
| 10 | `obtenerDepartamentos` | `SELECT id, nombre FROM departamentos_col_view` |
| 11 | `obtenerCiudades` | `SELECT id, nombre FROM obtener_ciudades(:id_depto) ORDER BY...` |
| 12 | `obtenerGrupos` | `SELECT id, nombre FROM grupos_view` |
| 13 | `obtenerBancos` | `SELECT id, nombre FROM bancos_view` |
| 14 | `crearProductor` | `SELECT fun_c_productor(:tipo_doc, :id_prod, :id_user...` |
| 15 | `validarProductor` | `SELECT fun_val_productor(:id_user)` |
| 16 | `obtenerCategorias` | `SELECT id_categoria, nom_categoria FROM categorias_view` |
| 17 | `obtenerColores` | `SELECT id_color, nom_color FROM colores_view` |
| 18 | `obtenerOficios` | `SELECT id_oficio, nom_oficio FROM oficios_view` |
| 19 | `obtenerMaterias` | `SELECT id_materia, nom_materia FROM materias_view` |
| 20 | `obtenerIdProductor` | `SELECT id_productor FROM tab_productores WHERE id_user = :id_user` |
| 21 | `eliminarProductoLogicamente` | `UPDATE tab_productos SET is_deleted = TRUE, is_active = FALSE...` |
| 22 | `registrarProducto` | `SELECT fun_c_producto(:id_productor, :nom_producto, :stock...` |
| 23 | `registrarImagen` | `SELECT fun_c_imagen(:id_producto, :url_imagen)` |
| 24 | `obtenerProductos` | `SELECT id_producto, nom_producto, precio_producto...` |
| 25 | `incrementarVistasProducto` | `SELECT fun_u_vista_producto(:id_producto) AS resultado` |
| 26 | `obtenerVistasProducto` | `SELECT vistas FROM tab_productos WHERE id_producto = :id_producto...` |
| 27 | `obtenerProductoPorId` | `SELECT id_producto, nom_producto, precio_producto...` |
| 28 | `registrarStand` | `SELECT fun_c_stand(:id_productor, :nom_stand, :slogan_stand...` |
| 29 | `verificarStand` | `SELECT id_stand FROM tab_stand WHERE id_productor = :id_p` |
| 30 | `obtenerStand` | `SELECT id_productor, id_stand, nom_stand, slogan_stand...` |
| 31 | `obtenerStandPrivado` | `SELECT id_productor, id_stand, nom_stand, slogan_stand...` |
| 32 | `obtenerIdStandPorUser` | `SELECT s.id_stand FROM tab_stand s INNER JOIN tab_productores...` |
| 33 | `actualizarStand` | `SELECT fun_u_stand(:id_productor, :id_stand, :nom_stand...` |
| 34 | `obtenerConfiguracionGlobal` | `SELECT nom_plataforma, dir_contacto, correo_contacto...` |
| 35 | `actualizarParametrosGlob` | `SELECT fun_u_parametros(:id_parametro, :nom_plataforma...` |
| 36 | `obtenerProductosDestacados` | `SELECT p.id_producto, p.id_productor, p.nom_producto...` |
| 37 | `obtenerMenuPublico` | `SELECT id_menu, nom_menu, url_menu, icono_menu FROM tab_menu...` |
| 38 | `obtenerNavegacionUsuario` | `SELECT id_menu, nom_menu, url_menu, icono_menu FROM...` |
| 39 | `asignarMenuUsuario` | `SELECT fun_asignar_menu(:id_user, :id_menu)` |
| 40 | `revocarMenuUsuario` | `SELECT fun_revocar_menu(:id_user, :id_menu)` |
| 41 | `crearResetToken` | `SELECT fun_c_reset_token(:mail_user, :minutos)` |
| 42 | `validarResetToken` | `SELECT fun_v_reset_token(:mail_user, :token_reset)` |
| 43 | `actualizarPassword` | `SELECT fun_u_password(:id_user, :pass_user)` |
| 44 | `obtenerProductosCatalogo` | `SELECT p.id_producto, p.id_productor, p.nom_producto...` |
| 45 | `obtenerDetalleProducto` | `SELECT id_producto, nom_producto, precio_producto...` |
| 46 | `obtenerStandsActivos` | `SELECT id_productor, id_stand, nom_stand, slogan_stand...` |
| 47 | `obtenerStandsDestacados` | `SELECT id_productor, id_stand, nom_stand, slogan_stand...` |
| 48 | `buscarStandsActivos` | `SELECT id_productor, id_stand, nom_stand, slogan_stand...` |
| 49 | `obtenerFiltrosCategorias` | `SELECT c.id_categoria, c.nom_categoria, c.img_cat, COUNT...` |
| 50 | `obtenerFiltrosOficios` | `SELECT o.id_oficio, o.nom_oficio, COUNT(p.id_producto)...` |
| 51 | `obtenerFiltrosMaterias` | `SELECT m.id_materia, m.nom_materia, COUNT(p.id_producto)...` |
| 52 | `gestionarCarrito` | `SELECT fun_carrito(:id_user, :accion, :id_producto, :cantidad)` |
| 53 | `agregarFavorito` | `SELECT fun_c_favorito(:id_user, :id_producto)` |
| 54 | `eliminarFavorito` | `SELECT fun_d_favoritos(:id_user, :id_producto)` |
| 55 | `eliminarResena` | `DELETE FROM tab_resenas WHERE id_user = :id_user AND...` |
| 56 | `obtenerPedidosCliente` | `SELECT f.id_factura, f.fec_factura, f.val_tot_fact...` |
| 57 | `obtenerFavoritosUsuario` | `SELECT p.id_producto, p.id_productor, p.nom_producto...` |
| 58 | `agregarResena` | `SELECT fun_c_resena(:id_user, :id_producto, :calificacion...` |
| 59 | `obtenerResenasProducto` | `SELECT r.calificacion, r.texto_resena, r.created_at...` |
| 60 | `obtenerPromedioEstrellasProducto` | `SELECT COALESCE(AVG(calificacion), 0) as promedio...` |
| 61 | `obtenerPromedioEstrellasStand` | `SELECT COALESCE(AVG(r.calificacion), 0) as promedio...` |
| 62 | `crearProductor` (dup) | `SELECT fun_c_productor(:tipo_doc, :id_prod, :id_user...` |
| 63 | `guardarCliente` | `SELECT fun_c_cliente(:id_user, :id_client, :nom, :mail...` |
| 64 | `actualizarClienteEpayco` | `SELECT fun_u_cliente_epayco(:id_user, :id_client...` |
| 65 | `obtenerDireccionCliente` | `SELECT id_departamento, id_ciudad, dir_envio, barrio_envio...` |
| 66 | `facturar` | `SELECT fun_facturar(:id_user, :id_pago, :dpto, :ciudad...` |
| 67 | `obtenerUltimoIdProducto` | `SELECT MAX(id_producto) FROM tab_productos` |
| 68 | `verificarPropiedadProducto` | `SELECT id_producto FROM tab_productos WHERE id_producto...` |
| 69 | `obtenerImagenesProducto` | `SELECT id_imagen, url_imagen FROM tab_imagenes WHERE...` |
| 70 | `actualizarDescripcionPrecio` | `UPDATE tab_productos SET descripcion_producto = :desc...` |
| 71 | `eliminarImagen` | `DELETE FROM tab_imagenes WHERE id_imagen = :id` |
| 72 | `obtenerNombreUsuarioPorEmail` | `SELECT nom_user FROM tab_users WHERE mail_user = :email...` |
| 73 | `actualizarProducto` | `SELECT fun_u_producto(:id_producto, :nom_producto, :stock...` |
| 74 | `obtenerFacturaPorId` | `SELECT f.id_factura, f.fec_factura, f.val_hora_fact...` |
| 75 | `obtenerDetallesFactura` | `SELECT d.val_cantidad, d.val_neto, prod.nom_producto...` |
| 76 | `registrarCarritoItem` | `SELECT fun_c_carrito_item(:usuario_id, :producto_id...` |
| 77 | `cambiarEstadoCarrito` | `UPDATE carrito SET status = :status WHERE id = :id` |
| 78 | `registrarCarrito` | `SELECT fun_c_carrito(:id_user, :items, :total)` |
| 79 | `contarUsuarios` | `SELECT COUNT(*) FROM tab_users WHERE is_deleted = FALSE` |
| 80 | `contarProductos` | `SELECT COUNT(*) FROM tab_productos WHERE is_deleted = FALSE` |
| 81 | `contarPedidos` | `SELECT COUNT(*) FROM tab_enc_fact` |
| 82 | `contarArtesanos` | `SELECT COUNT(*) FROM tab_productores WHERE is_deleted = FALSE` |
| 83 | `sumarIngresosMes` | `SELECT COALESCE(SUM(val_tot_fact), 0) FROM tab_enc_fact...` |
| 84 | `listarUsuariosAdmin` | `SELECT u.id_user, u.nom_user, u.ape_user, u.mail_user...` |
| 85 | `listarProductosAdmin` | `SELECT p.id_producto, p.nom_producto, p.precio_producto...` |
| 86 | `toggleUsuarioActivo` | `SELECT fun_softdel_tab_users(:id_user, :is_deleted)` |
| 87 | `toggleProductoActivo` | `SELECT fun_softdel_tab_productos(:id_producto, :is_deleted)` |
| 88 | `listarTodosMenus` | `SELECT id_menu, nom_menu, icono_menu, url_menu FROM tab_menu...` |
| 89 | `listarMenusUsuario` | `SELECT m.id_menu, m.nom_menu, m.icono_menu...` |

**Nota**: `crearProductor` aparece duplicado en el código actual (líneas 58-64 y 403-405) con SQL idéntico. El refactor DEBE deduplicar esta entrada — una sola key `crearProductor` en el mapa.

---

## Estructura Propuesta del Código

```php
class Database {
    private static $instance = null;
    public $connection;
    private $statements = [];

    private function __construct() {
        // 1. Cargar .env si no está cargado
        // 2. Validar credenciales (fail fast)
        // 3. Crear conexión PDO
        // 4. NO llamar a prepararConsultas()
    }

    public static function getInstance() {
        // Sin cambios
    }

    /**
     * Retorna el mapa de queries nombradas: nombre → SQL string.
     * NO prepara statements — solo retorna los strings.
     */
    private function consultasNombradas(): array {
        return [
            'validarEmail' => "SELECT fun_val_mail(:email)",
            'crearUsuario' => "SELECT fun_c_user(:email, :contrasena, :nombre, :apellido)",
            // ... todas las queries del mapa
        ];
    }

    public function ejecutar($nombre, $params = []) {
        // Lazy prepare: si no es PDOStatement, preparar y cachear
        if (!isset($this->statements[$nombre])) {
            $sqlMap = $this->consultasNombradas();
            if (!isset($sqlMap[$nombre])) {
                throw new Exception("Consulta preparada '$nombre' no encontrada.");
            }
            $this->statements[$nombre] = $this->connection->prepare($sqlMap[$nombre]);
        }

        $stmt = $this->statements[$nombre];

        // Bind dinámico (sin cambios)
        foreach ($params as $key => $value) {
            // ... mismo bind type-respecting logic
        }

        $stmt->execute();
        return $stmt;
    }

    // obtenerProductosCatalogoFiltrado() — sin cambios
    // obtenerConfiguracion() — sin cambios
    // ADMIN_ENTITIES — sin cambios
    // gestionarCRUDAdmin() — sin cambios
}
```

---

## Riesgos y Mitigación

| Riesgo | Probabilidad | Mitigación |
|--------|-------------|------------|
| SQL typo al convertir `prepare()` a string | Baja | Los SQL strings ya existen y están probados — solo se elimina el wrapper `$this->connection->prepare()`. Copiar nombre por nombre, mecánico. |
| Error en nombre de statement no detectado hasta runtime | Media | Hoy falla en constructor. Ahora: fail en primer `ejecutar()` del nombre erróneo. Mismo mensaje de excepción. Agregar verificación de integridad: todos los callers usan keys que existen en el mapa. |
| Duplicación de `crearProductor` | Baja | El código actual tiene la misma key dos veces. El refactor debe deduplicar — una sola entrada en el mapa. |
| Race condition en lazy prepare | Nula | PHP es single-threaded por request. Singleton por request, sin concurrencia dentro del mismo proceso. |
| Statement cache crece sin límite | Baja | Max 89 statements (igual que el eager actual), pero solo se crean los usados. Mismo pico, menor promedio. |

---

## Rollback Plan

Revertir a la versión anterior de `database.php` — el contrato público (`ejecutar($nombre, $params)`) no cambia, zero impacto en consumidores. El rollback es un simple `git revert`.
