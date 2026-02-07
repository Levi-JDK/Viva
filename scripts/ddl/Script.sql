-- DROPS
DROP TABLE IF EXISTS tab_kardex;
DROP TABLE IF EXISTS tab_envios;
DROP TABLE IF EXISTS tab_det_fact;
DROP TABLE IF EXISTS tab_enc_fact;
DROP TABLE IF EXISTS tab_transito;
DROP TABLE IF EXISTS tab_formas_pago;
DROP TABLE IF EXISTS tab_transportadoras;
DROP TABLE IF EXISTS tab_producto_productor;
DROP TABLE IF EXISTS tab_productos;
DROP TABLE IF EXISTS tab_categorias;
DROP TABLE IF EXISTS tab_materia_prima;
DROP TABLE IF EXISTS tab_oficios;
DROP TABLE IF EXISTS tab_clientes;
DROP TABLE IF EXISTS tab_monedas;
DROP TABLE IF EXISTS tab_idiomas;
DROP TABLE IF EXISTS tab_productores;
DROP TABLE IF EXISTS tab_pais_tipos_doc;
DROP TABLE IF EXISTS tab_regiones;
DROP TABLE IF EXISTS tab_grupos;
DROP TABLE IF EXISTS tab_ciudades;
DROP TABLE IF EXISTS tab_paises;
DROP TABLE IF EXISTS tab_tipos_doc;
DROP TABLE IF EXISTS tab_color;
DROP TABLE IF EXISTS tab_bancos;
DROP TABLE IF EXISTS tab_menu_user;
DROP TABLE IF EXISTS tab_menu;
DROP TABLE IF EXISTS tab_users;
DROP TABLE IF EXISTS tab_pmtros;

-- Tabla de parámetros
CREATE TABLE IF NOT EXISTS tab_pmtros
(
    id_parametro   DECIMAL(1,0)  NOT NULL DEFAULT 1,                   -- Identificador del registro de parámetros
    nom_plataforma VARCHAR       NOT NULL,                              -- Nombre de la plataforma
    dir_contacto   VARCHAR       NOT NULL,                              -- Dirección de contacto
    tel_contacto   VARCHAR(20)   NOT NULL,                              -- Teléfono de contacto
    correo_contacto VARCHAR      NOT NULL,                              -- Correo de contacto
    val_poriva     DECIMAL(4,2)  NOT NULL DEFAULT 19.00,                -- Porcentaje de IVA por defecto
    val_inifact    DECIMAL(12,0) NOT NULL,                              -- Numeración inicial de facturas
    val_finfact    DECIMAL(12,0) NOT NULL CHECK(val_finfact >= val_inifact), -- Numeración final de facturas
    val_actfact    DECIMAL(12,0) NOT NULL CHECK (val_actfact >= val_inifact AND val_actfact <= val_finfact), -- Número actual
    val_observa    TEXT,                                                -- Observación impresa en factura
    created_by     VARCHAR       NOT NULL DEFAULT current_user,         -- Usuario que creó
    created_at     TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
    updated_by     VARCHAR,                                             -- Usuario que modificó
    updated_at     TIMESTAMP WITHOUT TIME ZONE,                         -- Fecha de modificación
    is_deleted     BOOLEAN       NOT NULL DEFAULT FALSE,                -- Indicador de borrado lógico
    PRIMARY KEY(id_parametro)
);

INSERT INTO tab_pmtros (
  nom_plataforma, dir_contacto, tel_contacto, correo_contacto,
  val_poriva, val_inifact, val_finfact, val_actfact, val_observa
) VALUES (
  'Artesanías Viva',
  'Calle 100 # 15-20, Bogotá',
  '+57 601 555 1234',
  'contacto@artesaniasviva.com',
  19.00,
  1000,
  9000,
  1000,
  'Parámetros iniciales de facturación'
);

-- SELECT * FROM tab_users
-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS tab_users (
    id_user         INTEGER   NOT NULL,                                 -- Identificador del usuario
    mail_user       VARCHAR   NOT NULL,                                  -- Correo del usuario
    pass_user       VARCHAR   NOT NULL,                                  -- Contraseña (hash)
    nom_user        VARCHAR   NOT NULL,                                  -- Nombres del usuario
    ape_user        VARCHAR   NOT NULL,                                  -- Apellidos del usuario
    foto_user       VARCHAR   DEFAULT ('images/default.webp'),            -- Foto del usuario
    ult_fec_ingreso TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Último ingreso
    created_by      VARCHAR   NOT NULL DEFAULT current_user,             -- Usuario que creó
    created_at      TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
    updated_by      VARCHAR,                                             -- Usuario que modificó
    updated_at      TIMESTAMP WITHOUT TIME ZONE,                         -- Fecha de modificación
    is_deleted      BOOLEAN   NOT NULL DEFAULT FALSE,                    -- Borrado lógico
    PRIMARY KEY (id_user)
);

-- Tabla de menú
CREATE TABLE IF NOT EXISTS tab_menu
(
    id_menu    INTEGER  NOT NULL,                                       -- Identificador del menú
    nom_menu   VARCHAR  NOT NULL,                                       -- Nombre del menú
    created_by VARCHAR  NOT NULL DEFAULT current_user,                  -- Usuario que creó
    created_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
    updated_by VARCHAR,                                                 -- Usuario que modificó
    updated_at TIMESTAMP WITHOUT TIME ZONE,                             -- Fecha de modificación
    is_deleted BOOLEAN  NOT NULL DEFAULT FALSE,                         -- Borrado lógico
    PRIMARY KEY(id_menu)
);

-- Tabla de menú por usuario
CREATE TABLE IF NOT EXISTS tab_menu_user
(
    id_user    INTEGER  NOT NULL,                                       -- Usuario
    id_menu    INTEGER  NOT NULL,                                       -- Menú asignado
    created_by VARCHAR  NOT NULL DEFAULT current_user,                  -- Usuario que creó
    created_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
    updated_by VARCHAR,                                                 -- Usuario que modificó
    updated_at TIMESTAMP WITHOUT TIME ZONE,                             -- Fecha de modificación
    is_deleted BOOLEAN  NOT NULL DEFAULT FALSE,                         -- Borrado lógico
    PRIMARY KEY(id_user,id_menu),
    FOREIGN KEY(id_user) REFERENCES tab_users(id_user),
    FOREIGN KEY(id_menu) REFERENCES tab_menu(id_menu)
);

