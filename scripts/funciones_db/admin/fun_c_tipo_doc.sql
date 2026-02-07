CREATE OR REPLACE FUNCTION fun_c_tipo_doc(
    p_id_tipo_doc  tab_tipos_doc.id_tipo_doc%TYPE,
    p_nom_tipo_doc tab_tipos_doc.nom_tipo_doc%TYPE
) RETURNS BOOLEAN AS $$
BEGIN
    IF p_id_tipo_doc IS NULL THEN
        -- Validacion: El ID del tipo de documento es obligatorio.
        RETURN FALSE;
    END IF;

    IF p_nom_tipo_doc IS NULL OR TRIM(p_nom_tipo_doc) = '' THEN
        -- Validacion: El nombre del tipo de documento es obligatorio.
        RETURN FALSE;
    END IF;

    PERFORM 1 FROM tab_tipos_doc WHERE id_tipo_doc = p_id_tipo_doc;
    IF FOUND THEN
        -- Validacion: El ID ya existe en tipos de documento.
        RETURN FALSE;
    END IF;

    INSERT INTO tab_tipos_doc (id_tipo_doc, nom_tipo_doc)
    VALUES (p_id_tipo_doc, p_nom_tipo_doc);

    RETURN TRUE;
END;
$$ LANGUAGE plpgsql;
