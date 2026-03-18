-- DROP DE VISTAS
DROP VIEW IF EXISTS bancos_view;
DROP VIEW IF EXISTS grupos_view;
DROP VIEW IF EXISTS departamentos_col_view;
DROP VIEW IF EXISTS tipos_col_view;
DROP VIEW IF EXISTS categorias_view;
DROP VIEW IF EXISTS colores_view;
DROP VIEW IF EXISTS oficios_view;
DROP VIEW IF EXISTS materias_view;
-- DROP DE TABLAS
DROP TABLE IF EXISTS tab_reset_tokens;
DROP TABLE IF EXISTS tab_kardex;
DROP TABLE IF EXISTS tab_clientes;
DROP TABLE IF EXISTS tab_envios;
DROP TABLE IF EXISTS tab_det_fact;
DROP TABLE IF EXISTS tab_enc_fact;
DROP TABLE IF EXISTS tab_transito;
DROP TABLE IF EXISTS tab_formas_pago;
DROP TABLE IF EXISTS tab_transportadoras;
DROP TABLE IF EXISTS tab_carrito;
DROP TABLE IF EXISTS tab_favoritos;
DROP TABLE IF EXISTS tab_resenas;
DROP TABLE IF EXISTS tab_imagenes;
DROP TABLE IF EXISTS tab_productos;
DROP TABLE IF EXISTS tab_categorias;
DROP TABLE IF EXISTS tab_materia_prima;
DROP TABLE IF EXISTS tab_oficios;
DROP TABLE IF EXISTS tab_monedas;
DROP TABLE IF EXISTS tab_idiomas;
DROP TABLE IF EXISTS tab_stand;
DROP TABLE IF EXISTS tab_productores;
DROP TABLE IF EXISTS tab_grupos;
DROP TABLE IF EXISTS tab_ciudades;
DROP TABLE IF EXISTS tab_departamentos;
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
    correo_contacto VARCHAR      NOT NULL,                              -- Correo de contacto
    val_inifact    DECIMAL(12,0) NOT NULL,                              -- Numeración inicial de facturas
    val_finfact    DECIMAL(12,0) NOT NULL CHECK(val_finfact >= val_inifact), -- Numeración final de facturas
    val_actfact    DECIMAL(12,0) NOT NULL CHECK (val_actfact >= val_inifact AND val_actfact <= val_finfact), -- Número actual
    val_observa    TEXT,                                                -- Observación impresa en factura
    visitas        INTEGER       NOT NULL DEFAULT 0,                    -- Visitas a la plataforma
    landing_hero_titulo VARCHAR       NOT NULL DEFAULT 'Conecta con nuestro {mercado real}',
    landing_hero_subtitulo VARCHAR    NOT NULL DEFAULT 'Conoce los productos de naturaleza autoctona y artesanal de Colombia.',
    landing_hero_btn    VARCHAR       NOT NULL DEFAULT 'Explorar ahora',
    landing_conf_1_tit  VARCHAR       NOT NULL DEFAULT 'Envíos seguros',
    landing_conf_1_sub  VARCHAR       NOT NULL DEFAULT 'Entrega protegida en todo el mundo',
    landing_conf_2_tit  VARCHAR       NOT NULL DEFAULT 'Pago protegido',
    landing_conf_2_sub  VARCHAR       NOT NULL DEFAULT 'Transacciones 100% seguras',
    landing_conf_3_tit  VARCHAR       NOT NULL DEFAULT 'Apoyo directo',
    landing_conf_3_sub  VARCHAR       NOT NULL DEFAULT 'Beneficia directamente a las comunidades',
    landing_filosofia_tit VARCHAR     NOT NULL DEFAULT 'Nuestra historia',
    landing_filosofia_p1 VARCHAR      NOT NULL DEFAULT 'VIVA nació del sueño de preservar y compartir la riqueza cultural de las comunidades indígenas colombianas. Creemos que cada artesanía cuenta una historia milenaria y conecta generaciones.',
    landing_filosofia_p2 VARCHAR      NOT NULL DEFAULT 'A través de nuestra plataforma, los artesanos pueden compartir su talento con el mundo, mientras preservamos tradiciones ancestrales y generamos un impacto económico directo en sus comunidades.',
    created_by     VARCHAR       NOT NULL DEFAULT current_user,         -- Usuario que creó
    created_at     TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
    updated_by     VARCHAR NOT NULL DEFAULT 'N/A',                               -- Usuario que modificó
    updated_at     TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT '1900-01-01 00:00:00',     -- Fecha de modificación
    is_deleted     BOOLEAN       NOT NULL DEFAULT FALSE,                -- Indicador de borrado lógico
    PRIMARY KEY(id_parametro)
);

