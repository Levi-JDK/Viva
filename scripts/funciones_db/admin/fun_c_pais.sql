CREATE OR REPLACE FUNCTION fun_c_pais(
    p_id_pais     tab_paises.id_pais%TYPE,
    p_cod_iso     tab_paises.cod_iso%TYPE,
    p_nom_pais    tab_paises.nom_pais%TYPE,
    p_arancel_pct tab_paises.arancel_pct%TYPE
) RETURNS BOOLEAN AS $$
BEGIN
    -- Validacion: ID país requerido
    IF p_id_pais IS NULL THEN RETURN FALSE; END IF;
    -- Validacion: Código ISO requerido
    IF p_cod_iso IS NULL THEN RETURN FALSE; END IF;
    -- Validacion: Nombre país requerido
    IF p_nom_pais IS NULL OR TRIM(p_nom_pais) = '' THEN RETURN FALSE; END IF;
    -- arancel can be 0 or null (if null, default 0?) - let's enforce non-negative

    PERFORM 1 FROM tab_paises WHERE id_pais = p_id_pais;
    -- Validacion: País ya existe
    IF FOUND THEN RETURN FALSE; END IF;

    INSERT INTO tab_paises (id_pais, cod_iso, nom_pais, arancel_pct)
    VALUES (p_id_pais, p_cod_iso, p_nom_pais, COALESCE(p_arancel_pct, 0));

    RETURN TRUE;
END;
$$ LANGUAGE plpgsql;
