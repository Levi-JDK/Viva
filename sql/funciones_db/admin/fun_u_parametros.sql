-- Generado automáticamente aplicando Skill: create-sql-function
CREATE OR REPLACE FUNCTION fun_u_parametros(
    p_id_parametro tab_pmtros.id_parametro%TYPE,
    p_nom_plataforma tab_pmtros.nom_plataforma%TYPE,
    p_dir_contacto tab_pmtros.dir_contacto%TYPE,
    p_correo_contacto tab_pmtros.correo_contacto%TYPE,
    p_val_inifact tab_pmtros.val_inifact%TYPE,
    p_val_finfact tab_pmtros.val_finfact%TYPE,
    p_val_actfact tab_pmtros.val_actfact%TYPE,
    p_val_observa tab_pmtros.val_observa%TYPE,
    p_foto_hero tab_pmtros.foto_hero%TYPE,
    p_landing_hero_titulo tab_pmtros.landing_hero_titulo%TYPE,
    p_landing_hero_subtitulo tab_pmtros.landing_hero_subtitulo%TYPE,
    p_landing_hero_btn tab_pmtros.landing_hero_btn%TYPE,
    p_landing_conf_1_tit tab_pmtros.landing_conf_1_tit%TYPE,
    p_landing_conf_1_sub tab_pmtros.landing_conf_1_sub%TYPE,
    p_landing_conf_2_tit tab_pmtros.landing_conf_2_tit%TYPE,
    p_landing_conf_2_sub tab_pmtros.landing_conf_2_sub%TYPE,
    p_landing_conf_3_tit tab_pmtros.landing_conf_3_tit%TYPE,
    p_landing_conf_3_sub tab_pmtros.landing_conf_3_sub%TYPE,
    p_landing_filosofia_tit tab_pmtros.landing_filosofia_tit%TYPE,
    p_landing_filosofia_p1 tab_pmtros.landing_filosofia_p1%TYPE,
    p_landing_filosofia_p2 tab_pmtros.landing_filosofia_p2%TYPE
) RETURNS BOOLEAN AS $$
BEGIN

    -- Validaciones en caliente
    IF p_id_parametro IS NULL THEN
        RAISE NOTICE 'El parámetro p_id_parametro es inválido o nulo.';
        RETURN FALSE;
    END IF;

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

    IF p_foto_hero IS NULL THEN
        RAISE NOTICE 'El parámetro p_foto_hero es inválido o nulo.';
        RETURN FALSE;
    END IF;

    IF p_val_observa IS NULL THEN
        RAISE NOTICE 'El parámetro p_val_observa es inválido o nulo.';
        RETURN FALSE;
    END IF;

    IF p_landing_hero_titulo IS NULL THEN
        RAISE NOTICE 'El parámetro p_landing_hero_titulo es inválido o nulo.';
        RETURN FALSE;
    END IF;

    IF p_landing_hero_subtitulo IS NULL THEN
        RAISE NOTICE 'El parámetro p_landing_hero_subtitulo es inválido o nulo.';
        RETURN FALSE;
    END IF;

    IF p_landing_hero_btn IS NULL THEN
        RAISE NOTICE 'El parámetro p_landing_hero_btn es inválido o nulo.';
        RETURN FALSE;
    END IF;

    IF p_landing_conf_1_tit IS NULL THEN
        RAISE NOTICE 'El parámetro p_landing_conf_1_tit es inválido o nulo.';
        RETURN FALSE;
    END IF;

    IF p_landing_conf_1_sub IS NULL THEN
        RAISE NOTICE 'El parámetro p_landing_conf_1_sub es inválido o nulo.';
        RETURN FALSE;
    END IF;

    IF p_landing_conf_2_tit IS NULL THEN
        RAISE NOTICE 'El parámetro p_landing_conf_2_tit es inválido o nulo.';
        RETURN FALSE;
    END IF;

    IF p_landing_conf_2_sub IS NULL THEN
        RAISE NOTICE 'El parámetro p_landing_conf_2_sub es inválido o nulo.';
        RETURN FALSE;
    END IF;

    IF p_landing_conf_3_tit IS NULL THEN
        RAISE NOTICE 'El parámetro p_landing_conf_3_tit es inválido o nulo.';
        RETURN FALSE;
    END IF;

    IF p_landing_conf_3_sub IS NULL THEN
        RAISE NOTICE 'El parámetro p_landing_conf_3_sub es inválido o nulo.';
        RETURN FALSE;
    END IF;

    IF p_landing_filosofia_tit IS NULL THEN
        RAISE NOTICE 'El parámetro p_landing_filosofia_tit es inválido o nulo.';
        RETURN FALSE;
    END IF;

    IF p_landing_filosofia_p1 IS NULL THEN
        RAISE NOTICE 'El parámetro p_landing_filosofia_p1 es inválido o nulo.';
        RETURN FALSE;
    END IF;

    IF p_landing_filosofia_p2 IS NULL THEN
        RAISE NOTICE 'El parámetro p_landing_filosofia_p2 es inválido o nulo.';
        RETURN FALSE;
    END IF;

    -- Operación DML Pura
    PERFORM 1 FROM tab_pmtros WHERE id_parametro = p_id_parametro;
    IF NOT FOUND THEN 
        RAISE NOTICE 'El registro en tab_pmtros no existe.';
        RETURN FALSE; 
    END IF;

    UPDATE tab_pmtros
    SET nom_plataforma = p_nom_plataforma,
        dir_contacto = p_dir_contacto,
        correo_contacto = p_correo_contacto,
        val_inifact = p_val_inifact,
        val_finfact = p_val_finfact,
        val_actfact = p_val_actfact,
        val_observa = p_val_observa,
        foto_hero = p_foto_hero,
        landing_hero_titulo = p_landing_hero_titulo,
        landing_hero_subtitulo = p_landing_hero_subtitulo,
        landing_hero_btn = p_landing_hero_btn,
        landing_conf_1_tit = p_landing_conf_1_tit,
        landing_conf_1_sub = p_landing_conf_1_sub,
        landing_conf_2_tit = p_landing_conf_2_tit,
        landing_conf_2_sub = p_landing_conf_2_sub,
        landing_conf_3_tit = p_landing_conf_3_tit,
        landing_conf_3_sub = p_landing_conf_3_sub,
        landing_filosofia_tit = p_landing_filosofia_tit,
        landing_filosofia_p1 = p_landing_filosofia_p1,
        landing_filosofia_p2 = p_landing_filosofia_p2
    WHERE id_parametro = p_id_parametro;

    RETURN TRUE;
EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error transaccional en %: %', 'fun_u_parametros', SQLERRM;
        RETURN FALSE;
END;
$$ LANGUAGE plpgsql;