INSERT INTO tab_pmtros (
  nom_plataforma, dir_contacto, correo_contacto,
   val_inifact, val_finfact, val_actfact, val_observa,
  landing_hero_titulo, landing_hero_subtitulo, landing_hero_btn,
  landing_conf_1_tit, landing_conf_1_sub, landing_conf_2_tit, landing_conf_2_sub,
  landing_conf_3_tit, landing_conf_3_sub, landing_filosofia_tit,
  landing_filosofia_p1, landing_filosofia_p2
) VALUES (
  'Viva',
  'Calle 100 # 15-20, Bogotá',
  'contacto@artesaniasviva.com',
  1000,
  9000,
  1000,
  'Parámetros iniciales de facturación',
  'Conecta con nuestro {mercado real}',
  'Conoce los productos de naturaleza autoctona y artesanal de Colombia.',
  'Explorar ahora',
  'Envíos seguros',
  'Entrega protegida en todo el mundo',
  'Pago protegido',
  'Transacciones 100% seguras',
  'Apoyo directo',
  'Beneficia directamente a las comunidades',
  'Nuestra historia',
  'VIVA nació del sueño de preservar y compartir la riqueza cultural de las comunidades indígenas colombianas. Creemos que cada artesanía cuenta una historia milenaria y conecta generaciones.',
  'A través de nuestra plataforma, los artesanos pueden compartir su talento con el mundo, mientras preservamos tradiciones ancestrales y generamos un impacto económico directo en sus comunidades.'
);

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS tab_users (
    id_user         INTEGER   NOT NULL,                                         -- Identificador del usuario
    mail_user       VARCHAR   NOT NULL,                                         -- Correo del usuario
    pass_user       VARCHAR   NOT NULL,                                         -- Contraseña (hash)
    nom_user        VARCHAR   NOT NULL,                                         -- Nombres del usuario
    ape_user        VARCHAR   NOT NULL,                                         -- Apellidos del usuario
    foto_user       VARCHAR   DEFAULT ('images/profiles/default.webp'),         -- Foto del usuario
    ult_fec_ingreso TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Último ingreso
    created_by      VARCHAR   NOT NULL DEFAULT current_user,             -- Usuario que creó
    created_at      TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
    updated_by      VARCHAR NOT NULL DEFAULT 'N/A',                               -- Usuario que modificó
    updated_at      TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT '1900-01-01 00:00:00',     -- Fecha de modificación
    is_deleted      BOOLEAN   NOT NULL DEFAULT FALSE,                    -- Borrado lógico
    PRIMARY KEY (id_user)
);

-- Tabla de menú
CREATE TABLE IF NOT EXISTS tab_menu
(
    id_menu          INTEGER            NOT NULL,                                       -- Identificador del menú
    nom_menu         VARCHAR            NOT NULL,                                       -- Nombre del menú
    url_menu         VARCHAR            DEFAULT '#',                                    -- URL o ruta de acceso
    icono_menu       VARCHAR            DEFAULT 'fas fa-circle',                        -- Clase CSS del icono (FontAwesome)
    created_by       VARCHAR            NOT NULL DEFAULT current_user,                  -- Usuario que creó
    created_at       TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,    -- Fecha de creación
    updated_by       VARCHAR NOT NULL DEFAULT 'N/A',                                  -- Usuario que modificó
    updated_at       TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT '1900-01-01 00:00:00',         -- Fecha de modificación
    is_deleted       BOOLEAN  NOT NULL DEFAULT FALSE,                                   -- Borrado lógico
    PRIMARY KEY(id_menu)
);

-- Tabla de menú por usuario
CREATE TABLE IF NOT EXISTS tab_menu_user
(
    id_user          INTEGER            NOT NULL,                                       -- Usuario
    id_menu          INTEGER            NOT NULL,                                       -- Menú asignado
    created_by       VARCHAR            NOT NULL DEFAULT current_user,                  -- Usuario que creó
    created_at       TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,    -- Fecha de creación
    updated_by       VARCHAR NOT NULL DEFAULT 'N/A',                                  -- Usuario que modificó
    updated_at       TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT '1900-01-01 00:00:00',         -- Fecha de modificación
    is_deleted       BOOLEAN            NOT NULL DEFAULT FALSE,                         -- Borrado lógico
    PRIMARY KEY(id_user,id_menu),
    FOREIGN KEY(id_user) REFERENCES tab_users(id_user),
    FOREIGN KEY(id_menu) REFERENCES tab_menu(id_menu)
);

-- Tabla de bancos
CREATE TABLE IF NOT EXISTS tab_bancos
(
    id_banco            DECIMAL(2,0)        NOT NULL,                                       -- Código del banco
    nom_banco           VARCHAR             NOT NULL,                                       -- Nombre del banco
    created_by          VARCHAR             NOT NULL DEFAULT current_user,                  -- Usuario que creó
    created_at          TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
    updated_by          VARCHAR NOT NULL DEFAULT 'N/A',                                  -- Usuario que modificó
    updated_at          TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT '1900-01-01 00:00:00',         -- Fecha de modificación
    is_deleted          BOOLEAN             NOT NULL DEFAULT FALSE,                         -- Borrado lógico
    PRIMARY KEY(id_banco)
);

-- Tabla de colores
CREATE TABLE IF NOT EXISTS tab_color
(
    id_color            VARCHAR             NOT NULL,                                       -- Código de color (hex RGB)
    nom_color           VARCHAR             NOT NULL,                                       -- Nombre del color
    created_by          VARCHAR             NOT NULL DEFAULT current_user,                  -- Usuario que creó
    created_at          TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
    updated_by          VARCHAR NOT NULL DEFAULT 'N/A',                                  -- Usuario que modificó
    updated_at          TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT '1900-01-01 00:00:00',         -- Fecha de modificación
    is_deleted          BOOLEAN             NOT NULL DEFAULT FALSE,                         -- Borrado lógico
    PRIMARY KEY(id_color)
);

-- Tabla de tipos de documento
CREATE TABLE IF NOT EXISTS tab_tipos_doc
(
    id_tipo_doc             DECIMAL(1,0)        NOT NULL,                                       -- Identificador del tipo de documento
    nom_tipo_doc            VARCHAR             NOT NULL,                                       -- Nombre/código del tipo de documento (CC, PP, etc.)
    created_by              VARCHAR             NOT NULL DEFAULT current_user,                  -- Usuario que creó
    created_at              TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
    updated_by              VARCHAR NOT NULL DEFAULT 'N/A',                                  -- Usuario que modificó
    updated_at              TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT '1900-01-01 00:00:00',         -- Fecha de modificación
    is_deleted              BOOLEAN             NOT NULL DEFAULT FALSE,                         -- Borrado lógico
    PRIMARY KEY(id_tipo_doc)
);

