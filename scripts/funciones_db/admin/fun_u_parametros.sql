CREATE OR REPLACE FUNCTION fun_u_parametros(
    p_id_parametro                tab_pmtros.id_parametro%TYPE,
    p_nom_plataforma              tab_pmtros.nom_plataforma%TYPE,
    p_dir_contacto                tab_pmtros.dir_contacto%TYPE,
    p_correo_contacto             tab_pmtros.correo_contacto%TYPE,
    p_val_inifact                 tab_pmtros.val_inifact%TYPE,
    p_val_finfact                 tab_pmtros.val_finfact%TYPE,
    p_val_actfact                 tab_pmtros.val_actfact%TYPE,
    p_val_observa                 tab_pmtros.val_observa%TYPE,
    p_landing_hero_titulo         tab_pmtros.landing_hero_titulo%TYPE,
    p_landing_hero_subtitulo      tab_pmtros.landing_hero_subtitulo%TYPE,
    p_landing_hero_btn            tab_pmtros.landing_hero_btn%TYPE,
    p_landing_conf_1_tit          tab_pmtros.landing_conf_1_tit%TYPE,
    p_landing_conf_1_sub         tab_pmtros.landing_conf_1_sub%TYPE,
    p_landing_conf_2_tit          tab_pmtros.landing_conf_2_tit%TYPE,
    p_landing_conf_2_sub         tab_pmtros.landing_conf_2_sub%TYPE,
    p_landing_conf_3_tit          tab_pmtros.landing_conf_3_tit%TYPE,
    p_landing_conf_3_sub         tab_pmtros.landing_conf_3_sub%TYPE,
    p_landing_filosofia_tit       tab_pmtros.landing_filosofia_tit%TYPE,
    p_landing_filosofia_p1        tab_pmtros.landing_filosofia_p1%TYPE,
    p_landing_filosofia_p2        tab_pmtros.landing_filosofia_p2%TYPE
)
RETURNS BOOLEAN AS $$
DECLARE
    v_nom_plataforma   tab_pmtros.nom_plataforma%TYPE;
    v_dir_contacto     tab_pmtros.dir_contacto%TYPE;
    v_correo_contacto  tab_pmtros.correo_contacto%TYPE;
    v_val_inifact      tab_pmtros.val_inifact%TYPE;
    v_val_finfact      tab_pmtros.val_finfact%TYPE;
    v_val_actfact      tab_pmtros.val_actfact%TYPE;
    v_val_observa      tab_pmtros.val_observa%TYPE;
    v_landing_hero_titulo         tab_pmtros.landing_hero_titulo%TYPE;
    v_landing_hero_subtitulo      tab_pmtros.landing_hero_subtitulo%TYPE;
    v_landing_hero_btn            tab_pmtros.landing_hero_btn%TYPE;
    v_landing_conf_1_tit          tab_pmtros.landing_conf_1_tit%TYPE;
    v_landing_conf_1_sub         tab_pmtros.landing_conf_1_sub%TYPE;
    v_landing_conf_2_tit          tab_pmtros.landing_conf_2_tit%TYPE;
    v_landing_conf_2_sub         tab_pmtros.landing_conf_2_sub%TYPE;
    v_landing_conf_3_tit          tab_pmtros.landing_conf_3_tit%TYPE;
    v_landing_conf_3_sub         tab_pmtros.landing_conf_3_sub%TYPE;
    v_landing_filosofia_tit       tab_pmtros.landing_filosofia_tit%TYPE;
    v_landing_filosofia_p1        tab_pmtros.landing_filosofia_p1%TYPE;
    v_landing_filosofia_p2        tab_pmtros.landing_filosofia_p2%TYPE;
