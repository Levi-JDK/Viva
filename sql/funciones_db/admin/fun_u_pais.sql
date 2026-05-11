-- Generado automáticamente aplicando Skill: create-sql-function
CREATE OR REPLACE FUNCTION fun_u_pais(
    p_id_pais tab_paises.id_pais%TYPE,
    p_cod_iso tab_paises.cod_iso%TYPE,
    p_nom_pais tab_paises.nom_pais%TYPE,
    p_arancel_pct tab_paises.arancel_pct%TYPE
) RETURNS BOOLEAN AS $$
BEGIN

    -- Validaciones en caliente
    IF p_id_pais IS NULL THEN
        RAISE NOTICE 'El parámetro p_id_pais es inválido o nulo.';
        RETURN FALSE;
    END IF;

    IF p_cod_iso IS NULL THEN
        RAISE NOTICE 'El parámetro p_cod_iso es inválido o nulo.';
        RETURN FALSE;
    END IF;

    IF p_nom_pais IS NULL THEN
        RAISE NOTICE 'El parámetro p_nom_pais es inválido o nulo.';
        RETURN FALSE;
    END IF;

    IF p_arancel_pct IS NULL THEN
        RAISE NOTICE 'El parámetro p_arancel_pct es inválido o nulo.';
        RETURN FALSE;
    END IF;

    -- Operación DML Pura
    PERFORM 1 FROM tab_paises WHERE id_pais = p_id_pais;
    IF NOT FOUND THEN 
        RAISE NOTICE 'El registro en tab_paises no existe.';
        RETURN FALSE; 
    END IF;

    UPDATE tab_paises
    SET cod_iso = p_cod_iso,
        nom_pais = p_nom_pais,
        arancel_pct = p_arancel_pct
    WHERE id_pais = p_id_pais;

    RETURN TRUE;
EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error transaccional en %: %', 'fun_u_pais', SQLERRM;
        RETURN FALSE;
END;
$$ LANGUAGE plpgsql;