-- Tabla de países
CREATE TABLE IF NOT EXISTS tab_paises
(
    id_pais                 DECIMAL(3,0)        NOT NULL,                                       -- Identificador del país
    cod_iso                 DECIMAL(3,0)        NOT NULL,                                       -- Código ISO numérico
    nom_pais                VARCHAR             NOT NULL,                                       -- Nombre del país
    arancel_pct             DECIMAL(5,2)        NOT NULL DEFAULT 0.00,                          -- % arancel desde Colombia
    created_by              VARCHAR             NOT NULL DEFAULT current_user,                  -- Usuario que creó
    created_at              TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
    updated_by              VARCHAR NOT NULL DEFAULT 'N/A',                                  -- Usuario que modificó
    updated_at              TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT '1900-01-01 00:00:00',         -- Fecha de modificación
    is_deleted              BOOLEAN             NOT NULL DEFAULT FALSE,                         -- Borrado lógico
    PRIMARY KEY(id_pais)
);

-- Tabla de regiones
CREATE TABLE IF NOT EXISTS tab_departamentos
(
    id_pais                 DECIMAL(3,0)        NOT NULL,                                  -- Identificador del país
    id_departamento         DECIMAL(2,0)        NOT NULL,                                   -- Identificador de la región
    nom_departamento        VARCHAR(50)         NOT NULL,                                   -- Nombre de la región
    created_by              VARCHAR             NOT NULL            DEFAULT current_user,              -- Usuario que creó
    created_at              TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
    updated_by              VARCHAR             NOT NULL DEFAULT 'N/A',           -- Usuario que modificó
    updated_at              TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT '1900-01-01 00:00:00', -- Fecha de modificación
    is_deleted              BOOLEAN             NOT NULL            DEFAULT FALSE,                     -- Borrado lógico
    PRIMARY KEY(id_pais,id_departamento),
    FOREIGN KEY(id_pais) REFERENCES tab_paises(id_pais)
);
-- Tabla de ciudades
CREATE TABLE IF NOT EXISTS tab_ciudades
(
    id_pais                 DECIMAL(3,0)        NOT NULL,                                   -- Identificador del país
    id_departamento         DECIMAL(2,0)        NOT NULL,                                   -- Identificador de la región
    id_ciudad               DECIMAL(5,0)        NOT NULL,                                   -- Identificador de la ciudad
    nom_ciudad              VARCHAR(50)         NOT NULL,                                   -- Nombre de la ciudad
    zip_ciudad              VARCHAR(10)         NOT NULL,                                   -- Código postal
    created_by              VARCHAR             NOT NULL            DEFAULT current_user,              -- Usuario que creó
    created_at              TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
    updated_by              VARCHAR             NOT NULL DEFAULT 'N/A',           -- Usuario que modificó
    updated_at              TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT '1900-01-01 00:00:00', -- Fecha de modificación
    is_deleted BOOLEAN      NOT NULL DEFAULT FALSE,                     -- Borrado lógico
    PRIMARY KEY(id_pais,id_departamento,id_ciudad),
    FOREIGN KEY(id_pais,id_departamento) REFERENCES tab_departamentos(id_pais,id_departamento),
    FOREIGN KEY(id_pais) REFERENCES tab_paises(id_pais)
);

-- Tabla de grupos poblacionales
CREATE TABLE IF NOT EXISTS tab_grupos
(
    id_grupo                DECIMAL(12,0)       NOT NULL,                                   -- Identificador del grupo
    nom_grupo               VARCHAR             NOT NULL,                                   -- Nombre del grupo
    created_by              VARCHAR             NOT NULL DEFAULT current_user,              -- Usuario que creó
    created_at              TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
    updated_by              VARCHAR NOT NULL DEFAULT 'N/A',                                  -- Usuario que modificó
    updated_at              TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT '1900-01-01 00:00:00',         -- Fecha de modificación
    is_deleted              BOOLEAN             NOT NULL DEFAULT FALSE,                     -- Borrado lógico
    PRIMARY KEY(id_grupo)
);
-- Tabla de productores
CREATE TABLE IF NOT EXISTS tab_productores
(
    id_tipo_doc		   DECIMAL(1,0)         NOT NULL,                            -- Tipo de documento del productor
    id_productor       DECIMAL(10,0)        NOT NULL CHECK (id_productor BETWEEN 1 AND 9999999999), -- Número de documento
    id_user            INTEGER              NOT NULL,                            -- Usuario asociado en la plataforma
    dir_prod           VARCHAR              NOT NULL,                            -- Dirección del productor
    id_pais            DECIMAL(3,0)         NOT NULL DEFAULT 1,                  -- País del productor
    id_ciudad          DECIMAL(5,0)         NOT NULL,                            -- Ciudad del productor
    id_departamento    DECIMAL(2,0)         NOT NULL,                            -- Departamento del país
    id_grupo           DECIMAL(12,0)        NOT NULL,                            -- Grupo poblacional
    id_banco           DECIMAL(2,0)         NOT NULL,                            -- Banco del productor
    id_cuenta_prod     DECIMAL(20,0)        NOT NULL,                            -- Número de cuenta bancaria
    tipo_cuenta        DECIMAL(1,0)         NOT NULL,                            -- 1. Ahorros 2. Corriente
    created_by         VARCHAR              NOT NULL DEFAULT current_user,       -- Usuario que creó
    created_at         TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
    updated_by         VARCHAR NOT NULL DEFAULT 'N/A',                       -- Usuario que modificó
    updated_at         TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT '1900-01-01 00:00:00',-- Fecha de modificación
    is_deleted         BOOLEAN              NOT NULL DEFAULT FALSE,              -- Borrado lógico
    PRIMARY KEY(id_productor),
    FOREIGN KEY(id_tipo_doc) 						REFERENCES tab_tipos_doc(id_tipo_doc),
    FOREIGN KEY(id_pais)    						REFERENCES tab_paises(id_pais),
    FOREIGN KEY(id_user)           					REFERENCES tab_users(id_user),
	FOREIGN KEY(id_pais,id_departamento) 			REFERENCES tab_departamentos(id_pais,id_departamento),
    FOREIGN KEY(id_pais,id_departamento,id_ciudad)  REFERENCES tab_ciudades(id_pais,id_departamento,id_ciudad),
    FOREIGN KEY(id_grupo)          					REFERENCES tab_grupos(id_grupo),
    FOREIGN KEY(id_banco)          					REFERENCES tab_bancos(id_banco)
);

