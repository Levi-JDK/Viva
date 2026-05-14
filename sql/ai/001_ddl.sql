-- ============================================================================
-- DDL — Schema AI (Validación IA de Productos)
-- Proyecto: VIVA Marketplace
-- Refleja EXACTAMENTE el estado actual de la DB.
-- Nada de ALTER TABLE, nada de migraciones, solo el DDL final.
-- ============================================================================

CREATE SCHEMA IF NOT EXISTS ai;

CREATE EXTENSION IF NOT EXISTS vector;

-- ════════════════════════════════════════════════════════════════════════════
-- 1. Embeddings visuales de imagen
-- ════════════════════════════════════════════════════════════════════════════

-- Los hashes de imagen viven en tab_imagenes (public schema) y se agregan
-- mediante migración/ALTER TABLE para no redefinir la tabla base acá.
CREATE TABLE IF NOT EXISTS ai.product_image_embeddings(
    id_producto      DECIMAL(12,0)               NOT NULL,
    id_imagen        DECIMAL(12,0)               NOT NULL,
    visual_embedding VECTOR(1024)                NOT NULL,
    embedding_model  VARCHAR(100)                NOT NULL DEFAULT 'Sin modelo',
    created_at       TIMESTAMP WITH TIME ZONE    NOT NULL DEFAULT NOW(),
    PRIMARY KEY(id_producto, id_imagen),
    FOREIGN KEY(id_producto, id_imagen) REFERENCES tab_imagenes(id_producto, id_imagen) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_product_image_embeddings_producto
    ON ai.product_image_embeddings(id_producto);

CREATE INDEX IF NOT EXISTS idx_product_image_embeddings_model
    ON ai.product_image_embeddings(embedding_model);

-- ════════════════════════════════════════════════════════════════════════════
-- 2. Embeddings de texto (productos, políticas RAG, ejemplos)
-- ════════════════════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS ai.product_text_embeddings(
    id              BIGSERIAL                   NOT NULL,
    product_id      DECIMAL(12,0)               NOT NULL,
    producer_id     DECIMAL(10,0)               NOT NULL,
    content         TEXT                        NOT NULL,
    text_embedding  VECTOR(1024)                NOT NULL,
    created_at      TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
    updated_at      TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
    PRIMARY KEY(id),
    FOREIGN KEY(product_id) REFERENCES tab_productos(id_producto),
    FOREIGN KEY(producer_id) REFERENCES tab_productores(id_productor)
);

CREATE TABLE IF NOT EXISTS ai.product_visual_descriptions(
    product_id          DECIMAL(12,0)               NOT NULL,
    description         TEXT                        NOT NULL,
    embedding           VECTOR(2048)                NOT NULL,
    model               VARCHAR(100)                NOT NULL,
    created_at          TIMESTAMP WITH TIME ZONE    NOT NULL    DEFAULT NOW(),
    PRIMARY KEY(product_id),
    FOREIGN KEY(product_id) REFERENCES tab_productos(id_producto)
);

-- ════════════════════════════════════════════════════════════════════════════
-- 3. Configuración del módulo AI (parametrizable desde DB)
-- ════════════════════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS ai.config(
    key         VARCHAR(100)    NOT NULL,
    value       TEXT            NOT NULL,
    description TEXT            NOT NULL DEFAULT '',
    updated_at  TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    PRIMARY KEY(key)
);

INSERT INTO ai.config(key, value, description) VALUES
    ('rag.min_examples', '20', 'Mínimo ejemplos (approved + rejected) antes de que la IA decida sola. Por debajo -> pending_review.')
ON CONFLICT (key) DO NOTHING;

-- ════════════════════════════════════════════════════════════════════════════
-- 4. Resultados de validación
-- ════════════════════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS ai.product_validation_results(
    id                      BIGSERIAL                   NOT NULL,
    product_id              DECIMAL(12,0)               NOT NULL,
    producer_id             DECIMAL(10,0)               NOT NULL,
    decision                TEXT                        NOT NULL,
    plagiarism_status       TEXT                        NOT NULL DEFAULT 'none',
    plagiarism_score        NUMERIC                     NOT NULL DEFAULT 0,
    plagiarism_method       TEXT                        NOT NULL DEFAULT 'N/A',
    matched_product_id      VARCHAR(20)                 NOT NULL DEFAULT 'N/A',
    matched_producer_id     VARCHAR(20)                 NOT NULL DEFAULT 'N/A',
    matched_image_id        VARCHAR(20)                 NOT NULL DEFAULT 'N/A',
    matched_image_url       TEXT                        NOT NULL DEFAULT 'N/A',
    text_image_status       TEXT                        NOT NULL DEFAULT 'no_evaluada',
    text_image_score        NUMERIC                     NOT NULL DEFAULT 0,
    artisan_status          TEXT                        NOT NULL DEFAULT 'no_evaluada',
    artisan_score           NUMERIC                     NOT NULL DEFAULT 0,
    provider_used           TEXT                        NOT NULL DEFAULT 'N/A',
    decision_model          TEXT                        NOT NULL DEFAULT 'N/A',
    fallback_used           BOOLEAN                     NOT NULL DEFAULT FALSE,
    reason                  TEXT                        NOT NULL DEFAULT 'N/A',
    created_at              TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
    PRIMARY KEY(id),
    CHECK(decision IN ('approved', 'rejected', 'revision_humana', 'pending_validacion_ia', 'pending_review')),
    CHECK(plagiarism_status IN ('none', 'posible', 'confirmed')),
    CHECK(plagiarism_method IN ('N/A', 'hash_exacto', 'hash_perceptual', 'hash_diferencia', 'embedding_visual')),
    CHECK(text_image_status IN ('alta', 'media', 'baja', 'no_evaluada')),
    CHECK(artisan_status IN ('artesanal', 'dudosa', 'no_artesanal', 'no_evaluada')),
    FOREIGN KEY(product_id) REFERENCES tab_productos(id_producto),
    FOREIGN KEY(producer_id) REFERENCES tab_productores(id_productor)
);

CREATE INDEX IF NOT EXISTS idx_pvr_product
    ON ai.product_validation_results(product_id);

CREATE INDEX IF NOT EXISTS idx_pvr_producer
    ON ai.product_validation_results(producer_id);

CREATE INDEX IF NOT EXISTS idx_pvr_decision
    ON ai.product_validation_results(decision);

-- ════════════════════════════════════════════════════════════════════════════
-- 5. Reglas RAG (contexto para validación IA, sin embeddings)
-- ════════════════════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS ai.rag_rules(
    id          DECIMAL(2,0) NOT NULL,
    type        TEXT         NOT NULL,
    content     TEXT         NOT NULL,
    created_at  TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
    updated_at  TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
    PRIMARY KEY(id)
);

INSERT INTO ai.rag_rules(id, type, content) VALUES
(1, 'artisan_policy',   'Se considera ARTESANAL todo producto hecho total o predominantemente a mano, utilizando técnicas tradicionales como telar, crochet, cestería, alfarería, talla en madera, filigrana, barniz de Pasto, trabajo en werregue, orfebrería precolombina o marroquinería artesanal. Las materias primas deben ser naturales o tradicionales: lana, fique, caña flecha, iraca, arcilla, madera, semillas, metales preciosos, bronce, cobre.'),
(2, 'artisan_policy',   'Un producto es APROBADO como artesanal si demuestra técnicas manuales tradicionales, uso de materias primas naturales o tradicionales, y refleja el oficio artesanal declarado. Aplica también a piezas vintage, antigüedades precolombinas y artículos de mercadillo con valor cultural comprobable, siempre que NO sean producción industrial.'),
(3, 'artisan_policy',   'Alimentos y bebidas artesanales siguen procesos tradicionales de transformación como cacao artesanal, café de origen, viche destilado en alambique, miel nativa, plantas medicinales secadas a mano. Productos con empaque industrial o registro Invima industrial se consideran dudosos → revisión humana.'),
(4, 'plagiarism_policy', 'Se considera PLAGIO cuando un producto utiliza la MISMA IMAGEN (hash exacto) que otro producto de DISTINTO productor. Esto aplica incluso si el nombre del producto o la descripción son diferentes. En ese caso → revision_humana directa, sin pasar por IA.'),
(5, 'plagiarism_policy', 'Dos productos del MISMO productor con la misma imagen NO son plagio. Si un productor reutiliza sus propias imágenes para distintos productos, no se marca. La similitud perceptual (misma imagen con distinta resolución/compresión) se detecta por pHash/dHash con distancia de Hamming.')
ON CONFLICT (id) DO NOTHING;