-- Tabla de bancos
CREATE TABLE IF NOT EXISTS tab_bancos
(




    
    id_banco   VARCHAR  NOT NULL,                                       -- Código del banco
    nom_banco  VARCHAR  NOT NULL,                                       -- Nombre del banco
    dir_banco  VARCHAR  NOT NULL,                                       -- Dirección del banco
    created_by VARCHAR  NOT NULL DEFAULT current_user,                  -- Usuario que creó
    created_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
    updated_by VARCHAR,                                                 -- Usuario que modificó
    updated_at TIMESTAMP WITHOUT TIME ZONE,                             -- Fecha de modificación
    is_deleted BOOLEAN  NOT NULL DEFAULT FALSE,                         -- Borrado lógico
    PRIMARY KEY(id_banco)
);

-- Tabla de colores
CREATE TABLE IF NOT EXISTS tab_color
(
    id_color   VARCHAR  NOT NULL,                                       -- Código de color (hex RGB)
    nom_color  VARCHAR  NOT NULL,                                       -- Nombre del color
    created_by VARCHAR  NOT NULL DEFAULT current_user,                  -- Usuario que creó
    created_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
    updated_by VARCHAR,                                                 -- Usuario que modificó
    updated_at TIMESTAMP WITHOUT TIME ZONE,                             -- Fecha de modificación
    is_deleted BOOLEAN  NOT NULL DEFAULT FALSE,                         -- Borrado lógico
    PRIMARY KEY(id_color)
);

-- Tabla de tipos de documento
CREATE TABLE IF NOT EXISTS tab_tipos_doc
(
    id_tipo_doc DECIMAL(1,0) NOT NULL,                                  -- Identificador del tipo de documento
    nom_tipo_doc VARCHAR     NOT NULL,                                   -- Nombre/código del tipo de documento (CC, PP, etc.)
    created_by   VARCHAR     NOT NULL DEFAULT current_user,              -- Usuario que creó
    created_at   TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
    updated_by   VARCHAR,                                                -- Usuario que modificó
    updated_at   TIMESTAMP WITHOUT TIME ZONE,                            -- Fecha de modificación
    is_deleted   BOOLEAN     NOT NULL DEFAULT FALSE,                     -- Borrado lógico
    PRIMARY KEY(id_tipo_doc)
);

-- Tabla de países
CREATE TABLE IF NOT EXISTS tab_paises
(
    id_pais     DECIMAL(3,0) NOT NULL,                                  -- Identificador del país
    cod_iso     DECIMAL(3,0) NOT NULL,                                  -- Código ISO numérico
    nom_pais    VARCHAR      NOT NULL,                                   -- Nombre del país
    arancel_pct DECIMAL(5,2) NOT NULL DEFAULT 0.00,                      -- % arancel desde Colombia
    created_by  VARCHAR      NOT NULL DEFAULT current_user,              -- Usuario que creó
    created_at  TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
    updated_by  VARCHAR,                                                 -- Usuario que modificó
    updated_at  TIMESTAMP WITHOUT TIME ZONE,                             -- Fecha de modificación
    is_deleted  BOOLEAN      NOT NULL DEFAULT FALSE,                     -- Borrado lógico
    PRIMARY KEY(id_pais)
);

-- Tabla de ciudades
CREATE TABLE IF NOT EXISTS tab_ciudades
(
    id_ciudad  DECIMAL(5,0) NOT NULL,                                   -- Identificador de la ciudad
    nom_ciudad VARCHAR      NOT NULL,                                   -- Nombre de la ciudad
    zip_ciudad VARCHAR      NOT NULL,                                   -- Código postal
    id_pais    DECIMAL(3,0) NOT NULL,                                   -- País al que pertenece
    created_by VARCHAR      NOT NULL DEFAULT current_user,              -- Usuario que creó
    created_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
    updated_by VARCHAR,                                                 -- Usuario que modificó
    updated_at TIMESTAMP WITHOUT TIME ZONE,                             -- Fecha de modificación
    is_deleted BOOLEAN      NOT NULL DEFAULT FALSE,                     -- Borrado lógico
    PRIMARY KEY(id_ciudad,id_pais),
    FOREIGN KEY(id_pais) REFERENCES tab_paises(id_pais)
);

-- Tabla de grupos poblacionales
CREATE TABLE IF NOT EXISTS tab_grupos
(
    id_grupo   DECIMAL(12,0) NOT NULL,                                  -- Identificador del grupo
    nom_grupo  VARCHAR       NOT NULL,                                   -- Nombre del grupo
    created_by VARCHAR       NOT NULL DEFAULT current_user,              -- Usuario que creó
    created_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
    updated_by VARCHAR,                                                 -- Usuario que modificó
    updated_at TIMESTAMP WITHOUT TIME ZONE,                             -- Fecha de modificación
    is_deleted BOOLEAN       NOT NULL DEFAULT FALSE,                     -- Borrado lógico
    PRIMARY KEY(id_grupo)
);

-- Tabla de regiones
CREATE TABLE IF NOT EXISTS tab_regiones
(
    id_region  DECIMAL(1,0) NOT NULL,                                   -- Identificador de la región
    nom_region VARCHAR      NOT NULL,                                   -- Nombre de la región
    created_by VARCHAR      NOT NULL DEFAULT current_user,              -- Usuario que creó
    created_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
    updated_by VARCHAR,                                                 -- Usuario que modificó
    updated_at TIMESTAMP WITHOUT TIME ZONE,                             -- Fecha de modificación
    is_deleted BOOLEAN      NOT NULL DEFAULT FALSE,                     -- Borrado lógico
    PRIMARY KEY(id_region)
);

-- Tabla de relación país–tipo de documento
CREATE TABLE IF NOT EXISTS tab_pais_tipos_doc
(
    id_pais     DECIMAL(3,0) NOT NULL,                                  -- País
    id_tipo_doc DECIMAL(1,0) NOT NULL,                                  -- Tipo de documento permitido en el país
    created_by  VARCHAR      NOT NULL DEFAULT current_user,              -- Usuario que creó
    created_at  TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
    updated_by  VARCHAR,                                                 -- Usuario que modificó
    updated_at  TIMESTAMP WITHOUT TIME ZONE,                             -- Fecha de modificación
    is_deleted  BOOLEAN      NOT NULL DEFAULT FALSE,                     -- Borrado lógico
    PRIMARY KEY(id_pais, id_tipo_doc),
    FOREIGN KEY(id_pais)     REFERENCES tab_paises(id_pais),
    FOREIGN KEY(id_tipo_doc) REFERENCES tab_tipos_doc(id_tipo_doc)
);

