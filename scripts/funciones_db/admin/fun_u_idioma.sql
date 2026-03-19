-- Generado automáticamente aplicando Skill: create-sql-function
CREATE OR REPLACE FUNCTION fun_u_idioma(
    p_id_idioma tab_idiomas.id_idioma%TYPE,
    p_nom_idioma tab_idiomas.nom_idioma%TYPE
) RETURNS BOOLEAN AS $$
BEGIN

    -- Validaciones en caliente
    IF p_id_idioma IS NULL THEN
        RAISE NOTICE 'El parámetro p_id_idioma es inválido o nulo.';
        RETURN FALSE;
    END IF;

    IF p_nom_idioma IS NULL THEN
        RAISE NOTICE 'El parámetro p_nom_idioma es inválido o nulo.';
        RETURN FALSE;
    END IF;

    -- Operación DML Pura
    PERFORM 1 FROM tab_idiomas WHERE id_idioma = p_id_idioma;
    IF NOT FOUND THEN 
        RAISE NOTICE 'El registro en tab_idiomas no existe.';
        RETURN FALSE; 
    END IF;

    UPDATE tab_idiomas
    SET nom_idioma = p_nom_idioma
    WHERE id_idioma = p_id_idioma;

    RETURN TRUE;
EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error transaccional en %: %', 'fun_u_idioma', SQLERRM;
        RETURN FALSE;
END;
$$ LANGUAGE plpgsql;
