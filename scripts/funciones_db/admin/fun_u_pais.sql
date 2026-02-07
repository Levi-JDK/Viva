CREATE OR REPLACE FUNCTION fun_u_pais(
    p_id_pais     tab_paises.id_pais%TYPE,
    p_cod_iso     tab_paises.cod_iso%TYPE,
    p_nom_pais    tab_paises.nom_pais%TYPE,
    p_arancel_pct tab_paises.arancel_pct%TYPE
) RETURNS BOOLEAN AS $$
DECLARE
    v_cod_iso     tab_paises.cod_iso%TYPE;
    v_nom_pais    tab_paises.nom_pais%TYPE;
    v_arancel_pct tab_paises.arancel_pct%TYPE;
BEGIN
    -- Validacion: ID país requerido para actualizar
    IF p_id_pais IS NULL THEN RETURN FALSE; END IF;

    SELECT cod_iso, nom_pais, arancel_pct
      INTO v_cod_iso, v_nom_pais, v_arancel_pct
      FROM tab_paises
     WHERE id_pais = p_id_pais AND is_deleted = FALSE;

    -- Validacion: País no encontrado
    IF NOT FOUND THEN RETURN FALSE; END IF;

    IF p_cod_iso IS NOT NULL THEN v_cod_iso := p_cod_iso; END IF;
    IF p_nom_pais IS NOT NULL AND TRIM(p_nom_pais) <> '' THEN v_nom_pais := p_nom_pais; END IF;
    IF p_arancel_pct IS NOT NULL THEN v_arancel_pct := p_arancel_pct; END IF;

    UPDATE tab_paises
       SET cod_iso     = v_cod_iso,
           nom_pais    = v_nom_pais,
           arancel_pct = v_arancel_pct,
           updated_by  = current_user,
           updated_at  = CURRENT_TIMESTAMP
     WHERE id_pais     = p_id_pais;

    RETURN TRUE;
END;
$$ LANGUAGE plpgsql;
