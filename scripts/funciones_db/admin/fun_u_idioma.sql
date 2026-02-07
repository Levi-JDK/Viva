CREATE OR REPLACE FUNCTION fun_u_idioma(
    p_id_idioma  tab_idiomas.id_idioma%TYPE,
    p_nom_idioma tab_idiomas.nom_idioma%TYPE
) RETURNS BOOLEAN AS $$
DECLARE
    v_nom_idioma tab_idiomas.nom_idioma%TYPE;
BEGIN
    -- Validacion: ID idioma requerido
    IF p_id_idioma IS NULL OR TRIM(p_id_idioma) = '' THEN RETURN FALSE; END IF;

    SELECT nom_idioma INTO v_nom_idioma FROM tab_idiomas WHERE id_idioma = p_id_idioma AND is_deleted = FALSE;
    -- Validacion: Idioma no encontrado
    IF NOT FOUND THEN RETURN FALSE; END IF;

    IF p_nom_idioma IS NOT NULL AND TRIM(p_nom_idioma) <> '' THEN v_nom_idioma := p_nom_idioma; END IF;

    UPDATE tab_idiomas
       SET nom_idioma = v_nom_idioma,
           updated_by = current_user,
           updated_at = CURRENT_TIMESTAMP
     WHERE id_idioma  = p_id_idioma;

    RETURN TRUE;
END;
$$ LANGUAGE plpgsql;