-- Tabla de productores
CREATE TABLE IF NOT EXISTS tab_productores
(
    tipo_doc_productor DECIMAL(1,0)  NOT NULL,                           -- Tipo de documento del productor
    id_productor       DECIMAL(10,0) NOT NULL CHECK (id_productor BETWEEN 1 AND 9999999999), -- Número de documento
    id_user            INTEGER       NOT NULL,                            -- Usuario asociado en la plataforma
    dir_prod           VARCHAR       NOT NULL,                            -- Dirección del productor
    nom_emprend        VARCHAR       NOT NULL,                            -- Nombre del emprendimiento
    rut_prod           VARCHAR       NOT NULL,                            -- Documento RUT (ruta/archivo)
    cam_prod           VARCHAR       NOT NULL,                            -- Cámara de comercio (ruta/archivo)
    img_prod           VARCHAR,                                          -- Logo/imagen del emprendimiento
    id_pais            DECIMAL(3,0)  NOT NULL DEFAULT 1,                 -- País del productor
    id_ciudad          DECIMAL(5,0)  NOT NULL,                            -- Ciudad del productor
    id_grupo           DECIMAL(12,0) NOT NULL,                            -- Grupo poblacional
    id_region          DECIMAL(1,0)  NOT NULL,                            -- Región del país
    id_banco           VARCHAR       NOT NULL,                            -- Banco del productor
    id_cuenta_prod     DECIMAL(20,0) NOT NULL,                            -- Número de cuenta bancaria
    created_by         VARCHAR       NOT NULL DEFAULT current_user,       -- Usuario que creó
    created_at         TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
    updated_by         VARCHAR,                                          -- Usuario que modificó
    updated_at         TIMESTAMP WITHOUT TIME ZONE,                      -- Fecha de modificación
    is_deleted         BOOLEAN       NOT NULL DEFAULT FALSE,              -- Borrado lógico
    PRIMARY KEY(id_productor),
    FOREIGN KEY(tipo_doc_productor) REFERENCES tab_tipos_doc(id_tipo_doc),
    FOREIGN KEY(id_pais, tipo_doc_productor) REFERENCES tab_pais_tipos_doc(id_pais, id_tipo_doc),
    FOREIGN KEY(id_user)           REFERENCES tab_users(id_user),
    FOREIGN KEY(id_ciudad,id_pais) REFERENCES tab_ciudades(id_ciudad,id_pais),
    FOREIGN KEY(id_grupo)          REFERENCES tab_grupos(id_grupo),
    FOREIGN KEY(id_region)         REFERENCES tab_regiones(id_region),
    FOREIGN KEY(id_banco)          REFERENCES tab_bancos(id_banco)
);

-- Tabla de idiomas
CREATE TABLE IF NOT EXISTS tab_idiomas
(
    id_idioma  VARCHAR NOT NULL,                                         -- Código de idioma (ISO 639-1)
    nom_idioma VARCHAR NOT NULL,                                         -- Nombre del idioma
    created_by VARCHAR NOT NULL DEFAULT current_user,                    -- Usuario que creó
    created_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
    updated_by VARCHAR,                                                  -- Usuario que modificó
    updated_at TIMESTAMP WITHOUT TIME ZONE,                              -- Fecha de modificación
    is_deleted BOOLEAN NOT NULL DEFAULT FALSE,                           -- Borrado lógico
    PRIMARY KEY(id_idioma)
);

-- Tabla de monedas
CREATE TABLE IF NOT EXISTS tab_monedas
(
    id_moneda  VARCHAR NOT NULL,                                         -- Código de moneda (ISO 4217)
    nom_moneda VARCHAR NOT NULL,                                         -- Nombre de la moneda
    simbolo    VARCHAR NOT NULL,                                         -- Símbolo de la moneda
    created_by VARCHAR NOT NULL DEFAULT current_user,                    -- Usuario que creó
    created_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
    updated_by VARCHAR,                                                  -- Usuario que modificó
    updated_at TIMESTAMP WITHOUT TIME ZONE,                              -- Fecha de modificación
    is_deleted BOOLEAN NOT NULL DEFAULT FALSE,                           -- Borrado lógico
    PRIMARY KEY(id_moneda)
);

-- Tabla de clientes
CREATE TABLE IF NOT EXISTS tab_clientes
(
    id_user          INTEGER       NOT NULL,                             -- Usuario dueño de la cuenta
    tipo_doc_client  DECIMAL(1,0)  NOT NULL,                             -- Tipo de documento del cliente
    id_client        VARCHAR       NOT NULL,                             -- Identificador del cliente (número/documento)
    id_pais          DECIMAL(3,0)  NOT NULL,                             -- País del cliente
    id_ciudad        DECIMAL(5,0)  NOT NULL,                             -- Ciudad del cliente
    id_idioma        VARCHAR       NOT NULL,                             -- Idioma preferido
    id_moneda        VARCHAR       NOT NULL,                             -- Moneda preferida
    created_by       VARCHAR       NOT NULL DEFAULT current_user,        -- Usuario que creó
    created_at       TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
    updated_by       VARCHAR,                                            -- Usuario que modificó
    updated_at       TIMESTAMP WITHOUT TIME ZONE,                        -- Fecha de modificación
    is_deleted       BOOLEAN       NOT NULL DEFAULT FALSE,               -- Borrado lógico
    PRIMARY KEY(id_client),
    FOREIGN KEY(tipo_doc_client)   REFERENCES tab_tipos_doc(id_tipo_doc),
    FOREIGN KEY(id_pais, tipo_doc_client) REFERENCES tab_pais_tipos_doc(id_pais, id_tipo_doc),
    FOREIGN KEY(id_user)           REFERENCES tab_users(id_user),
    FOREIGN KEY(id_ciudad,id_pais) REFERENCES tab_ciudades(id_ciudad,id_pais),
    FOREIGN KEY(id_idioma)         REFERENCES tab_idiomas(id_idioma),
    FOREIGN KEY(id_moneda)         REFERENCES tab_monedas(id_moneda)
);

