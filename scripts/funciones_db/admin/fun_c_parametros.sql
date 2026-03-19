-- Generado automáticamente aplicando Skill: create-sql-function
CREATE OR REPLACE FUNCTION fun_c_parametros(
    p_nom_plataforma tab_pmtros.nom_plataforma%TYPE,
    p_dir_contacto tab_pmtros.dir_contacto%TYPE,
    p_correo_contacto tab_pmtros.correo_contacto%TYPE,
    p_val_inifact tab_pmtros.val_inifact%TYPE,
    p_val_finfact tab_pmtros.val_finfact%TYPE,
    p_val_actfact tab_pmtros.val_actfact%TYPE,
    p_val_observa tab_pmtros.val_observa%TYPE
) RETURNS BOOLEAN AS $$
BEGIN

    -- Validaciones en caliente
    IF p_nom_plataforma IS NULL THEN
        RAISE NOTICE 'El parámetro p_nom_plataforma es inválido o nulo.';
        RETURN FALSE;
    END IF;

    IF p_dir_contacto IS NULL THEN
        RAISE NOTICE 'El parámetro p_dir_contacto es inválido o nulo.';
        RETURN FALSE;
    END IF;

    IF p_correo_contacto IS NULL THEN
        RAISE NOTICE 'El parámetro p_correo_contacto es inválido o nulo.';
        RETURN FALSE;
    END IF;

    IF p_val_inifact IS NULL THEN
        RAISE NOTICE 'El parámetro p_val_inifact es inválido o nulo.';
        RETURN FALSE;
    END IF;

    IF p_val_finfact IS NULL THEN
        RAISE NOTICE 'El parámetro p_val_finfact es inválido o nulo.';
        RETURN FALSE;
    END IF;

    IF p_val_actfact IS NULL THEN
        RAISE NOTICE 'El parámetro p_val_actfact es inválido o nulo.';
        RETURN FALSE;
    END IF;

    IF p_val_observa IS NULL THEN
        RAISE NOTICE 'El parámetro p_val_observa es inválido o nulo.';
        RETURN FALSE;
    END IF;

    -- Operación DML Pura
    PERFORM 1 FROM tab_pmtros WHERE nom_plataforma = p_nom_plataforma;
    IF FOUND THEN 
        RAISE NOTICE 'El registro en tab_pmtros ya existe.';
        RETURN FALSE; 
    END IF;

    INSERT INTO tab_pmtros (nom_plataforma, dir_contacto, correo_contacto, val_inifact, val_finfact, val_actfact, val_observa)
    VALUES (p_nom_plataforma, p_dir_contacto, p_correo_contacto, p_val_inifact, p_val_finfact, p_val_actfact, p_val_observa);

    RETURN TRUE;
EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error transaccional en %: %', 'fun_c_parametros', SQLERRM;
        RETURN FALSE;
END;
$$ LANGUAGE plpgsql;
