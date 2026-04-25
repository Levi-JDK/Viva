-- Generado automáticamente aplicando Skill: create-sql-function
CREATE OR REPLACE FUNCTION fun_c_pais(
        p_cod_iso tab_paises.cod_iso%TYPE,
    p_nom_pais tab_paises.nom_pais%TYPE,
    p_arancel_pct tab_paises.arancel_pct%TYPE
) RETURNS BOOLEAN AS $$
DECLARE
    v_id_pais tab_paises.id_pais%TYPE;
BEGIN

    -- Validaciones en caliente
    

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

    
    v_id_pais := COALESCE((SELECT MAX(id_pais) FROM tab_paises), 0) + 1;

    INSERT INTO tab_paises (id_pais, cod_iso, nom_pais, arancel_pct)
    VALUES (v_id_pais, p_cod_iso, p_nom_pais, p_arancel_pct);

    RETURN TRUE;
EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error transaccional en %: %', 'fun_c_pais', SQLERRM;
        RETURN FALSE;
END;
$$ LANGUAGE plpgsql;