-- Tabla de oficios
CREATE TABLE IF NOT EXISTS tab_oficios
(
    id_oficio  DECIMAL(12,0) NOT NULL,                                   -- Identificador del oficio
    nom_oficio VARCHAR       NOT NULL,                                   -- Nombre del oficio
    created_by VARCHAR       NOT NULL DEFAULT current_user,              -- Usuario que creó
    created_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
    updated_by VARCHAR,                                                  -- Usuario que modificó
    updated_at TIMESTAMP WITHOUT TIME ZONE,                              -- Fecha de modificación
    is_deleted BOOLEAN       NOT NULL DEFAULT FALSE,                     -- Borrado lógico
    PRIMARY KEY(id_oficio)
);

-- Tabla de materia prima
CREATE TABLE IF NOT EXISTS tab_materia_prima
(
    id_materia  DECIMAL(12,0) NOT NULL,                                  -- Identificador de la materia prima
    nom_materia VARCHAR       NOT NULL,                                   -- Nombre de la materia prima
    created_by  VARCHAR       NOT NULL DEFAULT current_user,              -- Usuario que creó
    created_at  TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
    updated_by  VARCHAR,                                                  -- Usuario que modificó
    updated_at  TIMESTAMP WITHOUT TIME ZONE,                              -- Fecha de modificación
    is_deleted  BOOLEAN       NOT NULL DEFAULT FALSE,                     -- Borrado lógico
    PRIMARY KEY(id_materia)
);

-- Tabla de categorías
CREATE TABLE IF NOT EXISTS tab_categorias
(
    id_categoria DECIMAL(12,0) NOT NULL,                                  -- Identificador de la categoría
    nom_categoria VARCHAR      NOT NULL,                                   -- Nombre de la categoría
    created_by    VARCHAR      NOT NULL DEFAULT current_user,              -- Usuario que creó
    created_at    TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
    updated_by    VARCHAR,                                                 -- Usuario que modificó
    updated_at    TIMESTAMP WITHOUT TIME ZONE,                             -- Fecha de modificación
    is_deleted    BOOLEAN      NOT NULL DEFAULT FALSE,                     -- Borrado lógico
    PRIMARY KEY(id_categoria)
);

-- Tabla de productos
CREATE TABLE IF NOT EXISTS tab_productos
(
    id_producto  DECIMAL(12,0) NOT NULL,                                  -- Identificador del producto
    nom_producto VARCHAR       NOT NULL,                                   -- Nombre del producto
    stock        DECIMAL(12,0) NOT NULL,                                   -- Stock disponible
    id_categoria DECIMAL(12,0) NOT NULL,                                   -- Categoría del producto
    id_color     VARCHAR       NOT NULL,                                   -- Color principal
    id_oficio    DECIMAL(12,0) NOT NULL,                                   -- Oficio asociado
    id_materia   DECIMAL(12,0) NOT NULL,                                   -- Materia prima principal
    created_by   VARCHAR       NOT NULL DEFAULT current_user,              -- Usuario que creó
    created_at   TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
    updated_by   VARCHAR,                                                  -- Usuario que modificó
    updated_at   TIMESTAMP WITHOUT TIME ZONE,                              -- Fecha de modificación
    is_deleted   BOOLEAN       NOT NULL DEFAULT FALSE,                     -- Borrado lógico
    PRIMARY KEY(id_producto),
    FOREIGN KEY(id_categoria) REFERENCES tab_categorias(id_categoria),
    FOREIGN KEY(id_color)     REFERENCES tab_color(id_color),
    FOREIGN KEY(id_oficio)    REFERENCES tab_oficios(id_oficio),
    FOREIGN KEY(id_materia)   REFERENCES tab_materia_prima(id_materia)
);

-- Tabla de producto por productor
CREATE TABLE IF NOT EXISTS tab_producto_productor
(
    id_producto        DECIMAL(12,0) NOT NULL,                             -- Producto
    id_productor       DECIMAL(10,0) NOT NULL,                             -- Productor
    precio_prod        DECIMAL(10,2) NOT NULL,                             -- Precio de venta del productor
    stock_prod         DECIMAL(12,0) NOT NULL CHECK (stock_prod >= 0),     -- Stock del productor para el producto
    desc_prod_personal TEXT          NOT NULL,                              -- Descripción personalizada
    img_personal       VARCHAR       NOT NULL,                              -- Imagen personalizada
    activo             BOOLEAN       NOT NULL DEFAULT TRUE,                 -- Activo si tiene stock
    created_by         VARCHAR       NOT NULL DEFAULT current_user,         -- Usuario que creó
    created_at         TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
    updated_by         VARCHAR,                                            -- Usuario que modificó
    updated_at         TIMESTAMP WITHOUT TIME ZONE,                        -- Fecha de modificación
    is_deleted         BOOLEAN       NOT NULL DEFAULT FALSE,                -- Borrado lógico
    CONSTRAINT chk_pp_stock_activo CHECK (
        (stock_prod > 0 AND activo = TRUE) OR
        (stock_prod = 0 AND activo = FALSE)
    ),
    PRIMARY KEY(id_producto, id_productor),
    FOREIGN KEY(id_producto)  REFERENCES tab_productos(id_producto),
    FOREIGN KEY(id_productor) REFERENCES tab_productores(id_productor)
);

-- Tabla de transportadoras
CREATE TABLE IF NOT EXISTS tab_transportadoras
(
    id_transportador  VARCHAR NOT NULL,                                    -- Código de la transportadora
    nom_transportador VARCHAR NOT NULL,                                    -- Nombre de la transportadora
    tipo_transporte   VARCHAR NOT NULL,                                    -- Tipo (Nacional/Internacional/Mixto)
    tel_contacto      VARCHAR NOT NULL,                                    -- Teléfono de contacto
    correo_contacto   VARCHAR NOT NULL,                                    -- Correo de contacto
    sitio_web         VARCHAR NOT NULL,                                    -- Sitio web
    activo            BOOLEAN NOT NULL DEFAULT TRUE,                       -- Estado activo
    created_by        VARCHAR NOT NULL DEFAULT current_user,               -- Usuario que creó
    created_at        TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
    updated_by        VARCHAR,                                             -- Usuario que modificó
    updated_at        TIMESTAMP WITHOUT TIME ZONE,                         -- Fecha de modificación
    is_deleted        BOOLEAN NOT NULL DEFAULT FALSE,                      -- Borrado lógico
    PRIMARY KEY(id_transportador)
);