CREATE TABLE IF NOT EXISTS tab_stand
(
    id_productor            DECIMAL(10,0)                   NOT NULL,
    id_stand                DECIMAL(10,0)                   NOT NULL,
    nom_stand               VARCHAR                         NOT NULL,
    slogan_stand            VARCHAR                         NOT NULL,
    descripcion_stand       TEXT                            NOT NULL,
    img_stand               VARCHAR                         NOT NULL,
    portada_stand           VARCHAR                         NOT NULL,
    created_by              VARCHAR                         NOT NULL DEFAULT current_user,
    created_at              TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by              VARCHAR NOT NULL DEFAULT 'N/A',
    updated_at              TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT '1900-01-01 00:00:00',
    is_deleted              BOOLEAN                         NOT NULL DEFAULT FALSE,
    PRIMARY KEY(id_productor,id_stand),
    FOREIGN KEY(id_productor) REFERENCES tab_productores(id_productor)   
);

-- Tabla de idiomas
CREATE TABLE IF NOT EXISTS tab_idiomas
(
    id_idioma               VARCHAR             NOT NULL,                                         -- Código de idioma (ISO 639-1)
    nom_idioma              VARCHAR             NOT NULL,                                         -- Nombre del idioma
    created_by              VARCHAR             NOT NULL DEFAULT current_user,                    -- Usuario que creó
    created_at              TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
    updated_by              VARCHAR NOT NULL DEFAULT 'N/A',                                                  -- Usuario que modificó
    updated_at              TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT '1900-01-01 00:00:00',                              -- Fecha de modificación
    is_deleted              BOOLEAN             NOT NULL DEFAULT FALSE,                           -- Borrado lógico
    PRIMARY KEY(id_idioma)
);

-- Tabla de monedas
CREATE TABLE IF NOT EXISTS tab_monedas
(
    id_moneda               VARCHAR             NOT NULL,                                         -- Código de moneda (ISO 4217)
    nom_moneda              VARCHAR             NOT NULL,                                         -- Nombre de la moneda
    simbolo                 VARCHAR             NOT NULL,                                         -- Símbolo de la moneda
    created_by              VARCHAR             NOT NULL DEFAULT current_user,                    -- Usuario que creó
    created_at              TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
    updated_by              VARCHAR NOT NULL DEFAULT 'N/A',                                                  -- Usuario que modificó
    updated_at              TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT '1900-01-01 00:00:00',                              -- Fecha de modificación
    is_deleted              BOOLEAN             NOT NULL DEFAULT FALSE,                           -- Borrado lógico
    PRIMARY KEY(id_moneda)
);

-- Tabla de oficios
CREATE TABLE IF NOT EXISTS tab_oficios
(
    id_oficio               DECIMAL(12,0)       NOT NULL,                                   -- Identificador del oficio
    nom_oficio              VARCHAR             NOT NULL,                                   -- Nombre del oficio
    created_by              VARCHAR             NOT NULL DEFAULT current_user,              -- Usuario que creó
    created_at              TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
    updated_by              VARCHAR NOT NULL DEFAULT 'N/A',                                                  -- Usuario que modificó
    updated_at              TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT '1900-01-01 00:00:00',                              -- Fecha de modificación
    is_deleted              BOOLEAN             NOT NULL DEFAULT FALSE,                     -- Borrado lógico
    PRIMARY KEY(id_oficio)
);

-- Tabla de materia prima
CREATE TABLE IF NOT EXISTS tab_materia_prima
(
    id_materia              DECIMAL(12,0)       NOT NULL,                                  -- Identificador de la materia prima
    nom_materia             VARCHAR             NOT NULL,                                   -- Nombre de la materia prima
    created_by              VARCHAR             NOT NULL DEFAULT current_user,              -- Usuario que creó
    created_at              TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
    updated_by              VARCHAR NOT NULL DEFAULT 'N/A',                                                  -- Usuario que modificó
    updated_at              TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT '1900-01-01 00:00:00',                              -- Fecha de modificación
    is_deleted              BOOLEAN             NOT NULL DEFAULT FALSE,                     -- Borrado lógico
    PRIMARY KEY(id_materia)
);

-- Tabla de categorías
CREATE TABLE IF NOT EXISTS tab_categorias
(
    id_categoria            DECIMAL(12,0)       NOT NULL,                                  -- Identificador de la categoría
    nom_categoria           VARCHAR             NOT NULL,                                   -- Nombre de la categoría
    img_cat                 VARCHAR             NOT NULL DEFAULT 'images/default_category.webp', -- Imagen de la categoría
    created_by              VARCHAR             NOT NULL DEFAULT current_user,              -- Usuario que creó
    created_at              TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
    updated_by              VARCHAR NOT NULL DEFAULT 'N/A',                                                 -- Usuario que modificó
    updated_at              TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT '1900-01-01 00:00:00',                             -- Fecha de modificación
    is_deleted              BOOLEAN             NOT NULL DEFAULT FALSE,                     -- Borrado lógico
    PRIMARY KEY(id_categoria)
);

