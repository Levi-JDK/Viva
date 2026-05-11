CREATE OR REPLACE FUNCTION fun_obtener_producto_por_id(
    p_id_producto tab_productos.id_producto%TYPE
)
RETURNS TABLE (
    id_producto         tab_productos.id_producto%TYPE,
    nom_producto        tab_productos.nom_producto%TYPE,
    precio_producto     tab_productos.precio_producto%TYPE,
    stock_productor     tab_productos.stock_productor%TYPE,
    descripcion_producto tab_productos.descripcion_producto%TYPE,
    id_categoria        tab_productos.id_categoria%TYPE,
    nom_categoria       tab_categorias.nom_categoria%TYPE,
    id_oficio           tab_productos.id_oficio%TYPE,
    nom_oficio          tab_oficios.nom_oficio%TYPE,
    id_materia          tab_productos.id_materia%TYPE,
    nom_materia         tab_materia_prima.nom_materia%TYPE,
    id_color            tab_productos.id_color%TYPE,
    nom_color           tab_color.nom_color%TYPE,
    id_productor        tab_productos.id_productor%TYPE,
    nom_productor       VARCHAR, -- Nombre del usuario/productor
    ubicacion           VARCHAR, -- Ciudad, Departamento
    imagenes            JSON
) AS $$
BEGIN
    RETURN QUERY
    SELECT 
        p.id_producto,
        p.nom_producto,
        p.precio_producto,
        p.stock_productor,
        p.descripcion_producto,
        p.id_categoria,
        c.nom_categoria,
        p.id_oficio,
        o.nom_oficio,
        p.id_materia,
        m.nom_materia,
        p.id_color,
        col.nom_color,
        p.id_productor,
        CAST(u.nom_user AS VARCHAR) as nom_productor,
        CAST(ci.nom_ciudad || ', ' || d.nom_departamento AS VARCHAR) as ubicacion,
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
    JOIN tab_oficios o ON p.id_oficio = o.id_oficio
    JOIN tab_materia_prima m ON p.id_materia = m.id_materia
    JOIN tab_color col ON p.id_color = col.id_color
    JOIN tab_productores prod ON p.id_productor = prod.id_productor
    JOIN tab_users u ON prod.id_user = u.id_user
    JOIN tab_ciudades ci ON prod.id_pais = ci.id_pais AND prod.id_departamento = ci.id_departamento AND prod.id_ciudad = ci.id_ciudad
    JOIN tab_departamentos d ON prod.id_pais = d.id_pais AND prod.id_departamento = d.id_departamento
    WHERE p.id_producto = p_id_producto;
END;
$$ LANGUAGE plpgsql;
