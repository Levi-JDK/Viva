CREATE OR REPLACE FUNCTION fun_obtener_productos(
    p_id_productor tab_productos.id_productor%TYPE
)
RETURNS TABLE (
    id_producto         tab_productos.id_producto%TYPE,
    nom_producto        tab_productos.nom_producto%TYPE,
    precio_producto     tab_productos.precio_producto%TYPE,
    stock_productor     tab_productos.stock_productor%TYPE,
    nom_categoria       tab_categorias.nom_categoria%TYPE,
    activo              tab_productos.is_active%TYPE,
    vistas              tab_productos.vistas%TYPE,
    imagenes            JSON
) AS $$
BEGIN
    RETURN QUERY
    SELECT 
        p.id_producto,
        p.nom_producto,
        p.precio_producto,
        p.stock_productor,
        c.nom_categoria,
        p.is_active,
        p.vistas,
        COALESCE(
            (
                SELECT json_agg(json_build_object('url', i.url_imagen))
                FROM tab_imagenes i 
                WHERE i.id_producto = p.id_producto
            ), 
            '[]'::json
        ) AS imagenes
    FROM tab_productos p
    JOIN tab_categorias c ON p.id_categoria = c.id_categoria
    WHERE p.id_productor = p_id_productor AND p.is_deleted = FALSE
    ORDER BY p.created_at ASC;
END;
$$ LANGUAGE plpgsql;
