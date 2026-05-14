-- ============================================================================
-- Funciones — Módulo de IA
-- ============================================================================

-- Limpieza de sobrecargas antiguas
DROP FUNCTION IF EXISTS ai.fun_c_image_signature(DECIMAL, VARCHAR, CHAR(64), BIT(64), BIT(64));
DROP FUNCTION IF EXISTS ai.fun_u_embedding(BIGINT, VECTOR, VARCHAR);
DROP FUNCTION IF EXISTS ai.fun_u_delete_image_signatures_by_product(DECIMAL);
DROP FUNCTION IF EXISTS ai.fun_val_unified_hash_search(CHAR(64), BIT(64), BIT(64), INTEGER, INTEGER, DECIMAL, INTEGER);
DROP FUNCTION IF EXISTS ai.fun_val_unified_hash_search(TEXT, BIT(64), BIT(64), INTEGER, INTEGER, DECIMAL, INTEGER);
DROP FUNCTION IF EXISTS ai.fun_val_unified_hash_search(TEXT, BIT(64), BIT(64), INTEGER, INTEGER, DECIMAL, DECIMAL, INTEGER);
DROP FUNCTION IF EXISTS ai.fun_val_similar_by_vector(VECTOR, DOUBLE PRECISION, INTEGER);
DROP FUNCTION IF EXISTS ai.fun_val_similar_by_vector_exclude(VECTOR, DECIMAL, DOUBLE PRECISION, INTEGER);
DROP FUNCTION IF EXISTS ai.fun_val_similar_by_status(VECTOR, VARCHAR, DOUBLE PRECISION, INTEGER);
DROP FUNCTION IF EXISTS ai.fun_val_visual_embeddings_by_products(DECIMAL[]);

