-- Generado automáticamente aplicando Skill: create-sql-function
CREATE OR REPLACE FUNCTION fun_u_oficio(
    p_id_oficio tab_oficios.id_oficio%TYPE,
    p_nom_oficio tab_oficios.nom_oficio%TYPE
) RETURNS BOOLEAN AS $$
BEGIN

    -- Validaciones en caliente
    IF p_id_oficio IS NULL THEN
        RAISE NOTICE 'El parámetro p_id_oficio es inválido o nulo.';
        RETURN FALSE;
    END IF;

    IF p_nom_oficio IS NULL THEN
        RAISE NOTICE 'El parámetro p_nom_oficio es inválido o nulo.';
        RETURN FALSE;
    END IF;

    -- Operación DML Pura
    PERFORM 1 FROM tab_oficios WHERE id_oficio = p_id_oficio;
    IF NOT FOUND THEN 
        RAISE NOTICE 'El registro en tab_oficios no existe.';
        RETURN FALSE; 
    END IF;

    UPDATE tab_oficios
    SET nom_oficio = p_nom_oficio
    WHERE id_oficio = p_id_oficio;

    RETURN TRUE;
EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error transaccional en %: %', 'fun_u_oficio', SQLERRM;
        RETURN FALSE;
END;
$$ LANGUAGE plpgsql;