BEGIN
    -- 1. Verificar existencia y obtener valores actuales
    SELECT nom_plataforma, dir_contacto, correo_contacto,
           val_inifact, val_finfact, val_actfact, val_observa,
           landing_hero_titulo, landing_hero_subtitulo, landing_hero_btn,
           landing_conf_1_tit, landing_conf_1_sub, landing_conf_2_tit, landing_conf_2_sub,
           landing_conf_3_tit, landing_conf_3_sub, landing_filosofia_tit, landing_filosofia_p1, landing_filosofia_p2
      INTO v_nom_plataforma, v_dir_contacto, v_correo_contacto,
           v_val_inifact, v_val_finfact, v_val_actfact, v_val_observa,
           v_landing_hero_titulo, v_landing_hero_subtitulo, v_landing_hero_btn,
           v_landing_conf_1_tit, v_landing_conf_1_sub, v_landing_conf_2_tit, v_landing_conf_2_sub,
           v_landing_conf_3_tit, v_landing_conf_3_sub, v_landing_filosofia_tit, v_landing_filosofia_p1, v_landing_filosofia_p2
      FROM tab_pmtros
     WHERE id_parametro = p_id_parametro;

    IF NOT FOUND THEN
        RETURN FALSE;
    END IF;

    -- 2. Asignar nuevos valores si no son NULL o vacíos (para texto)
    IF p_nom_plataforma IS NOT NULL AND TRIM(p_nom_plataforma) <> '' THEN
        v_nom_plataforma := p_nom_plataforma;
    END IF;

    IF p_dir_contacto IS NOT NULL AND TRIM(p_dir_contacto) <> '' THEN
        v_dir_contacto := p_dir_contacto;
    END IF;

    IF p_correo_contacto IS NOT NULL AND TRIM(p_correo_contacto) <> '' THEN
        v_correo_contacto := p_correo_contacto;
    END IF;

    IF p_val_inifact IS NOT NULL THEN
        v_val_inifact := p_val_inifact;
    END IF;

    IF p_val_finfact IS NOT NULL THEN
        v_val_finfact := p_val_finfact;
    END IF;

    IF p_val_actfact IS NOT NULL THEN
        v_val_actfact := p_val_actfact;
    END IF;

    IF p_val_observa IS NOT NULL THEN
        v_val_observa := p_val_observa; -- Puede ser texto vacío si se desea limpiar
    END IF;

    -- Textos Landing
    IF p_landing_hero_titulo IS NOT NULL AND TRIM(p_landing_hero_titulo) <> '' THEN
        v_landing_hero_titulo := p_landing_hero_titulo;
    END IF;
    IF p_landing_hero_subtitulo IS NOT NULL AND TRIM(p_landing_hero_subtitulo) <> '' THEN
        v_landing_hero_subtitulo := p_landing_hero_subtitulo;
    END IF;
    IF p_landing_hero_btn IS NOT NULL AND TRIM(p_landing_hero_btn) <> '' THEN
        v_landing_hero_btn := p_landing_hero_btn;
    END IF;
    IF p_landing_conf_1_tit IS NOT NULL AND TRIM(p_landing_conf_1_tit) <> '' THEN
        v_landing_conf_1_tit := p_landing_conf_1_tit;
    END IF;
    IF p_landing_conf_1_sub IS NOT NULL AND TRIM(p_landing_conf_1_sub) <> '' THEN
        v_landing_conf_1_sub := p_landing_conf_1_sub;
    END IF;
    IF p_landing_conf_2_tit IS NOT NULL AND TRIM(p_landing_conf_2_tit) <> '' THEN
        v_landing_conf_2_tit := p_landing_conf_2_tit;
    END IF;
    IF p_landing_conf_2_sub IS NOT NULL AND TRIM(p_landing_conf_2_sub) <> '' THEN
        v_landing_conf_2_sub := p_landing_conf_2_sub;
    END IF;
    IF p_landing_conf_3_tit IS NOT NULL AND TRIM(p_landing_conf_3_tit) <> '' THEN
        v_landing_conf_3_tit := p_landing_conf_3_tit;
    END IF;
    IF p_landing_conf_3_sub IS NOT NULL AND TRIM(p_landing_conf_3_sub) <> '' THEN
        v_landing_conf_3_sub := p_landing_conf_3_sub;
    END IF;
    IF p_landing_filosofia_tit IS NOT NULL AND TRIM(p_landing_filosofia_tit) <> '' THEN
        v_landing_filosofia_tit := p_landing_filosofia_tit;
    END IF;
    IF p_landing_filosofia_p1 IS NOT NULL AND TRIM(p_landing_filosofia_p1) <> '' THEN
        v_landing_filosofia_p1 := p_landing_filosofia_p1;
    END IF;
    IF p_landing_filosofia_p2 IS NOT NULL AND TRIM(p_landing_filosofia_p2) <> '' THEN
        v_landing_filosofia_p2 := p_landing_filosofia_p2;
    END IF;


    -- 3. Validaciones sobre los valores FINALIZADOS

    -- Validar nombre de plataforma
    IF v_nom_plataforma IS NULL OR TRIM(v_nom_plataforma) = '' THEN
        -- El nombre de la plataforma es obligatorio
        RETURN FALSE;
    END IF;

    -- Validar dirección de contacto
    IF v_dir_contacto IS NULL OR TRIM(v_dir_contacto) = '' THEN
        -- La dirección de contacto es obligatoria
        RETURN FALSE;
    END IF;

    -- Validar correo electrónico (debe contener @)
    IF v_correo_contacto NOT LIKE '%@%' THEN
        -- El correo electrónico no es válido (debe contener @)
        RETURN FALSE;
    END IF;

    -- Validar rangos de facturación
    IF v_val_inifact < 0 OR v_val_finfact < 0 OR v_val_actfact < 0 THEN
         -- Los valores de facturación no pueden ser negativos
         RETURN FALSE;
    END IF;

    IF v_val_inifact >= v_val_finfact THEN
        -- El inicio de facturación debe ser menor al fin de facturación
        RETURN FALSE;
    END IF;

    IF v_val_actfact < v_val_inifact OR v_val_actfact > v_val_finfact THEN
         -- La factura actual debe estar dentro del rango definido
         RETURN FALSE;
    END IF;


    -- 4. Actualizar registro
    UPDATE tab_pmtros
       SET nom_plataforma = v_nom_plataforma,
           dir_contacto   = v_dir_contacto,
           correo_contacto= v_correo_contacto,
           val_inifact    = v_val_inifact,
           val_finfact    = v_val_finfact,
           val_actfact    = v_val_actfact,
           val_observa    = v_val_observa,
           landing_hero_titulo = v_landing_hero_titulo,
           landing_hero_subtitulo = v_landing_hero_subtitulo,
           landing_hero_btn = v_landing_hero_btn,
           landing_conf_1_tit = v_landing_conf_1_tit,
           landing_conf_1_sub = v_landing_conf_1_sub,
           landing_conf_2_tit = v_landing_conf_2_tit,
           landing_conf_2_sub = v_landing_conf_2_sub,
           landing_conf_3_tit = v_landing_conf_3_tit,
           landing_conf_3_sub = v_landing_conf_3_sub,
           landing_filosofia_tit = v_landing_filosofia_tit,
           landing_filosofia_p1 = v_landing_filosofia_p1,
           landing_filosofia_p2 = v_landing_filosofia_p2,
           updated_at     = CURRENT_TIMESTAMP
     WHERE id_parametro   = p_id_parametro;

    RETURN TRUE;

EXCEPTION
    WHEN OTHERS THEN
        -- Error genérico al actualizar parámetros
        RETURN FALSE;
END;
$$ LANGUAGE plpgsql;
