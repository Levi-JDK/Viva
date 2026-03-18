CREATE OR REPLACE FUNCTION fun_c_parametros(
    p_nom_plataforma              tab_pmtros.nom_plataforma%TYPE,
    p_dir_contacto                tab_pmtros.dir_contacto%TYPE,
    p_correo_contacto             tab_pmtros.correo_contacto%TYPE,
    p_val_inifact                 tab_pmtros.val_inifact%TYPE,
    p_val_finfact                 tab_pmtros.val_finfact%TYPE,
    p_val_actfact                 tab_pmtros.val_actfact%TYPE,
    p_val_observa                 tab_pmtros.val_observa%TYPE
)
RETURNS BOOLEAN AS $$
BEGIN
    -- Validar nombre de plataforma
    IF p_nom_plataforma IS NULL OR TRIM(p_nom_plataforma) = '' THEN
        RETURN FALSE;
    END IF;

    -- Validar dirección de contacto
    IF p_dir_contacto IS NULL OR TRIM(p_dir_contacto) = '' THEN
        RETURN FALSE;
    END IF;

    -- Validar correo electrónico (debe contener @)
    IF p_correo_contacto NOT LIKE '%@%' THEN
        RETURN FALSE;
    END IF;

    -- Validar rangos de facturación
    IF p_val_inifact < 0 OR p_val_finfact < 0 OR p_val_actfact < 0 THEN
         RETURN FALSE;
    END IF;

    IF p_val_inifact >= p_val_finfact THEN
        RETURN FALSE;
    END IF;

    IF p_val_actfact < p_val_inifact OR p_val_actfact > p_val_finfact THEN
         RETURN FALSE;
    END IF;

    RETURN true;
END;
$$ LANGUAGE plpgsql;