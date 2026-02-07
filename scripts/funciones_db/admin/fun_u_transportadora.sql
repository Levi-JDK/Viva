CREATE OR REPLACE FUNCTION fun_u_transportadora(
    p_id_transportador  tab_transportadoras.id_transportador%TYPE,
    p_nom_transportador tab_transportadoras.nom_transportador%TYPE,
    p_tipo_transporte   tab_transportadoras.tipo_transporte%TYPE,
    p_tel_contacto      tab_transportadoras.tel_contacto%TYPE,
    p_correo_contacto   tab_transportadoras.correo_contacto%TYPE,
    p_sitio_web         tab_transportadoras.sitio_web%TYPE,
    p_activo            tab_transportadoras.activo%TYPE
) RETURNS BOOLEAN AS $$
DECLARE
    v_nom            tab_transportadoras.nom_transportador%TYPE;
    v_tipo           tab_transportadoras.tipo_transporte%TYPE;
    v_tel            tab_transportadoras.tel_contacto%TYPE;
    v_correo         tab_transportadoras.correo_contacto%TYPE;
    v_web            tab_transportadoras.sitio_web%TYPE;
    v_activo         tab_transportadoras.activo%TYPE;
BEGIN
    -- Validacion: ID requerido
    IF p_id_transportador IS NULL OR TRIM(p_id_transportador) = '' THEN RETURN FALSE; END IF;

    SELECT nom_transportador, tipo_transporte, tel_contacto, correo_contacto, sitio_web, activo
      INTO v_nom, v_tipo, v_tel, v_correo, v_web, v_activo
      FROM tab_transportadoras
     WHERE id_transportador = p_id_transportador AND is_deleted = FALSE;

    -- Validacion: Transportadora no encontrada
    IF NOT FOUND THEN RETURN FALSE; END IF;

    IF p_nom_transportador IS NOT NULL AND TRIM(p_nom_transportador) <> '' THEN v_nom := p_nom_transportador; END IF;
    IF p_tipo_transporte   IS NOT NULL AND TRIM(p_tipo_transporte)   <> '' THEN v_tipo := p_tipo_transporte; END IF;
    IF p_tel_contacto      IS NOT NULL THEN v_tel := p_tel_contacto; END IF;
    IF p_correo_contacto   IS NOT NULL THEN 
       -- Validacion: Correo inválido
       IF p_correo_contacto NOT LIKE '%@%' THEN RETURN FALSE; END IF;
       v_correo := p_correo_contacto; 
    END IF;
    IF p_sitio_web         IS NOT NULL THEN v_web := p_sitio_web; END IF;
    IF p_activo            IS NOT NULL THEN v_activo := p_activo; END IF;

    UPDATE tab_transportadoras
       SET nom_transportador = v_nom,
           tipo_transporte   = v_tipo,
           tel_contacto      = v_tel,
           correo_contacto   = v_correo,
           sitio_web         = v_web,
           activo            = v_activo,
           updated_by        = current_user,
           updated_at        = CURRENT_TIMESTAMP
     WHERE id_transportador  = p_id_transportador;

    RETURN TRUE;
END;
$$ LANGUAGE plpgsql;
