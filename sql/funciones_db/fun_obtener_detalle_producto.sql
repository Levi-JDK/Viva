-- Function to get full product details including stand info and images
CREATE OR REPLACE FUNCTION fun_obtener_detalle_producto(p_id_producto DECIMAL)
RETURNS TABLE (
    id_producto DECIMAL,
    nom_producto VARCHAR,
    precio_producto DECIMAL,
    descripcion_producto TEXT,
    stock_productor DECIMAL,
    is_active BOOLEAN,
    id_categoria DECIMAL,
    nom_categoria VARCHAR,
    id_color VARCHAR,
    nom_color VARCHAR,
    id_oficio DECIMAL,
    nom_oficio VARCHAR,
    id_materia DECIMAL,
    nom_materia VARCHAR,
    id_productor DECIMAL,
    nom_productor VARCHAR, -- Name of the person/user
    id_stand DECIMAL,
    nom_stand VARCHAR,
    img_stand VARCHAR,
    slogan_stand VARCHAR,
    descripcion_stand TEXT,
    portada_stand VARCHAR,
    ubicacion VARCHAR,
    foto_user VARCHAR,
    imagenes JSON
) AS $$
BEGIN
    RETURN QUERY
    SELECT 
        p.id_producto,
        p.nom_producto,
        p.precio_producto,
        p.descripcion_producto::TEXT,
        p.stock_productor,
        p.is_active,
        c.id_categoria,
        c.nom_categoria,
        co.id_color,
        co.nom_color,
        o.id_oficio,
        o.nom_oficio,
        m.id_materia,
        m.nom_materia,
        pr.id_productor,
        (u.nom_user || ' ' || u.ape_user)::VARCHAR as nom_productor,
        s.id_stand,
        s.nom_stand,
        s.img_stand,
        s.slogan_stand,
        s.descripcion_stand,
        s.portada_stand,
        (ci.nom_ciudad || ', ' || d.nom_departamento)::VARCHAR as ubicacion,
        u.foto_user::VARCHAR as foto_user,
        COALESCE(
            (
                SELECT json_agg(json_build_object('id_imagen', i.id_imagen, 'url', i.url_imagen))
                FROM tab_imagenes i
                WHERE i.id_producto = p.id_producto AND i.is_deleted = FALSE
            ),
            '[]'::json
        ) as imagenes
    FROM tab_productos p
    JOIN tab_categorias c ON p.id_categoria = c.id_categoria
    JOIN tab_color co ON p.id_color = co.id_color
    JOIN tab_oficios o ON p.id_oficio = o.id_oficio
    JOIN tab_materia_prima m ON p.id_materia = m.id_materia
    JOIN tab_productores pr ON p.id_productor = pr.id_productor
    JOIN tab_users u ON pr.id_user = u.id_user
    JOIN tab_ciudades ci ON pr.id_pais = ci.id_pais AND pr.id_departamento = ci.id_departamento AND pr.id_ciudad = ci.id_ciudad
    JOIN tab_departamentos d ON pr.id_pais = d.id_pais AND pr.id_departamento = d.id_departamento
    LEFT JOIN tab_stand s ON p.id_productor = s.id_productor
    WHERE p.id_producto = p_id_producto
    AND p.is_deleted = FALSE;
END;
$$ LANGUAGE plpgsql;