-- Tabla de formas de pago
CREATE TABLE IF NOT EXISTS tab_formas_pago
(
    id_pago    VARCHAR NOT NULL,                                           -- Código de la forma de pago
    nom_pago   VARCHAR NOT NULL,                                           -- Nombre de la forma de pago
    created_by VARCHAR NOT NULL DEFAULT current_user,                      -- Usuario que creó
    created_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
    updated_by VARCHAR,                                                    -- Usuario que modificó
    updated_at TIMESTAMP WITHOUT TIME ZONE,                                -- Fecha de modificación
    is_deleted BOOLEAN NOT NULL DEFAULT FALSE,                             -- Borrado lógico
    PRIMARY KEY(id_pago)
);

-- Tabla de tránsito de inventario
CREATE TABLE IF NOT EXISTS tab_transito
(
    id_entrada  DECIMAL(12,0) NOT NULL,                                   -- Identificador de la entrada
    id_producto DECIMAL(12,0) NOT NULL,                                   -- Producto en tránsito
    fec_entrada TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),        -- Fecha/hora de entrada
    val_entrada DECIMAL(4,0)  NOT NULL,                                   -- Cantidad de entrada
    fec_salida  TIMESTAMP WITHOUT TIME ZONE,                               -- Fecha/hora de salida
    created_by  VARCHAR       NOT NULL DEFAULT current_user,               -- Usuario que creó
    created_at  TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
    updated_by  VARCHAR,                                                   -- Usuario que modificó
    updated_at  TIMESTAMP WITHOUT TIME ZONE,                               -- Fecha de modificación
    is_deleted  BOOLEAN       NOT NULL DEFAULT FALSE,                      -- Borrado lógico
    PRIMARY KEY(id_entrada),
    FOREIGN KEY(id_producto) REFERENCES tab_productos(id_producto)
);

-- Tabla de encabezado de factura
CREATE TABLE IF NOT EXISTS tab_enc_fact
(
    id_factura    DECIMAL(7,0)  NOT NULL CHECK(id_factura >= 1),          -- Número de factura
    fec_factura   DATE          NOT NULL,                                  -- Fecha de emisión
    val_hora_fact TIME WITHOUT TIME ZONE NOT NULL,                         -- Hora de emisión
    id_client     VARCHAR       NOT NULL,                                  -- Cliente
    id_pais       DECIMAL(3,0)  NOT NULL,                                  -- País destino
    id_ciudad     DECIMAL(5,0)  NOT NULL,                                  -- Ciudad destino
    val_tot_fact  DECIMAL(12,2) NOT NULL,                                  -- Total factura
    ind_estado    BOOLEAN       NOT NULL,                                   -- Activa/Anulada
    id_pago       VARCHAR       NOT NULL,                                   -- Forma de pago
    created_by    VARCHAR       NOT NULL DEFAULT current_user,              -- Usuario que creó
    created_at    TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
    updated_by    VARCHAR,                                                  -- Usuario que modificó
    updated_at    TIMESTAMP WITHOUT TIME ZONE,                              -- Fecha de modificación
    is_deleted    BOOLEAN       NOT NULL DEFAULT FALSE,                     -- Borrado lógico
    PRIMARY KEY(id_factura),
    FOREIGN KEY(id_pago)              REFERENCES tab_formas_pago(id_pago),
    FOREIGN KEY(id_client)            REFERENCES tab_clientes(id_client),
    FOREIGN KEY(id_pais)              REFERENCES tab_paises(id_pais),
    FOREIGN KEY(id_ciudad,id_pais)    REFERENCES tab_ciudades(id_ciudad,id_pais)
);

-- Tabla de detalle de factura
CREATE TABLE IF NOT EXISTS tab_det_fact
(
    id_factura    DECIMAL(7,0)  NOT NULL,                                  -- Número de factura
    id_producto   DECIMAL(12,0) NOT NULL,                                  -- Producto facturado
    id_productor  DECIMAL(10,0) NOT NULL,                                  -- Productor asociado
    val_cantidad  DECIMAL(4,0)  NOT NULL CHECK(val_cantidad >=1),          -- Cantidad vendida
    val_bruto     DECIMAL(12,2) NOT NULL,                                  -- Valor antes de impuestos/descuentos
    val_neto      DECIMAL(12,2) NOT NULL,                                  -- Valor final línea
    created_by    VARCHAR       NOT NULL DEFAULT current_user,              -- Usuario que creó
    created_at    TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
    updated_by    VARCHAR,                                                  -- Usuario que modificó
    updated_at    TIMESTAMP WITHOUT TIME ZONE,                              -- Fecha de modificación
    is_deleted    BOOLEAN       NOT NULL DEFAULT FALSE,                     -- Borrado lógico
    PRIMARY KEY(id_factura, id_producto),
    FOREIGN KEY(id_factura)   REFERENCES tab_enc_fact(id_factura),
    FOREIGN KEY(id_producto)  REFERENCES tab_productos(id_producto),
    FOREIGN KEY(id_productor) REFERENCES tab_productores(id_productor)
);

-- Tabla de envíos
CREATE TABLE IF NOT EXISTS tab_envios
(
    id_envio         DECIMAL(12,0) NOT NULL,                               -- Identificador del envío
    id_factura       DECIMAL(7,0)  NOT NULL,                               -- Factura asociada
    fecha_envio      DATE          NOT NULL,                                -- Fecha de despacho
    id_transportador VARCHAR       NOT NULL,                                -- Transportadora
    num_guia         VARCHAR       NOT NULL,                                -- Número de guía
    estado_envio     VARCHAR       NOT NULL,                                -- Estado del envío
    direccion_dest   VARCHAR       NOT NULL,                                -- Dirección de destino
    barrio           VARCHAR       NOT NULL,                                -- Barrio del destino
    created_by       VARCHAR       NOT NULL DEFAULT current_user,           -- Usuario que creó
    created_at       TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
    updated_by       VARCHAR,                                              -- Usuario que modificó
    updated_at       TIMESTAMP WITHOUT TIME ZONE,                          -- Fecha de modificación
    is_deleted       BOOLEAN       NOT NULL DEFAULT FALSE,                 -- Borrado lógico
    PRIMARY KEY(id_envio),
    FOREIGN KEY(id_factura)       REFERENCES tab_enc_fact(id_factura),
    FOREIGN KEY(id_transportador) REFERENCES tab_transportadoras(id_transportador)
);

