CREATE OR REPLACE FUNCTION fun_u_parametros(
    p_id_parametro                tab_pmtros.id_parametro%TYPE,
    p_nom_plataforma              tab_pmtros.nom_plataforma%TYPE,
    p_dir_contacto                tab_pmtros.dir_contacto%TYPE,
    p_tel_contacto                tab_pmtros.tel_contacto%TYPE,
    p_correo_contacto             tab_pmtros.correo_contacto%TYPE,
    p_val_poriva                  tab_pmtros.val_poriva%TYPE,
    p_val_inifact                 tab_pmtros.val_inifact%TYPE,
    p_val_finfact                 tab_pmtros.val_finfact%TYPE,
    p_val_actfact                 tab_pmtros.val_actfact%TYPE,
    p_val_observa                 tab_pmtros.val_observa%TYPE
)
RETURNS BOOLEAN AS $$
DECLARE
    v_nom_plataforma   tab_pmtros.nom_plataforma%TYPE;
    v_dir_contacto     tab_pmtros.dir_contacto%TYPE;
    v_tel_contacto     tab_pmtros.tel_contacto%TYPE;
    v_correo_contacto  tab_pmtros.correo_contacto%TYPE;
    v_val_poriva       tab_pmtros.val_poriva%TYPE;
    v_val_inifact      tab_pmtros.val_inifact%TYPE;
    v_val_finfact      tab_pmtros.val_finfact%TYPE;
    v_val_actfact      tab_pmtros.val_actfact%TYPE;
    v_val_observa      tab_pmtros.val_observa%TYPE;
BEGIN
    -- 1. Verificar existencia y obtener valores actuales
    SELECT nom_plataforma, dir_contacto, tel_contacto, correo_contacto,
           val_poriva, val_inifact, val_finfact, val_actfact, val_observa
      INTO v_nom_plataforma, v_dir_contacto, v_tel_contacto, v_correo_contacto,
           v_val_poriva, v_val_inifact, v_val_finfact, v_val_actfact, v_val_observa
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

    IF p_tel_contacto IS NOT NULL AND TRIM(p_tel_contacto) <> '' THEN
        v_tel_contacto := p_tel_contacto;
    END IF;

    IF p_correo_contacto IS NOT NULL AND TRIM(p_correo_contacto) <> '' THEN
        v_correo_contacto := p_correo_contacto;
    END IF;

    IF p_val_poriva IS NOT NULL THEN
        v_val_poriva := p_val_poriva;
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

    -- Validar teléfono (solo números)
    IF v_tel_contacto !~ '^[0-9]+$' THEN
        -- El teléfono solo debe contener números
        RETURN FALSE;
    END IF;

    -- Validar correo electrónico (debe contener @)
    IF v_correo_contacto NOT LIKE '%@%' THEN
        -- El correo electrónico no es válido (debe contener @)
        RETURN FALSE;
    END IF;

    -- Validar porcentaje de IVA (no negativo)
    IF v_val_poriva < 0 THEN
        -- El valor del IVA no puede ser negativo
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
           tel_contacto   = v_tel_contacto,
           correo_contacto= v_correo_contacto,
           val_poriva     = v_val_poriva,
           val_inifact    = v_val_inifact,
           val_finfact    = v_val_finfact,
           val_actfact    = v_val_actfact,
           val_observa    = v_val_observa,
           updated_at     = CURRENT_TIMESTAMP
     WHERE id_parametro   = p_id_parametro;

    RETURN TRUE;

EXCEPTION
    WHEN OTHERS THEN
        -- Error genérico al actualizar parámetros
        RETURN FALSE;
END;
$$ LANGUAGE plpgsql;
