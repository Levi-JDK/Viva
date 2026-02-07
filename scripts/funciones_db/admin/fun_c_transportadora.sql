CREATE OR REPLACE FUNCTION fun_c_transportadora(
    p_id_transportador  tab_transportadoras.id_transportador%TYPE,
    p_nom_transportador tab_transportadoras.nom_transportador%TYPE,
    p_tipo_transporte   tab_transportadoras.tipo_transporte%TYPE,
    p_tel_contacto      tab_transportadoras.tel_contacto%TYPE,
    p_correo_contacto   tab_transportadoras.correo_contacto%TYPE,
    p_sitio_web         tab_transportadoras.sitio_web%TYPE,
    p_activo            tab_transportadoras.activo%TYPE
) RETURNS BOOLEAN AS $$
BEGIN
    -- Validacion: ID transportador requerido
    IF p_id_transportador IS NULL OR TRIM(p_id_transportador) = '' THEN RETURN FALSE; END IF;
    -- Validacion: Nombre transportador requerido
    IF p_nom_transportador IS NULL OR TRIM(p_nom_transportador) = '' THEN RETURN FALSE; END IF;
    -- Validacion: Tipo transporte requerido
    IF p_tipo_transporte IS NULL OR TRIM(p_tipo_transporte) = '' THEN RETURN FALSE; END IF;
    
    PERFORM 1 FROM tab_transportadoras WHERE id_transportador = p_id_transportador;
    -- Validacion: Transportadora ya existe
    IF FOUND THEN RETURN FALSE; END IF;

    -- Validacion basica correo
    IF p_correo_contacto IS NOT NULL AND p_correo_contacto NOT LIKE '%@%' THEN
        -- Validacion: Correo inválido
        RETURN FALSE;
    END IF;

    INSERT INTO tab_transportadoras (
        id_transportador, nom_transportador, tipo_transporte, tel_contacto, correo_contacto, sitio_web, activo
    ) VALUES (
        p_id_transportador, p_nom_transportador, p_tipo_transporte, COALESCE(p_tel_contacto,''), COALESCE(p_correo_contacto,''), COALESCE(p_sitio_web,''), COALESCE(p_activo, TRUE)
    );

    RETURN TRUE;
END;
$$ LANGUAGE plpgsql;