-- Tabla de productos
CREATE TABLE IF NOT EXISTS tab_productos
(
    id_productor            DECIMAL(10,0)                       NOT NULL,                                  -- Identificador del productor
    id_producto             DECIMAL(12,0)                       NOT NULL,                                  -- Identificador del producto
    nom_producto            VARCHAR                             NOT NULL,                                   -- Nombre del producto
    stock_productor         DECIMAL(12,0)                       NOT NULL,                                   -- Stock disponible
    id_categoria            DECIMAL(12,0)                       NOT NULL,                                   -- Categoría del producto
    id_color                VARCHAR                             NOT NULL,                                   -- Color principal
    id_oficio               DECIMAL(12,0)                       NOT NULL,                                   -- Oficio asociado
    id_materia              DECIMAL(12,0)                       NOT NULL,                                   -- Materia prima principal
    precio_producto         DECIMAL(12,2)                       NOT NULL,
    descripcion_producto    TEXT                                NOT NULL,                                   -- Precio del producto
    is_active               BOOLEAN                             NOT NULL DEFAULT TRUE,                      -- Visibilidad
    vistas                  INTEGER                             NOT NULL DEFAULT 0,                         -- Estadísticas de visualización
    created_by              VARCHAR                             NOT NULL DEFAULT current_user,              -- Usuario que creó
    created_at              TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,         -- Fecha de creación
    updated_by              VARCHAR NOT NULL DEFAULT 'N/A',                                                                        -- Usuario que modificó
    updated_at              TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT '1900-01-01 00:00:00',                                                    -- Fecha de modificación
    is_deleted              BOOLEAN                             NOT NULL DEFAULT FALSE,                     -- Borrado lógico
    CONSTRAINT chk_pp_stock_activo CHECK (
        (stock_productor > 0 AND is_active = TRUE) OR
        (stock_productor = 0 AND is_active = FALSE)
    ),
    PRIMARY KEY(id_producto),
    FOREIGN KEY(id_categoria) REFERENCES tab_categorias(id_categoria),
    FOREIGN KEY(id_color)     REFERENCES tab_color(id_color),
    FOREIGN KEY(id_oficio)    REFERENCES tab_oficios(id_oficio),
    FOREIGN KEY(id_materia)   REFERENCES tab_materia_prima(id_materia),
	FOREIGN KEY(id_productor) REFERENCES tab_productores(id_productor)
);
-- Tabla de imágenes para productos
CREATE TABLE IF NOT EXISTS tab_imagenes
(
        id_producto             DECIMAL(12,0)                       NOT NULL,                                  -- Identificador del producto
        id_imagen               DECIMAL(12,0)                       NOT NULL,
        url_imagen              VARCHAR(255)                        NOT NULL,
        created_by              VARCHAR                             NOT NULL DEFAULT current_user,              -- Usuario que creó
        created_at              TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,         -- Fecha de creación
        updated_by              VARCHAR NOT NULL DEFAULT 'N/A',                                                  -- Usuario que modificó
        updated_at              TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT '1900-01-01 00:00:00',                              -- Fecha de modificación
        is_deleted              BOOLEAN                             NOT NULL DEFAULT FALSE,                     -- Borrado lógico
        
        PRIMARY KEY(id_producto, id_imagen),
        FOREIGN KEY(id_producto) REFERENCES tab_productos(id_producto)        
);
-- Tabla de carrito
CREATE TABLE IF NOT EXISTS tab_carrito(
    id_user                 INTEGER         NOT NULL,                           -- Usuario dueño del carrito
    id_producto             DECIMAL(12,0)   NOT NULL,                           -- Producto en el carrito
    cantidad                INTEGER         NOT NULL CHECK(cantidad > 0),       -- Cantidad (mínimo 1)
    agregado_at             TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de agregado
    created_by              VARCHAR         NOT NULL DEFAULT current_user,      -- Usuario que creó
    created_at              TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
    updated_by              VARCHAR NOT NULL DEFAULT 'N/A',                      -- Usuario que modificó
    updated_at              TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT '1900-01-01 00:00:00',      -- Fecha de modificación
    is_deleted              BOOLEAN         NOT NULL DEFAULT FALSE,             -- Borrado lógico
    PRIMARY KEY(id_user, id_producto),
    FOREIGN KEY(id_user)                    REFERENCES tab_users(id_user),
    FOREIGN KEY(id_producto)                REFERENCES tab_productos(id_producto)
);
-- Tabla de favoritos
CREATE TABLE IF NOT EXISTS tab_favoritos(
    id_user                 INTEGER         NOT NULL,
    id_producto             INTEGER         NOT NULL,
    created_by              VARCHAR         NOT NULL DEFAULT current_user,
    created_at              TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by              VARCHAR NOT NULL DEFAULT 'N/A',
    updated_at              TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT '1900-01-01 00:00:00',
    is_deleted              BOOLEAN         NOT NULL DEFAULT FALSE,
    PRIMARY KEY(id_user, id_producto),
    FOREIGN KEY(id_user)                    REFERENCES tab_users(id_user),
    FOREIGN KEY(id_producto)                REFERENCES tab_productos(id_producto)
);

