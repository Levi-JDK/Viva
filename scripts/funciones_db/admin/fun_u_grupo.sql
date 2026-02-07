CREATE OR REPLACE FUNCTION fun_u_grupo(
    p_id_grupo  tab_grupos.id_grupo%TYPE,
    p_nom_grupo tab_grupos.nom_grupo%TYPE
) RETURNS BOOLEAN AS $$
DECLARE
    v_nom_grupo tab_grupos.nom_grupo%TYPE;
BEGIN
    -- Validacion: ID grupo requerido
    IF p_id_grupo IS NULL THEN RETURN FALSE; END IF;

    SELECT nom_grupo INTO v_nom_grupo FROM tab_grupos WHERE id_grupo = p_id_grupo AND is_deleted = FALSE;
    -- Validacion: Grupo no encontrado
    IF NOT FOUND THEN RETURN FALSE; END IF;

    IF p_nom_grupo IS NOT NULL AND TRIM(p_nom_grupo) <> '' THEN v_nom_grupo := p_nom_grupo; END IF;

    UPDATE tab_grupos
       SET nom_grupo  = v_nom_grupo,
           updated_by = current_user,
           updated_at = CURRENT_TIMESTAMP
     WHERE id_grupo   = p_id_grupo;

    RETURN TRUE;
END;
$$ LANGUAGE plpgsql;
