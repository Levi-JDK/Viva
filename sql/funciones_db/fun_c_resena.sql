CREATE OR REPLACE FUNCTION fun_c_resena(
    p_id_user       INTEGER,
    p_id_producto   DECIMAL(12,0),
    p_calificacion  INTEGER,
    p_texto         TEXT
)
RETURNS BOOLEAN AS $$
DECLARE
    v_exist BOOLEAN;
BEGIN
    -- Validar que el usuario exista
    IF NOT EXISTS (SELECT 1 FROM tab_users WHERE id_user = p_id_user) THEN
        RETURN FALSE;
    END IF;

    -- Validar que el producto exista, esté activo y no borrado
    IF NOT EXISTS (SELECT 1 FROM tab_productos WHERE id_producto = p_id_producto AND is_active = TRUE AND is_deleted = FALSE) THEN
        RETURN FALSE;
    END IF;

    -- Validar que no haya enviado una reseña vacía o NULL
    IF p_texto IS NULL OR LENGTH(TRIM(p_texto)) = 0 THEN
        RETURN FALSE;
    END IF;

    -- Validar estrellas entre 1 y 5
    IF p_calificacion IS NULL OR p_calificacion < 1 OR p_calificacion > 5 THEN
        RETURN FALSE;
    END IF;

    -- Verificar si ya existe una reseña de este usuario para este producto
    SELECT EXISTS(
        SELECT 1 FROM tab_resenas 
        WHERE id_user = p_id_user AND id_producto = p_id_producto
    ) INTO v_exist;

    IF v_exist THEN
        -- Actualizar la reseña existente
        UPDATE tab_resenas 
        SET calificacion = p_calificacion, 
            texto_resena = p_texto,
            updated_at = CURRENT_TIMESTAMP,
            is_deleted = FALSE
        WHERE id_user = p_id_user AND id_producto = p_id_producto;
    ELSE
        -- Insertar nueva reseña
        INSERT INTO tab_resenas (id_user, id_producto, calificacion, texto_resena)
        VALUES (p_id_user, p_id_producto, p_calificacion, p_texto);
    END IF;

    RETURN TRUE;
EXCEPTION
    WHEN OTHERS THEN
        RETURN FALSE;
END;
$$ LANGUAGE plpgsql;