-- Tabla de reseñas de productos
CREATE TABLE IF NOT EXISTS tab_resenas(
    id_user                     INTEGER         NOT NULL,
    id_producto                 DECIMAL(12,0)   NOT NULL,
    calificacion                INTEGER         NOT NULL CHECK (calificacion >= 1 AND calificacion <= 5),
    texto_resena                TEXT            NOT NULL CHECK (LENGTH(TRIM(texto_resena)) > 0),
    created_by                  VARCHAR         NOT NULL DEFAULT current_user,
    created_at                  TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by                  VARCHAR NOT NULL DEFAULT 'N/A',
    updated_at                  TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT '1900-01-01 00:00:00',
    is_deleted                  BOOLEAN         NOT NULL DEFAULT FALSE,
    PRIMARY KEY(id_user, id_producto),
    FOREIGN KEY(id_user)                    REFERENCES tab_users(id_user),
    FOREIGN KEY(id_producto)                REFERENCES tab_productos(id_producto)
);
-- Tabla de transportadoras
CREATE TABLE IF NOT EXISTS tab_transportadoras
(
    id_transportador        VARCHAR         NOT NULL,                                    -- Código de la transportadora
    nom_transportador       VARCHAR         NOT NULL,                                    -- Nombre de la transportadora
    tipo_transporte         VARCHAR         NOT NULL,                                    -- Tipo (Nacional/Internacional/Mixto)
    tel_contacto            VARCHAR         NOT NULL,                                    -- Teléfono de contacto
    correo_contacto         VARCHAR         NOT NULL,                                    -- Correo de contacto
    sitio_web               VARCHAR         NOT NULL,                                    -- Sitio web
    activo                  BOOLEAN         NOT NULL DEFAULT TRUE,                       -- Estado activo
    created_by              VARCHAR         NOT NULL DEFAULT current_user,               -- Usuario que creó
    created_at              TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
    updated_by              VARCHAR   NOT NULL DEFAULT 'N/A',                                             -- Usuario que modificó
    updated_at              TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT '1900-01-01 00:00:00',                         -- Fecha de modificación
    is_deleted              BOOLEAN   NOT NULL DEFAULT FALSE,                      -- Borrado lógico
    PRIMARY KEY(id_transportador)
);

-- Tabla de formas de pago
CREATE TABLE IF NOT EXISTS tab_formas_pago
(
    id_pago             VARCHAR         NOT NULL,                                           -- Código de la forma de pago
    nom_pago            VARCHAR         NOT NULL,                                           -- Nombre de la forma de pago
    created_by          VARCHAR         NOT NULL DEFAULT current_user,                      -- Usuario que creó
    created_at          TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
    updated_by          VARCHAR         NOT NULL DEFAULT 'N/A',                                                    -- Usuario que modificó
    updated_at          TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT '1900-01-01 00:00:00',                                -- Fecha de modificación
    is_deleted          BOOLEAN         NOT NULL DEFAULT FALSE,                             -- Borrado lógico
    PRIMARY KEY(id_pago)
);

-- Tabla de tránsito de inventario
CREATE TABLE IF NOT EXISTS tab_transito
(
    id_entrada          DECIMAL(12,0)           NOT NULL,                                   -- Identificador de la entrada
    id_producto         DECIMAL(12,0)           NOT NULL,                                   -- Producto en tránsito
    fec_entrada         TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),        -- Fecha/hora de entrada
    val_entrada         DECIMAL(4,0)            NOT NULL,                                   -- Cantidad de entrada
    fec_salida          TIMESTAMP WITHOUT TIME ZONE,                               -- Fecha/hora de salida
    created_by          VARCHAR                 NOT NULL DEFAULT current_user,               -- Usuario que creó
    created_at          TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
    updated_by          VARCHAR       NOT NULL DEFAULT 'N/A',                                                   -- Usuario que modificó
    updated_at          TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT '1900-01-01 00:00:00',                               -- Fecha de modificación
    is_deleted          BOOLEAN       NOT NULL DEFAULT FALSE,                      -- Borrado lógico
    PRIMARY KEY(id_entrada),
    FOREIGN KEY(id_producto) REFERENCES tab_productos(id_producto)
);

