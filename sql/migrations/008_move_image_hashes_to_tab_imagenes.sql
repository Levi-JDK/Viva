-- Move image hashes from ai.product_image_signatures to tab_imagenes and
-- store visual embeddings in their own ready-only table.

ALTER TABLE tab_imagenes
    ADD COLUMN IF NOT EXISTS file_hash CHAR(64),
    ADD COLUMN IF NOT EXISTS phash     BIT(64),
    ADD COLUMN IF NOT EXISTS dhash     BIT(64);

CREATE TABLE IF NOT EXISTS ai.product_image_embeddings(
    id_producto      DECIMAL(12,0)               NOT NULL,
    id_imagen        DECIMAL(12,0)               NOT NULL,
    visual_embedding VECTOR(2048)                NOT NULL,
    embedding_model  VARCHAR(100)                NOT NULL DEFAULT 'Sin modelo',
    created_at       TIMESTAMP WITH TIME ZONE    NOT NULL DEFAULT NOW(),
    PRIMARY KEY(id_producto, id_imagen),
    FOREIGN KEY(id_producto, id_imagen) REFERENCES tab_imagenes(id_producto, id_imagen) ON DELETE CASCADE
);

UPDATE tab_imagenes ti
SET file_hash = pis.file_hash,
    phash     = pis.phash,
    dhash     = pis.dhash
FROM ai.product_image_signatures pis
WHERE ti.id_producto = pis.id_producto
  AND ti.url_imagen = pis.image_path;

INSERT INTO ai.product_image_embeddings (id_producto, id_imagen, visual_embedding, embedding_model)
SELECT pis.id_producto, ti.id_imagen, pis.visual_embedding, pis.embedding_model
FROM ai.product_image_signatures pis
INNER JOIN tab_imagenes ti
    ON ti.id_producto = pis.id_producto
   AND ti.url_imagen = pis.image_path
WHERE pis.visual_embedding IS NOT NULL
ON CONFLICT (id_producto, id_imagen) DO UPDATE
    SET visual_embedding = EXCLUDED.visual_embedding,
        embedding_model = EXCLUDED.embedding_model;

DROP TABLE IF EXISTS ai.product_image_signatures CASCADE;