-- Tabla de kardex
CREATE TABLE IF NOT EXISTS tab_kardex
(
    id_kardex    DECIMAL(12,0) NOT NULL,                                   -- Identificador del movimiento
    id_producto  DECIMAL(12,0) NOT NULL,                                   -- Producto afectado
    id_productor DECIMAL(10,0) NOT NULL,                                   -- Productor relacionado
    tipo_movim   BOOLEAN       NOT NULL,                                   -- Tipo (TRUE entrada / FALSE salida)
    cantidad     DECIMAL(4,0)  NOT NULL CHECK(cantidad >= 1 AND cantidad <= 9999), -- Cantidad
    fecha_movim  TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),        -- Fecha del movimiento
    created_by   VARCHAR       NOT NULL DEFAULT current_user,               -- Usuario que creó
    created_at   TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
    updated_by   VARCHAR,                                                   -- Usuario que modificó
    updated_at   TIMESTAMP WITHOUT TIME ZONE,                               -- Fecha de modificación
    is_deleted   BOOLEAN       NOT NULL DEFAULT FALSE,                      -- Borrado lógico
    PRIMARY KEY(id_kardex),
    FOREIGN KEY(id_producto)  REFERENCES tab_productos(id_producto),
    FOREIGN KEY(id_productor) REFERENCES tab_productores(id_productor)
);

-- INDICES
CREATE INDEX idx_producto_categoria ON tab_productos(id_categoria);
CREATE INDEX idx_producto_color ON tab_productos(id_color);
CREATE INDEX idx_producto_oficio ON tab_productos(id_oficio);
CREATE INDEX idx_producto_materia ON tab_productos(id_materia);

CREATE INDEX idx_enc_fact_cliente ON tab_enc_fact(id_client);
CREATE INDEX idx_enc_fact_pago ON tab_enc_fact(id_pago);
CREATE INDEX idx_enc_fact_ciudad ON tab_enc_fact(id_ciudad, id_pais);

CREATE INDEX idx_det_fact_producto ON tab_det_fact(id_producto);
CREATE INDEX idx_det_fact_productor ON tab_det_fact(id_productor);

CREATE INDEX idx_kardex_producto ON tab_kardex(id_producto);
CREATE INDEX idx_kardex_productor ON tab_kardex(id_productor);
CREATE INDEX idx_kardex_fecha ON tab_kardex(fecha_movim);

CREATE INDEX idx_cliente_user ON tab_clientes(id_user);
CREATE INDEX idx_cliente_ciudad ON tab_clientes(id_ciudad, id_pais);

CREATE INDEX idx_productor_user ON tab_productores(id_user);

CREATE INDEX idx_envios_factura ON tab_envios(id_factura);
CREATE INDEX idx_envios_transportador ON tab_envios(id_transportador);

CREATE INDEX idx_transito_producto ON tab_transito(id_producto);

CREATE INDEX idx_producto_productor_activo ON tab_producto_productor(id_productor, activo);

CREATE INDEX idx_menu_user_user ON tab_menu_user(id_user);
CREATE INDEX idx_menu_user_menu ON tab_menu_user(id_menu);

CREATE INDEX idx_idiomas_nom ON tab_idiomas(nom_idioma);
CREATE INDEX idx_monedas_nom ON tab_monedas(nom_moneda);

-- INSERTS

INSERT INTO tab_tipos_doc (id_tipo_doc, nom_tipo_doc) VALUES
(1,'CC'),(2,'NIT'),(3,'CE'),(4,'PP'),(5,'NIE'),(6,'CPF');

INSERT INTO tab_paises (id_pais, cod_iso, nom_pais, arancel_pct) VALUES
(1,170,'Colombia',0.00),
(2,840,'Estados Unidos',8.00),
(3,826,'Reino Unido',10.00),
(4,250,'Francia',9.00),
(5,76,'Brasil',12.00);

INSERT INTO tab_ciudades (id_ciudad, nom_ciudad, zip_ciudad, id_pais) VALUES
(1,'Bucaramanga','680001',1),
(2,'Bogotá','110111',1),
(3,'Leticia','910001',1),
(4,'Miami','33101',2),
(5,'Londres','SW1A 1AA',3),
(6,'Medellín','050001',1),
(7,'Cartagena','130001',1),
(8,'São Paulo','01000-000',5);

INSERT INTO tab_grupos (id_grupo, nom_grupo) VALUES
(1,'Wayúu'),(2,'Emberá'),(3,'Arhuaco'),(4,'Kankuama'),(5,'Nasa'),(6,'Ticuna');

INSERT INTO tab_regiones (id_region, nom_region) VALUES
(1,'Caribe'),(2,'Andina'),(3,'Amazonía'),(4,'Orinoquía'),(5,'Pacífico');

INSERT INTO tab_pais_tipos_doc (id_pais, id_tipo_doc) VALUES
(1,1),(1,2),(1,3),(1,4),
(2,4),
(3,4),(3,5),
(4,4),
(5,4),(5,6);

INSERT INTO tab_bancos (id_banco, nom_banco, dir_banco) VALUES
('BANC01','Bancolombia','Calle 30 # 6-23, Bogotá'),
('BANC02','Banco de Bogotá','Carrera 13 # 27-00, Bogotá'),
('BANC03','Davivienda','Avenida El Dorado # 68C-61, Bogotá'),
('BANC04','BBVA','Calle 72 # 10-34, Bogotá'),
('BANC05','Itaú','Avenida Paulista 1234, São Paulo');

INSERT INTO tab_color (id_color, nom_color) VALUES
('#FF0000','Rojo'),('#008000','Verde'),('#0000FF','Azul'),('#FFFF00','Amarillo'),('#800080','Púrpura');

INSERT INTO tab_idiomas (id_idioma, nom_idioma) VALUES
('es','Español'),('en','Inglés'),('fr','Francés'),('pt','Portugués'),('de','Alemán');

INSERT INTO tab_monedas (id_moneda, nom_moneda, simbolo) VALUES
('COP','Peso Colombiano','$'),
('USD','Dólar Estadounidense','US$'),
('GBP','Libra Esterlina','£'),
('EUR','Euro','€'),
('BRL','Real Brasileño','R$');

