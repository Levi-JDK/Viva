CREATE OR REPLACE FUNCTION fun_u_tipo_doc(
    p_id_tipo_doc  tab_tipos_doc.id_tipo_doc%TYPE,
    p_nom_tipo_doc tab_tipos_doc.nom_tipo_doc%TYPE
) RETURNS BOOLEAN AS $$
DECLARE
    v_nom_tipo_doc tab_tipos_doc.nom_tipo_doc%TYPE;
BEGIN
    IF p_id_tipo_doc IS NULL THEN
        -- Validacion: El ID del tipo de documento es obligatorio.
        RETURN FALSE;
    END IF;

    SELECT nom_tipo_doc INTO v_nom_tipo_doc
      FROM tab_tipos_doc
     WHERE id_tipo_doc = p_id_tipo_doc
       AND is_deleted = FALSE;

    IF NOT FOUND THEN
        -- Validacion: Tipo de documento no encontrado o eliminado.
        RETURN FALSE;
    END IF;

    IF p_nom_tipo_doc IS NOT NULL AND TRIM(p_nom_tipo_doc) <> '' THEN
        v_nom_tipo_doc := p_nom_tipo_doc;
    END IF;

    UPDATE tab_tipos_doc
       SET nom_tipo_doc = v_nom_tipo_doc,
           updated_by   = current_user,
           updated_at   = CURRENT_TIMESTAMP
     WHERE id_tipo_doc  = p_id_tipo_doc;

    RETURN TRUE;
END;
$$ LANGUAGE plpgsql;