-- -----------------------------------------------------------------------------
-- Tabla de clientes compradores
-- Almacena información de envío/facturación del usuario que realiza la compra.
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS tab_clientes (
    id_user         INTEGER       NOT NULL,            --tab_users.id_user
    id_client       VARCHAR(20)   NOT NULL,            -- Documento del cliente  
    nom_client      VARCHAR       NOT NULL,            -- Nombre completo
    mail_client     VARCHAR       NOT NULL,            -- Email
    tel_client      VARCHAR(20)   DEFAULT '123',                       -- Teléfono (de ePayco)
    id_tipo_doc     DECIMAL(1,0)  DEFAULT 1,                     -- Tipo de documento (de ePayco)
    nro_doc         VARCHAR(20)   DEFAULT '123',                       -- Número de documento (de ePayco)
    -- Dirección de envío capturada en el formulario del checkout
    id_pais         DECIMAL(3,0)  NOT NULL DEFAULT 1,  -- Colombia por defecto
    id_departamento DECIMAL(2,0)  NOT NULL,
    id_ciudad       DECIMAL(5,0)  NOT NULL,
    dir_envio       VARCHAR       NOT NULL,            -- Ej: "Calle 10 # 5-20 Apto 301"
    barrio_envio    VARCHAR       DEFAULT 'N/A',                           -- Barrio (opcional)
    -- Último pago ePayco (se sobreescribe en cada compra del mismo cliente)
    epayco_ref      VARCHAR       DEFAULT 'N/A',                           -- x_ref_payco
    epayco_txn_id   VARCHAR       DEFAULT 'N/A',                           -- x_transaction_id
    epayco_banco    VARCHAR       DEFAULT 'N/A',                           -- x_bank_name
    epayco_cod_resp DECIMAL(1,0)  DEFAULT 1,                      -- 1=Aceptada|2=Rechazada|3=Pendiente|4=Fallida
    -- Auditoría
    created_by    VARCHAR       NOT NULL DEFAULT current_user,
    created_at    TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by    VARCHAR NOT NULL DEFAULT 'N/A',
    updated_at    TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT '1900-01-01 00:00:00',
    is_deleted    BOOLEAN       NOT NULL DEFAULT FALSE,
    -- Restricciones
    PRIMARY KEY (id_client),
    FOREIGN KEY (id_user)                              REFERENCES tab_users(id_user),
    FOREIGN KEY (id_pais, id_departamento, id_ciudad)    REFERENCES tab_ciudades(id_pais, id_departamento, id_ciudad),
    FOREIGN KEY (id_tipo_doc)                            REFERENCES tab_tipos_doc(id_tipo_doc)
);
CREATE INDEX IF NOT EXISTS idx_clientes_mail   ON tab_clientes(mail_client);
CREATE INDEX IF NOT EXISTS idx_clientes_ciudad ON tab_clientes(id_pais, id_ciudad);
-- -----------------------------------------------------------------------------
-- Tabla de encabezado de factura
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS tab_enc_fact
(
    id_factura              DECIMAL(7,0)  NOT NULL CHECK(id_factura >= 1), -- Número de factura
    fec_factura             DATE          NOT NULL,                         -- Fecha de emisión
    val_hora_fact           TIME WITHOUT TIME ZONE NOT NULL,                -- Hora de emisión
    id_client               VARCHAR       NOT NULL,                         -- Cliente (id_user como texto)
    id_pais                 DECIMAL(3,0)  NOT NULL,                         -- País destino
    id_departamento         DECIMAL(2,0)  NOT NULL,                         -- Departamento destino
    id_ciudad               DECIMAL(5,0)  NOT NULL,                         -- Ciudad destino
    dir_envio               VARCHAR,                                        -- Dirección de envío (texto libre)
    val_tot_fact            DECIMAL(12,2) NOT NULL,                         -- Total factura
    ind_estado              BOOLEAN       NOT NULL,                         -- Activa/Anulada
    id_pago                 VARCHAR       NOT NULL,                         -- Forma de pago
    -- Datos de trazabilidad ePayco
    epayco_ref              VARCHAR,                                        -- x_ref_payco
    epayco_txn_id           VARCHAR,                                        -- x_transaction_id
    epayco_estado           VARCHAR,                                        -- "Aceptada" / "Rechazada" / "Pendiente"
    -- Auditoría
    created_by              VARCHAR       NOT NULL DEFAULT current_user,
    created_at              TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by              VARCHAR       NOT NULL DEFAULT 'N/A',
    updated_at              TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT '1900-01-01 00:00:00',
    is_deleted              BOOLEAN       NOT NULL DEFAULT FALSE,
    PRIMARY KEY(id_factura),
    FOREIGN KEY(id_pago)     REFERENCES tab_formas_pago(id_pago),
    FOREIGN KEY(id_pais)     REFERENCES tab_paises(id_pais),
    FOREIGN KEY(id_pais, id_departamento, id_ciudad)
                             REFERENCES tab_ciudades(id_pais, id_departamento, id_ciudad)
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
    updated_by    VARCHAR NOT NULL DEFAULT 'N/A',                                                  -- Usuario que modificó
    updated_at    TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT '1900-01-01 00:00:00',                              -- Fecha de modificación
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
    created_by       VARCHAR       NOT NULL DEFAULT current_user,           -- Usuario que creó
    created_at       TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
    updated_by       VARCHAR NOT NULL DEFAULT 'N/A',                                              -- Usuario que modificó
    updated_at       TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT '1900-01-01 00:00:00',                          -- Fecha de modificación
    is_deleted       BOOLEAN       NOT NULL DEFAULT FALSE,                 -- Borrado lógico
    PRIMARY KEY(id_envio),
    FOREIGN KEY(id_factura)       REFERENCES tab_enc_fact(id_factura),
    FOREIGN KEY(id_transportador) REFERENCES tab_transportadoras(id_transportador)
);

-- Tabla de kardex
CREATE TABLE IF NOT EXISTS tab_kardex
(
    id_kardex    SERIAL        NOT NULL,                                    -- PK autoincrementable
    id_producto  DECIMAL(7,0)  NOT NULL,                                   -- Producto afectado
    id_productor DECIMAL(10,0) NOT NULL,                                   -- Productor relacionado
    tipo_movim   BOOLEAN       NOT NULL,                                   -- TRUE=entrada / FALSE=salida (venta)
    cantidad     DECIMAL(7,0)  NOT NULL CHECK(cantidad >= 1),              -- Cantidad del movimiento
    ref_factura  DECIMAL(7,0),                                             -- FK trazabilidad → tab_enc_fact
    created_at   TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by   VARCHAR       NOT NULL DEFAULT current_user,
    is_deleted   BOOLEAN       NOT NULL DEFAULT FALSE,
    PRIMARY KEY(id_kardex),
    FOREIGN KEY(id_producto)  REFERENCES tab_productos  (id_producto),
    FOREIGN KEY(id_productor) REFERENCES tab_productores(id_productor),
    FOREIGN KEY(ref_factura)  REFERENCES tab_enc_fact   (id_factura)
);