INSERT INTO tab_users (id_user, mail_user, pass_user, nom_user, ape_user, ult_fec_ingreso) VALUES
(1,'juan.perez@viva.com','hashed_pass123','Juan','Pérez','2025-08-24 05:40:00'),
(2,'maria.lopez@viva.com','hashed_pass456','María','López','2025-08-24 06:00:00'),
(3,'ana.gomez@viva.com','hashed_pass789','Ana','Gómez','2025-08-24 07:00:00'),
(4,'luis.martinez@viva.com','luis_pass101','Luis','Martínez','2025-08-24 08:00:00'),
(5,'clara.sanchez@viva.com','clara_pass102','Clara','Sánchez','2025-08-24 09:00:00'),
(6,'pedro.ramirez@viva.com','pedro_pass103','Pedro','Ramírez','2025-08-24 10:00:00'),
(7,'laura.mendez@viva.com','laura_pass104','Laura','Méndez','2025-08-24 11:00:00'),
(8,'diego.hernandez@viva.com','diego_pass105','Diego','Hernández','2025-08-24 12:00:00'),
(9,'sofia.torres@viva.com','sofia_pass106','Sofía','Torres','2025-08-24 13:00:00'),
(10,'andres.garcia@viva.com','andres_pass107','Andrés','García','2025-08-24 14:00:00');

INSERT INTO tab_menu (id_menu, nom_menu) VALUES
(1,'Gestión Productos'),(2,'Gestión Facturas'),(3,'Gestión Envíos'),(4,'Gestión Usuarios'),(5,'Reportes');

INSERT INTO tab_menu_user (id_user, id_menu) VALUES
(1,1),(1,2),(2,3),(3,1),(4,4),(5,5);

INSERT INTO tab_productores (
  tipo_doc_productor, id_productor, nom_prod, ape_prod, id_user, dir_prod,
  nom_emprend, rut_prod, cam_prod, img_prod, id_pais, id_ciudad, id_grupo,
  id_region, id_banco, id_cuenta_prod
) VALUES
(1,123456789,'Juan','Pérez',1,'Calle 5 # 10-20, Riohacha','Artesanías Wayúu','rut123.pdf','cam123.pdf','logo_wayuu.png',1,1,1,1,'BANC01',9876543210),
(2,987654321,'María','López',2,'Carrera 8 # 15-30, Leticia','Tejidos Emberá','rut456.pdf','cam456.pdf','logo_embera.png',1,3,2,3,'BANC02',1234567890),
(1,456789123,'Ana','Gómez',3,'Vereda Sierra Nevada, Santa Marta','Mochilas Arhuaco','rut789.pdf','cam789.pdf','logo_arhuaco.png',1,2,3,2,'BANC03',4561237890),
(1,111222333,'Luis','Martínez',4,'Calle 10 # 5-15, Medellín','Tejidos Nasa','rut111.pdf','cam111.pdf','logo_nasa.png',1,6,5,2,'BANC04',1112223334),
(2,222333444,'Clara','Sánchez',5,'Carrera 15 # 20-10, Cartagena','Joyas Caribe','rut222.pdf','cam222.pdf','logo_caribe.png',1,7,1,1,'BANC01',2223334445),
(1,333444555,'Pedro','Ramírez',6,'Vereda El Carmen, Bogotá','Cestería Arhuaco','rut333.pdf','cam333.pdf','logo_arhuaco2.png',1,2,3,2,'BANC02',3334445556),
(1,444555666,'Laura','Méndez',7,'Calle 3 # 8-25, Leticia','Artesanías Emberá','rut444.pdf','cam444.pdf','logo_embera2.png',1,3,2,3,'BANC03',4445556667),
(2,555666777,'Diego','Hernández',8,'Carrera 7 # 12-40, Bucaramanga','Mochilas Kankuama','rut555.pdf','cam555.pdf','logo_kankuama.png',1,1,4,2,'BANC01',5556667778),
(1,666777888,'Sofía','Torres',9,'Calle 20 # 10-30, Medellín','Tejidos Wayúu','rut666.pdf','cam666.pdf','logo_wayuu2.png',1,6,1,1,'BANC04',6667778889),
(1,777888999,'Andrés','García',10,'Vereda La Paz, Cartagena','Cerámica Nasa','rut777.pdf','cam777.pdf','logo_nasa2.png',1,7,5,1,'BANC02',7778889990);

INSERT INTO tab_clientes (id_user, tipo_doc_client, id_client, id_pais, id_ciudad, id_idioma, id_moneda) VALUES
(1,1,'CLI001',1,1,'es','COP'),
(2,4,'CLI002',2,4,'en','USD'),
(3,5,'CLI003',3,5,'fr','GBP'),
(4,1,'CLI004',1,6,'es','COP'),
(5,4,'CLI005',2,4,'en','USD'),
(6,1,'CLI006',1,7,'es','COP'),
(7,6,'CLI007',5,8,'pt','BRL');

INSERT INTO tab_oficios (id_oficio, nom_oficio) VALUES
(1,'Cestería'),(2,'Tejido'),(3,'Orfebrería'),(4,'Cerámica'),(5,'Talla en Madera');

INSERT INTO tab_materia_prima (id_materia, nom_materia) VALUES
(1,'Fique'),(2,'Lana'),(3,'Plata'),(4,'Barro'),(5,'Madera');

INSERT INTO tab_categorias (id_categoria, nom_categoria) VALUES
(1,'Accesorios'),(2,'Decoración'),(3,'Ropa'),(4,'Hogar'),(5,'Joyería');

INSERT INTO tab_productos (id_producto, nom_producto, stock, id_categoria, id_color, id_oficio, id_materia) VALUES
(1,'Mochila Wayúu',50,1,'#FF0000',2,1),
(2,'Cesto Emberá',30,2,'#008000',1,1),
(3,'Aretes de Plata',20,5,'#0000FF',3,3),
(4,'Jarrón de Barro',15,4,'#800080',4,4),
(5,'Chal de Lana',25,3,'#FFFF00',2,2),
(6,'Escultura de Madera',10,2,'#800080',5,5);

INSERT INTO tab_producto_productor (id_producto, id_productor, precio_prod, stock_prod, desc_prod_personal, img_personal, activo) VALUES
(1,123456789,150000.00,20,'Mochila tejida a mano por artesanos Wayúu','mochila_wayuu.jpg',TRUE),
(2,987654321, 80000.00,15,'Cesto tradicional Emberá con patrones únicos','cesto_embera.jpg',TRUE),
(3,456789123,120000.00,10,'Aretes de plata con diseño contemporáneo','aretes_plata.jpg',TRUE),
(4,777888999, 95000.00,12,'Jarrón de barro con acabados tradicionales','jarron_barro.jpg',TRUE),
(5,222333444,180000.00,18,'Chal de lana tejido a mano','chal_lana.jpg',TRUE),
(6,555666777,200000.00, 8,'Escultura tallada en madera de roble','escultura_madera.jpg',TRUE);

