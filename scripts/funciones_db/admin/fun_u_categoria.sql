CREATE OR REPLACE FUNCTION fun_u_categoria(
    p_id_categoria  tab_categorias.id_categoria%TYPE,
    p_nom_categoria tab_categorias.nom_categoria%TYPE
) RETURNS BOOLEAN AS $$
DECLARE
    v_nom_categoria tab_categorias.nom_categoria%TYPE;
BEGIN
    -- Validacion: ID categoría requerido
    IF p_id_categoria IS NULL THEN RETURN FALSE; END IF;

    SELECT nom_categoria INTO v_nom_categoria FROM tab_categorias WHERE id_categoria = p_id_categoria AND is_deleted = FALSE;
    -- Validacion: Categoría no encontrada
    IF NOT FOUND THEN RETURN FALSE; END IF;

    IF p_nom_categoria IS NOT NULL AND TRIM(p_nom_categoria) <> '' THEN v_nom_categoria := p_nom_categoria; END IF;

    UPDATE tab_categorias
       SET nom_categoria = v_nom_categoria,
           updated_by    = current_user,
           updated_at    = CURRENT_TIMESTAMP
     WHERE id_categoria  = p_id_categoria;

    RETURN TRUE;
END;
$$ LANGUAGE plpgsql;