-- Tabla de tokens de recuperación de contraseña (OTP)
CREATE TABLE IF NOT EXISTS tab_reset_tokens
(
    id_token     INTEGER       NOT NULL,                                         -- PK (asigna por Max+1)
    id_user      INTEGER       NOT NULL,                                         -- Usuario que solicitó el reset
    token_reset  VARCHAR(6)    NOT NULL,                                         -- Código OTP de 6 dígitos
    expira_at    TIMESTAMP WITHOUT TIME ZONE NOT NULL,                           -- Fecha/hora de expiración
    is_used      BOOLEAN       NOT NULL DEFAULT FALSE,                           -- Si ya fue utilizado
    created_at   TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación
    PRIMARY KEY(id_token),
    FOREIGN KEY(id_user) REFERENCES tab_users(id_user)
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
CREATE INDEX idx_kardex_prod ON tab_kardex (id_producto, id_productor);
CREATE INDEX idx_productor_user ON tab_productores(id_user);
CREATE INDEX idx_envios_factura ON tab_envios(id_factura);
CREATE INDEX idx_envios_transportador ON tab_envios(id_transportador);
CREATE INDEX idx_transito_producto ON tab_transito(id_producto);
CREATE INDEX idx_menu_user_user ON tab_menu_user(id_user);
CREATE INDEX idx_menu_user_menu ON tab_menu_user(id_menu);
CREATE INDEX idx_idiomas_nom ON tab_idiomas(nom_idioma);
CREATE INDEX idx_monedas_nom ON tab_monedas(nom_moneda);

-- INSERTS
INSERT INTO tab_tipos_doc (id_tipo_doc, nom_tipo_doc) VALUES
(1,'Cédula'),(2,'NIT'),(3,'Cédula de Extranjería'),(4,'Pasaporte');

-- Insertando los menús del sistema
INSERT INTO tab_menu (id_menu, nom_menu, url_menu, icono_menu) VALUES
(1, 'Inicio', '', 'fas fa-home'),
(2, 'Categorías', '#categorias', 'fas fa-th-large'),
(3, 'Catálogo', 'catalogo', 'fas fa-shopping-bag'),
(4, 'Mi Perfil', 'perfil#profile', 'fas fa-user'),
(5, 'Mis Pedidos', 'perfil#orders', 'fas fa-shopping-bag'),
(6, 'Favoritos', 'perfil#favorites', 'fas fa-heart'),
(7, 'Configuración', 'perfil#settings', 'fas fa-cog'),
(8, 'Admin Dashboard', 'admin_dashboard', 'fas fa-shield-alt'),
(9, 'Vender en VIVA', 'vender', 'fas fa-store'),
(10, 'Mis Productos', 'mis_productos', 'fas fa-box'),
(11, 'Mi Stand', 'mi_stand', 'fas fa-store-alt');

-- Insertando los gremios/grupos de identidad para el marketplace VIVA
INSERT INTO tab_grupos (id_grupo, nom_grupo) VALUES 
(1, 'Pueblos Indígenas de Colombia'),
(2, 'Comunidades Afrodescendientes y Palenqueras'),
(3, 'Artesanos de Tradición Campesina'),
(4, 'Maestros de Oficio y Joyería Tradicional'),
(5, 'Productores de Medicina y Cosmética Natural'),
(6, 'Asociaciones de Mujeres Tejedoras'),
(7, 'Cultivadores Agroecológicos y Orgánicos'),
(8, 'Artesanos Contemporáneos y de Diseño'),
(9, 'Comunidades Rurales y Colonas'),
(10, 'Talleres Familiares Independientes');

INSERT INTO tab_bancos (id_banco, nom_banco) VALUES 
(001, 'Bancolombia'),
(002, 'Banco de Bogotá'),
(003, 'Davivienda'),
(004, 'BBVA Colombia'),
(005, 'Banco de Occidente'),
(006, 'Banco Popular'),
(007, 'Banco AV Villas'),
(008, 'Banco Caja Social'),
(009, 'Banco Agrario de Colombia'),
(010, 'Scotiabank Colpatria'),
(011, 'Itaú'),
(012, 'Banco Falabella'),
(013, 'Banco Pichincha'),
(014, 'Banco Santander'),
(015, 'Bancoomeva'),
(016, 'Banco Finandina'),
(017, 'Citibank'),
(018, 'GNB Sudameris'),
(019, 'Lulo Bank'),
(020, 'Nu Colombia'),
(021, 'Nequi'),
(022, 'Daviplata'),
(023, 'Dale!'),
(024, 'Movii'),
(025, 'Rappipay');

-- Insertar forma de pago ePayco
INSERT INTO tab_formas_pago (id_pago, nom_pago) VALUES ('EPAYCO', 'Pasarela de Pago ePayco');

-- Vista para mostrar tipos de documento en registro
CREATE OR REPLACE VIEW tipos_col_view AS
SELECT id_tipo_doc AS id ,nom_tipo_doc AS nombre 
FROM tab_tipos_doc 
WHERE id_tipo_doc = 1 OR id_tipo_doc = 2 OR id_tipo_doc = 3 OR id_tipo_doc = 4 
ORDER BY nom_tipo_doc ASC;

CREATE OR REPLACE VIEW departamentos_col_view AS
SELECT id_departamento as id, nom_departamento as nombre 
FROM tab_departamentos 
WHERE id_pais = 1
ORDER BY nombre ASC;

CREATE OR REPLACE VIEW grupos_view AS
SELECT id_grupo as id, nom_grupo as nombre 
FROM tab_grupos 
ORDER BY nombre ASC;    

CREATE OR REPLACE VIEW bancos_view AS
SELECT id_banco as id, nom_banco as nombre
FROM tab_bancos
ORDER BY nombre ASC;

CREATE OR REPLACE VIEW categorias_view AS
SELECT id_categoria, nom_categoria, img_cat
FROM tab_categorias 
ORDER BY nom_categoria ASC;

CREATE OR REPLACE VIEW colores_view AS
SELECT id_color, nom_color 
FROM tab_color 
ORDER BY nom_color ASC;

CREATE OR REPLACE VIEW oficios_view AS
SELECT id_oficio, nom_oficio 
FROM tab_oficios 
ORDER BY nom_oficio ASC;

CREATE OR REPLACE VIEW materias_view AS
SELECT id_materia, nom_materia 
FROM tab_materia_prima 
ORDER BY nom_materia ASC;
