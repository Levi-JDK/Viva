-- Generado automáticamente aplicando Skill: create-sql-function
CREATE OR REPLACE FUNCTION fun_u_tipo_doc(
    p_id_tipo_doc tab_tipos_doc.id_tipo_doc%TYPE,
    p_nom_tipo_doc tab_tipos_doc.nom_tipo_doc%TYPE
) RETURNS BOOLEAN AS $$
BEGIN

    -- Validaciones en caliente
    IF p_id_tipo_doc IS NULL THEN
        RAISE NOTICE 'El parámetro p_id_tipo_doc es inválido o nulo.';
        RETURN FALSE;
    END IF;

    IF p_nom_tipo_doc IS NULL THEN
        RAISE NOTICE 'El parámetro p_nom_tipo_doc es inválido o nulo.';
        RETURN FALSE;
    END IF;

    -- Operación DML Pura
    PERFORM 1 FROM tab_tipos_doc WHERE id_tipo_doc = p_id_tipo_doc;
    IF NOT FOUND THEN 
        RAISE NOTICE 'El registro en tab_tipos_doc no existe.';
        RETURN FALSE; 
    END IF;

    UPDATE tab_tipos_doc
    SET nom_tipo_doc = p_nom_tipo_doc
    WHERE id_tipo_doc = p_id_tipo_doc;

    RETURN TRUE;
EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error transaccional en %: %', 'fun_u_tipo_doc', SQLERRM;
        RETURN FALSE;
END;
$$ LANGUAGE plpgsql;