INSERT INTO tab_transportadoras (id_transportador, nom_transportador, tipo_transporte, tel_contacto, correo_contacto, sitio_web, activo) VALUES
('TRANS01','Interrapidisimo','Nacional','+57 601 555 1234','contacto@interrapidisimo.com','www.interrapidisimo.com',TRUE),
('TRANS02','DHL','Internacional','+1 800 123 4567','contact@dhl.com','www.dhl.com',TRUE),
('TRANS03','Servientrega','Nacional','+57 601 777 8888','info@servientrega.com','www.servientrega.com',TRUE),
('TRANS04','FedEx','Internacional','+1 800 247 4747','support@fedex.com','www.fedex.com',TRUE);

INSERT INTO tab_formas_pago (id_pago, nom_pago) VALUES
('TRF','Transferencia Bancaria'),('TDC','Tarjeta de Crédito'),('PAYPAL','PayPal'),('PSE','PSE');

INSERT INTO tab_transito (id_entrada, id_producto, fec_entrada, val_entrada) VALUES
(1,1,'2025-08-24 10:00:00',20),
(2,2,'2025-08-24 11:00:00',15),
(3,3,'2025-08-24 12:00:00',10),
(4,4,'2025-08-24 13:00:00',12),
(5,5,'2025-08-24 14:00:00',18),
(6,6,'2025-08-24 15:00:00',8);


-- INSERT INTO tab_envios (id_envio, id_factura, fecha_envio, id_transportador, num_guia, estado_envio, direccion_dest, barrio) VALUES
-- (1,1,'2025-08-25','TRANS01','GUIA123456','En tránsito','Calle 20 # 15-30, Bucaramanga','Cabecera'),
-- (2,2,'2025-08-25','TRANS02','DHL789012','Pendiente','123 Main St, Miami, FL','South Beach'),
-- (3,3,'2025-08-25','TRANS02','DHL345678','Pendiente','10 Downing St, London','Westminster'),
-- (4,4,'2025-08-25','TRANS03','SERV123456','En tránsito','Carrera 50 # 20-10, Medellín','Laureles'),
-- (5,5,'2025-08-25','TRANS04','FEDEX789012','Pendiente','456 Ocean Dr, Miami, FL','Miami Beach'),
-- (6,6,'2025-08-25','TRANS01','GUIA789123','En tránsito','Calle 10 # 5-25, Cartagena','Bocagrande'),
-- (7,7,'2025-08-25','TRANS04','FEDEX456789','Pendiente','Rua Augusta 123, São Paulo','Consolação');

INSERT INTO tab_kardex (
  id_kardex, id_producto, id_productor, tipo_movim, cantidad, fecha_movim
) VALUES
(1,1,123456789,TRUE ,20,'2025-08-24 10:00:00'),
(2,2,987654321,TRUE ,15,'2025-08-24 11:00:00'),
(3,3,456789123,FALSE, 1,'2025-08-24 14:30:00'),
(4,4,777888999,TRUE ,12,'2025-08-24 13:00:00'),
(5,5,222333444,FALSE, 1,'2025-08-24 18:00:00'),
(6,6,555666777,TRUE , 8,'2025-08-24 15:00:00'),
(7,6,555666777,FALSE, 1,'2025-08-24 19:00:00');


-- Additional INSERTs for tab_paises
INSERT INTO tab_paises (id_pais, cod_iso, nom_pais, arancel_pct) VALUES
(6, 32, 'Argentina', 11.50),
(7, 152, 'Chile', 7.00),
(8, 484, 'México', 9.50),
(9, 392, 'Japón', 15.00),
(10, 36, 'Australia', 8.50),
(11, 124, 'Canadá', 7.50),
(12, 276, 'Alemania', 10.50),
(13, 724, 'España', 9.00),
(14, 380, 'Italia', 9.50),
(15, 356, 'India', 13.00),
(16, 156, 'China', 14.00),
(17, 604, 'Perú', 8.00),
(18, 218, 'Ecuador', 8.50),
(19, 862, 'Venezuela', 12.50),
(20, 554, 'Nueva Zelanda', 7.00);

-- Additional INSERTs for tab_ciudades
INSERT INTO tab_ciudades (id_ciudad, nom_ciudad, zip_ciudad, id_pais) VALUES
(9, 'Buenos Aires', 'C1001', 6),
(10, 'Córdoba', 'X5000', 6),
(11, 'Santiago', '8320000', 7),
(12, 'Valparaíso', '2340000', 7),
(13, 'Ciudad de México', '06000', 8),
(14, 'Guadalajara', '44100', 8),
(15, 'Tokio', '100-0001', 9),
(16, 'Osaka', '530-0001', 9),
(17, 'Sídney', '2000', 10),
(18, 'Melbourne', '3000', 10),
(19, 'Toronto', 'M5V 2T6', 11),
(20, 'Vancouver', 'V6B 1H7', 11),
(21, 'Berlín', '10115', 12),
(22, 'Múnich', '80331', 12),
(23, 'Madrid', '28001', 13),
(24, 'Barcelona', '08001', 13),
(25, 'Roma', '00100', 14),
(26, 'Milán', '20121', 14),
(27, 'Nueva Delhi', '110001', 15),
(28, 'Mumbai', '400001', 15),
(29, 'Beijing', '100000', 16),
(30, 'Shanghai', '200000', 16),
(31, 'Lima', '15001', 17),
(32, 'Cusco', '08000', 17),
(33, 'Quito', '170135', 18),
(34, 'Guayaquil', '090101', 18),
(35, 'Caracas', '1010', 19),
(36, 'Maracaibo', '4001', 19),
(37, 'Auckland', '1010', 20),
(38, 'Wellington', '6011', 20),
(39, 'Cali', '760001', 1),
(40, 'Barranquilla', '080001', 1),
(41, 'New York', '10001', 2),
(42, 'Los Ángeles', '90001', 2),
(43, 'Manchester', 'M1 1AA', 3),
(44, 'Birmingham', 'B1 1AA', 3),
(45, 'París', '75001', 4),
(46, 'Marsella', '13001', 4),
(47, 'Río de Janeiro', '20000-000', 5),
(48, 'Brasilia', '70000-000', 5);