-- Funciones actuales: drop + create con LANGUAGE al final
DROP FUNCTION IF EXISTS ai.hamming_distance(BIT(64), BIT(64));
CREATE FUNCTION ai.hamming_distance(a BIT(64), b BIT(64))
RETURNS INTEGER
AS $$
BEGIN
    RETURN CAST(bit_count(a # b) AS INTEGER);
END;
$$ LANGUAGE plpgsql;

DROP FUNCTION IF EXISTS ai.fun_val_unified_hash_search(TEXT, BIT(64), BIT(64), INTEGER, INTEGER, NUMERIC, NUMERIC, INTEGER);
CREATE FUNCTION ai.fun_val_unified_hash_search(
    p_file_hash TEXT,
    p_phash BIT(64),
    p_dhash BIT(64),
    p_phash_threshold INT,
    p_dhash_threshold INT,
    p_exclude_product_id DECIMAL,
    p_exclude_image_id DECIMAL,
    p_limit INT
) RETURNS TABLE(
    id_producto DECIMAL(12,0),
    id_imagen DECIMAL(12,0),
    url_imagen VARCHAR,
    file_hash CHAR(64),
    phash BIT(64),
    dhash BIT(64),
    id_productor DECIMAL(12,0),
    detection_method TEXT,
    score FLOAT8
)
AS $$
BEGIN
    RETURN QUERY
    SELECT ti.id_producto, ti.id_imagen, ti.url_imagen,
           ti.file_hash, ti.phash, ti.dhash, p.id_productor,
           CAST(
               CASE
                   WHEN ti.file_hash = p_file_hash THEN 'hash_exacto'
                   WHEN p_phash IS NOT NULL AND ti.phash IS NOT NULL AND ai.hamming_distance(ti.phash, p_phash) <= p_phash_threshold THEN 'hash_perceptual'
                   WHEN p_dhash IS NOT NULL AND ti.dhash IS NOT NULL AND ai.hamming_distance(ti.dhash, p_dhash) <= p_dhash_threshold THEN 'hash_diferencia'
                   ELSE 'hash_exacto'
               END AS TEXT
           ) AS detection_method,
           CAST(
               CASE
                   WHEN ti.file_hash = p_file_hash THEN 1.0
                   WHEN p_phash IS NOT NULL AND ti.phash IS NOT NULL AND ai.hamming_distance(ti.phash, p_phash) <= p_phash_threshold
                       THEN GREATEST(0.0, 1.0 - CAST(ai.hamming_distance(ti.phash, p_phash) AS FLOAT8) / 64.0)
                   WHEN p_dhash IS NOT NULL AND ti.dhash IS NOT NULL AND ai.hamming_distance(ti.dhash, p_dhash) <= p_dhash_threshold
                       THEN GREATEST(0.0, 1.0 - CAST(ai.hamming_distance(ti.dhash, p_dhash) AS FLOAT8) / 64.0)
                   ELSE 0.0
               END AS FLOAT8
           ) AS score
    FROM tab_imagenes ti
    INNER JOIN tab_productos p ON p.id_producto = ti.id_producto
    WHERE ti.id_producto != p_exclude_product_id
      AND ti.id_imagen != p_exclude_image_id
      AND ti.file_hash IS NOT NULL
      AND (
          ti.file_hash = p_file_hash
          OR (p_phash IS NOT NULL AND ti.phash IS NOT NULL AND ai.hamming_distance(ti.phash, p_phash) <= p_phash_threshold)
          OR (p_dhash IS NOT NULL AND ti.dhash IS NOT NULL AND ai.hamming_distance(ti.dhash, p_dhash) <= p_dhash_threshold)
      )
    ORDER BY score DESC
    LIMIT p_limit;
END;
$$ LANGUAGE plpgsql;

DROP FUNCTION IF EXISTS ai.fun_c_visual_embedding(NUMERIC, NUMERIC, VECTOR(1024), VARCHAR);
CREATE FUNCTION ai.fun_c_visual_embedding(
    p_id_producto tab_imagenes.id_producto%TYPE,
    p_id_imagen tab_imagenes.id_imagen%TYPE,
    p_visual_embedding VECTOR(1024),
    p_embedding_model VARCHAR
) RETURNS VOID
AS $$
BEGIN
    INSERT INTO ai.product_image_embeddings (id_producto, id_imagen, visual_embedding, embedding_model)
    VALUES (p_id_producto, p_id_imagen, p_visual_embedding, p_embedding_model)
    ON CONFLICT (id_producto, id_imagen) DO UPDATE
        SET visual_embedding = p_visual_embedding,
            embedding_model = p_embedding_model;
END;
$$ LANGUAGE plpgsql;

DROP FUNCTION IF EXISTS ai.fun_val_similar_by_vector(VECTOR(1024), FLOAT8, INT);
CREATE FUNCTION ai.fun_val_similar_by_vector(
    p_embedding VECTOR(1024),
    p_threshold FLOAT8,
    p_limit INT
) RETURNS TABLE(
    id_producto DECIMAL(12,0),
    id_imagen DECIMAL(12,0),
    url_imagen VARCHAR,
    similarity FLOAT8
)
AS $$
BEGIN
    RETURN QUERY
    SELECT e.id_producto, e.id_imagen, ti.url_imagen,
           1 - (e.visual_embedding <=> p_embedding) AS similarity
    FROM ai.product_image_embeddings e
    INNER JOIN tab_imagenes ti ON ti.id_producto = e.id_producto AND ti.id_imagen = e.id_imagen
    WHERE ti.phash IS NOT NULL
      AND 1 - (e.visual_embedding <=> p_embedding) >= p_threshold
    ORDER BY e.visual_embedding <=> p_embedding
    LIMIT p_limit;
END;
$$ LANGUAGE plpgsql;

DROP FUNCTION IF EXISTS ai.fun_val_similar_by_vector_exclude(VECTOR(1024), NUMERIC, FLOAT8, INT);
CREATE FUNCTION ai.fun_val_similar_by_vector_exclude(
    p_embedding VECTOR(1024),
    p_producer_id tab_productos.id_productor%TYPE,
    p_threshold FLOAT8,
    p_limit INT
) RETURNS TABLE(
    id_producto DECIMAL(12,0),
    id_imagen DECIMAL(12,0),
    url_imagen VARCHAR,
    similarity FLOAT8
)
AS $$
BEGIN
    RETURN QUERY
    SELECT e.id_producto, e.id_imagen, ti.url_imagen,
           1 - (e.visual_embedding <=> p_embedding) AS similarity
    FROM ai.product_image_embeddings e
    INNER JOIN tab_imagenes ti ON ti.id_producto = e.id_producto AND ti.id_imagen = e.id_imagen
    INNER JOIN tab_productos p ON p.id_producto = e.id_producto
    WHERE ti.phash IS NOT NULL
      AND p.id_productor <> p_producer_id
      AND 1 - (e.visual_embedding <=> p_embedding) >= p_threshold
    ORDER BY e.visual_embedding <=> p_embedding
    LIMIT p_limit;
END;
$$ LANGUAGE plpgsql;

DROP FUNCTION IF EXISTS ai.fun_val_similar_by_status(VECTOR(1024), VARCHAR, FLOAT8, INT);
CREATE FUNCTION ai.fun_val_similar_by_status(
    p_embedding VECTOR(1024),
    p_status tab_productos.validation_status%TYPE,
    p_threshold FLOAT8 DEFAULT 0.85,
    p_limit INT DEFAULT 10
) RETURNS TABLE(
    id_producto DECIMAL(12,0),
    id_imagen DECIMAL(12,0),
    url_imagen VARCHAR,
    similarity FLOAT8,
    validation_status tab_productos.validation_status%TYPE
)
AS $$
BEGIN
    RETURN QUERY
    SELECT e.id_producto, e.id_imagen, ti.url_imagen,
           1 - (e.visual_embedding <=> p_embedding) AS similarity,
           p.validation_status
    FROM ai.product_image_embeddings e
    INNER JOIN tab_imagenes ti ON ti.id_producto = e.id_producto AND ti.id_imagen = e.id_imagen
    INNER JOIN tab_productos p ON p.id_producto = e.id_producto
    WHERE ti.phash IS NOT NULL
      AND p.validation_status = p_status
      AND 1 - (e.visual_embedding <=> p_embedding) >= p_threshold
    ORDER BY e.visual_embedding <=> p_embedding
    LIMIT p_limit;
END;
$$ LANGUAGE plpgsql;

DROP FUNCTION IF EXISTS ai.fun_val_visual_embeddings_by_products(NUMERIC[]);
CREATE FUNCTION ai.fun_val_visual_embeddings_by_products(
    p_product_ids DECIMAL[]
) RETURNS TABLE(
    id_producto      DECIMAL,
    id_imagen        DECIMAL,
    url_imagen       TEXT,
    visual_embedding VECTOR(1024)
)
AS $$
BEGIN
    RETURN QUERY
    SELECT
        e.id_producto,
        e.id_imagen,
        CAST(ti.url_imagen AS TEXT) AS url_imagen,
        e.visual_embedding
    FROM ai.product_image_embeddings e
    INNER JOIN tab_imagenes ti
        ON ti.id_producto = e.id_producto
       AND ti.id_imagen = e.id_imagen
    WHERE e.id_producto = ANY(p_product_ids)
      AND ti.is_deleted = FALSE
    ORDER BY e.id_producto, e.id_imagen;
END;
$$ LANGUAGE plpgsql;

DROP FUNCTION IF EXISTS ai.fun_val_check_examples_count();
CREATE FUNCTION ai.fun_val_check_examples_count()
RETURNS BIGINT
AS $$
BEGIN
    RETURN (
        SELECT COUNT(DISTINCT product_id)
        FROM ai.product_validation_results
        WHERE decision IN ('approved', 'rejected')
    );
END;
$$ LANGUAGE plpgsql;

DROP FUNCTION IF EXISTS ai.fun_c_text_embedding(NUMERIC, NUMERIC, TEXT, VECTOR(1024));
CREATE FUNCTION ai.fun_c_text_embedding(
    p_product_id      DECIMAL(12,0),
    p_producer_id     DECIMAL(10,0),
    p_content         TEXT,
    p_text_embedding  VECTOR(1024)
) RETURNS BIGINT
AS $$
DECLARE
    v_id BIGINT;
BEGIN
    INSERT INTO ai.product_text_embeddings (
        product_id, producer_id, content, text_embedding
    ) VALUES (
        p_product_id, p_producer_id, p_content, p_text_embedding
    )
    ON CONFLICT ON CONSTRAINT product_text_embeddings_pkey
    DO UPDATE SET
        content = EXCLUDED.content,
        text_embedding = EXCLUDED.text_embedding,
        updated_at = NOW()
    RETURNING id INTO v_id;
    RETURN v_id;
END;
$$ LANGUAGE plpgsql;

DROP FUNCTION IF EXISTS ai.fun_val_search_similar_text(VECTOR(1024), DOUBLE PRECISION, INTEGER);
CREATE FUNCTION ai.fun_val_search_similar_text(
    p_embedding     VECTOR(1024),
    p_threshold     DOUBLE PRECISION,
    p_limit         INTEGER DEFAULT 5
) RETURNS TABLE(
    id              BIGINT,
    product_id      DECIMAL(12,0),
    producer_id     DECIMAL(10,0),
    content         TEXT,
    similarity      DOUBLE PRECISION
)
AS $$
BEGIN
    RETURN QUERY
    SELECT
        pte.id,
        pte.product_id,
        pte.producer_id,
        pte.content,
        1 - (pte.text_embedding <=> p_embedding) AS similarity
    FROM ai.product_text_embeddings pte
    WHERE 1 - (pte.text_embedding <=> p_embedding) >= p_threshold
    ORDER BY similarity DESC
    LIMIT p_limit;
END;
$$ LANGUAGE plpgsql;

DROP FUNCTION IF EXISTS ai.fun_val_search_similar_text_exclude(VECTOR(1024), NUMERIC, DOUBLE PRECISION, INTEGER);
CREATE FUNCTION ai.fun_val_search_similar_text_exclude(
    p_embedding     VECTOR(1024),
    p_producer_id   DECIMAL(10,0),
    p_threshold     DOUBLE PRECISION,
    p_limit         INTEGER DEFAULT 5
) RETURNS TABLE(
    id              BIGINT,
    product_id      DECIMAL(12,0),
    producer_id     DECIMAL(10,0),
    content         TEXT,
    similarity      DOUBLE PRECISION
)
AS $$
BEGIN
    RETURN QUERY
    SELECT
        pte.id,
        pte.product_id,
        pte.producer_id,
        pte.content,
        1 - (pte.text_embedding <=> p_embedding) AS similarity
    FROM ai.product_text_embeddings pte
    WHERE pte.producer_id != p_producer_id
      AND 1 - (pte.text_embedding <=> p_embedding) >= p_threshold
    ORDER BY similarity DESC
    LIMIT p_limit;
END;
$$ LANGUAGE plpgsql;

DROP FUNCTION IF EXISTS ai.fun_get_rag_rules(TEXT[]);
CREATE FUNCTION ai.fun_get_rag_rules(
    p_types TEXT[]
) RETURNS TABLE(
    id      DECIMAL(2,0),
    type    TEXT,
    content TEXT
)
AS $$
BEGIN
    RETURN QUERY
    SELECT rr.id, rr.type, rr.content
    FROM ai.rag_rules rr
    WHERE rr.type = ANY(p_types)
    ORDER BY rr.id;
END;
$$ LANGUAGE plpgsql;

DROP FUNCTION IF EXISTS ai.fun_c_validation_result(NUMERIC, NUMERIC, TEXT, TEXT, NUMERIC, TEXT, VARCHAR, VARCHAR, VARCHAR, TEXT, TEXT, NUMERIC, TEXT, NUMERIC, TEXT, TEXT, BOOLEAN, TEXT);
CREATE FUNCTION ai.fun_c_validation_result(
    p_product_id            ai.product_validation_results.product_id%TYPE,
    p_producer_id           ai.product_validation_results.producer_id%TYPE,
    p_decision              ai.product_validation_results.decision%TYPE,
    p_plagiarism_status     ai.product_validation_results.plagiarism_status%TYPE,
    p_plagiarism_score      ai.product_validation_results.plagiarism_score%TYPE,
    p_plagiarism_method     ai.product_validation_results.plagiarism_method%TYPE,
    p_matched_product_id    ai.product_validation_results.matched_product_id%TYPE,
    p_matched_producer_id   ai.product_validation_results.matched_producer_id%TYPE,
    p_matched_image_id      ai.product_validation_results.matched_image_id%TYPE,
    p_matched_image_url     ai.product_validation_results.matched_image_url%TYPE,
    p_text_image_status     ai.product_validation_results.text_image_status%TYPE,
    p_text_image_score      ai.product_validation_results.text_image_score%TYPE,
    p_artisan_status        ai.product_validation_results.artisan_status%TYPE,
    p_artisan_score         ai.product_validation_results.artisan_score%TYPE,
    p_provider_used         ai.product_validation_results.provider_used%TYPE,
    p_decision_model        ai.product_validation_results.decision_model%TYPE,
    p_fallback_used         ai.product_validation_results.fallback_used%TYPE,
    p_reason                ai.product_validation_results.reason%TYPE
) RETURNS BIGINT
AS $$
DECLARE
    v_id BIGINT;
BEGIN
    INSERT INTO ai.product_validation_results (
        product_id, producer_id, decision, plagiarism_status, plagiarism_score,
        plagiarism_method, matched_product_id, matched_producer_id, matched_image_id,
        matched_image_url, text_image_status, text_image_score, artisan_status,
        artisan_score, provider_used, decision_model, fallback_used, reason
    ) VALUES (
        p_product_id, p_producer_id, p_decision, p_plagiarism_status, p_plagiarism_score,
        p_plagiarism_method, p_matched_product_id, p_matched_producer_id, p_matched_image_id,
        p_matched_image_url, p_text_image_status, p_text_image_score, p_artisan_status,
        p_artisan_score, p_provider_used, p_decision_model, p_fallback_used, p_reason
    )
    RETURNING id INTO v_id;
    RETURN v_id;
END;
$$ LANGUAGE plpgsql;

DROP FUNCTION IF EXISTS ai.fun_val_check_pgvector();
CREATE FUNCTION ai.fun_val_check_pgvector()
RETURNS BOOLEAN
AS $$
BEGIN
    RETURN (SELECT EXISTS(SELECT 1 FROM pg_extension WHERE extname = 'vector'));
END;
$$ LANGUAGE plpgsql;

DROP FUNCTION IF EXISTS ai.fun_val_latest_validation_result(NUMERIC);
CREATE FUNCTION ai.fun_val_latest_validation_result(
    p_product_id ai.product_validation_results.product_id%TYPE
) RETURNS SETOF ai.product_validation_results
AS $$
BEGIN
    RETURN QUERY
    SELECT *
    FROM ai.product_validation_results
    WHERE product_id = p_product_id
    ORDER BY created_at DESC
    LIMIT 1;
END;
$$ LANGUAGE plpgsql;

DROP FUNCTION IF EXISTS ai.fun_c_rag_rule(NUMERIC, TEXT, TEXT);
CREATE FUNCTION ai.fun_c_rag_rule(
    p_id      DECIMAL(2,0),
    p_type    TEXT,
    p_content TEXT
) RETURNS VOID
AS $$
BEGIN
    INSERT INTO ai.rag_rules(id, type, content)
    VALUES (p_id, p_type, p_content)
    ON CONFLICT (id) DO UPDATE
    SET content = EXCLUDED.content,
        updated_at = NOW();
END;
$$ LANGUAGE plpgsql;
