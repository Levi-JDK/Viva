-- Generado automáticamente aplicando Skill: create-sql-function
CREATE OR REPLACE FUNCTION fun_c_tipo_doc(
        p_nom_tipo_doc tab_tipos_doc.nom_tipo_doc%TYPE
) RETURNS BOOLEAN AS $$
DECLARE
    v_id_tipo_doc tab_tipos_doc.id_tipo_doc%TYPE;
BEGIN

    -- Validaciones en caliente
    

    IF p_nom_tipo_doc IS NULL THEN
        RAISE NOTICE 'El parámetro p_nom_tipo_doc es inválido o nulo.';
        RETURN FALSE;
    END IF;

    -- Operación DML Pura

    
    v_id_tipo_doc := COALESCE((SELECT MAX(id_tipo_doc) FROM tab_tipos_doc), 0) + 1;

    INSERT INTO tab_tipos_doc (id_tipo_doc, nom_tipo_doc)
    VALUES (v_id_tipo_doc, p_nom_tipo_doc);

    RETURN TRUE;
EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error transaccional en %: %', 'fun_c_tipo_doc', SQLERRM;
        RETURN FALSE;
END;
$$ LANGUAGE plpgsql;
