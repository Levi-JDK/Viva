CREATE OR REPLACE FUNCTION fun_c_imagen(
    p_id_producto   tab_imagenes.id_producto%TYPE,
    p_url_imagen    tab_imagenes.url_imagen%TYPE
) RETURNS BOOLEAN AS $$
BEGIN
    -- 1. Validar datos obligatorios
    IF p_id_producto IS NULL OR p_url_imagen IS NULL THEN
        RETURN FALSE;
    END IF;

    -- 2. Validar que el producto exista
    IF NOT EXISTS (SELECT 1 FROM tab_productos WHERE id_producto = p_id_producto) THEN
        RETURN FALSE;
    END IF;

    -- 3. Insertar imagen
    INSERT INTO tab_imagenes (
        id_producto,
        id_imagen,
        url_imagen
    ) VALUES (
        p_id_producto,
        COALESCE((SELECT MAX(id_imagen) + 1 FROM tab_imagenes), 1),
        p_url_imagen
    );

    RETURN TRUE;
EXCEPTION
    WHEN OTHERS THEN
        RETURN FALSE;
END;
$$ LANGUAGE plpgsql;
