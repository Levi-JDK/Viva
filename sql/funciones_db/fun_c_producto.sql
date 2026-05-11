CREATE OR REPLACE FUNCTION fun_c_producto(
    p_id_productor          tab_productos.id_productor%TYPE,
    p_nom_producto          tab_productos.nom_producto%TYPE,
    p_stock_productor       tab_productos.stock_productor%TYPE,
    p_id_categoria          tab_productos.id_categoria%TYPE,
    p_id_color              tab_productos.id_color%TYPE,
    p_id_oficio             tab_productos.id_oficio%TYPE,
    p_id_materia            tab_productos.id_materia%TYPE,
    p_precio_producto       tab_productos.precio_producto%TYPE,
    p_descripcion_producto  tab_productos.descripcion_producto%TYPE,
    p_is_active             BOOLEAN
) RETURNS BOOLEAN AS $$
BEGIN
    -- 1. Validar que los datos obligatorios no sean nulos
    IF p_id_productor IS NULL OR p_nom_producto IS NULL OR 
       p_stock_productor IS NULL OR p_id_categoria IS NULL OR p_id_color IS NULL OR 
       p_id_oficio IS NULL OR p_id_materia IS NULL OR p_precio_producto IS NULL THEN
        RETURN FALSE;
    END IF;

    -- 2. Validar Existencia de LLaves Foráneas
    
    -- Validar Productor
    IF NOT EXISTS (SELECT 1 FROM tab_productores WHERE id_productor = p_id_productor) THEN
        RAISE NOTICE 'El productor % no existe', p_id_productor;
        RETURN FALSE;
    END IF;

    -- Validar Categoría
    IF NOT EXISTS (SELECT 1 FROM tab_categorias WHERE id_categoria = p_id_categoria) THEN
        RAISE NOTICE 'La categoría % no existe', p_id_categoria;
        RETURN FALSE;
    END IF;

    -- Validar Color
    IF NOT EXISTS (SELECT 1 FROM tab_color WHERE id_color = p_id_color) THEN
        RAISE NOTICE 'El color % no existe', p_id_color;
        RETURN FALSE;
    END IF;

    -- Validar Oficio
    IF NOT EXISTS (SELECT 1 FROM tab_oficios WHERE id_oficio = p_id_oficio) THEN
        RAISE NOTICE 'El oficio % no existe', p_id_oficio;
        RETURN FALSE;
    END IF;

    -- Validar Materia Prima
    IF NOT EXISTS (SELECT 1 FROM tab_materia_prima WHERE id_materia = p_id_materia) THEN
        RAISE NOTICE 'La materia prima % no existe', p_id_materia;
        RETURN FALSE;
    END IF;

    -- 3. Insertar producto
    INSERT INTO tab_productos (
        id_producto,
        id_productor,
        nom_producto,
        stock_productor,
        id_categoria,
        id_color,
        id_oficio,
        id_materia,
        precio_producto,
        descripcion_producto,
        is_active
    ) VALUES (
        (SELECT COALESCE(MAX(id_producto), 1) + 1 FROM tab_productos),
        p_id_productor,
        p_nom_producto,
        p_stock_productor,
        p_id_categoria,
        p_id_color,
        p_id_oficio,
        p_id_materia,
        p_precio_producto,
        p_descripcion_producto,
        (p_stock_productor > 0)
    );

    RETURN TRUE;
EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error en fun_c_producto: %', SQLERRM;
        RETURN FALSE;
END;
$$ LANGUAGE plpgsql;