CREATE OR REPLACE FUNCTION fun_c_categoria(
    p_id_categoria  tab_categorias.id_categoria%TYPE,
    p_nom_categoria tab_categorias.nom_categoria%TYPE
) RETURNS BOOLEAN AS $$
BEGIN
    -- Validacion: ID categoría requerido
    IF p_id_categoria IS NULL THEN RETURN FALSE; END IF;
    -- Validacion: Nombre categoría requerido
    IF p_nom_categoria IS NULL OR TRIM(p_nom_categoria) = '' THEN RETURN FALSE; END IF;

    PERFORM 1 FROM tab_categorias WHERE id_categoria = p_id_categoria;
    -- Validacion: La categoría ya existe
    IF FOUND THEN RETURN FALSE; END IF;

    INSERT INTO tab_categorias (id_categoria, nom_categoria)
    VALUES (p_id_categoria, p_nom_categoria);

    RETURN TRUE;
END;
$$ LANGUAGE plpgsql;
