-- Generado automáticamente aplicando Skill: create-sql-function
CREATE OR REPLACE FUNCTION fun_c_transportadora(
    p_id_transportador tab_transportadoras.id_transportador%TYPE,
    p_nom_transportador tab_transportadoras.nom_transportador%TYPE,
    p_tipo_transporte tab_transportadoras.tipo_transporte%TYPE,
    p_tel_contacto tab_transportadoras.tel_contacto%TYPE,
    p_correo_contacto tab_transportadoras.correo_contacto%TYPE,
    p_sitio_web tab_transportadoras.sitio_web%TYPE,
    p_activo tab_transportadoras.activo%TYPE
) RETURNS BOOLEAN AS $$
BEGIN

    -- Validaciones en caliente
    IF p_id_transportador IS NULL THEN
        RAISE NOTICE 'El parámetro p_id_transportador es inválido o nulo.';
        RETURN FALSE;
    END IF;

    IF p_nom_transportador IS NULL THEN
        RAISE NOTICE 'El parámetro p_nom_transportador es inválido o nulo.';
        RETURN FALSE;
    END IF;

    IF p_tipo_transporte IS NULL THEN
        RAISE NOTICE 'El parámetro p_tipo_transporte es inválido o nulo.';
        RETURN FALSE;
    END IF;

    IF p_tel_contacto IS NULL THEN
        RAISE NOTICE 'El parámetro p_tel_contacto es inválido o nulo.';
        RETURN FALSE;
    END IF;

    IF p_correo_contacto IS NULL THEN
        RAISE NOTICE 'El parámetro p_correo_contacto es inválido o nulo.';
        RETURN FALSE;
    END IF;

    IF p_sitio_web IS NULL THEN
        RAISE NOTICE 'El parámetro p_sitio_web es inválido o nulo.';
        RETURN FALSE;
    END IF;

    IF p_activo IS NULL THEN
        RAISE NOTICE 'El parámetro p_activo es inválido o nulo.';
        RETURN FALSE;
    END IF;

    -- Operación DML Pura
    PERFORM 1 FROM tab_transportadoras WHERE id_transportador = p_id_transportador;
    IF FOUND THEN 
        RAISE NOTICE 'El registro en tab_transportadoras ya existe.';
        RETURN FALSE; 
    END IF;

    INSERT INTO tab_transportadoras (id_transportador, nom_transportador, tipo_transporte, tel_contacto, correo_contacto, sitio_web, activo)
    VALUES (p_id_transportador, p_nom_transportador, p_tipo_transporte, p_tel_contacto, p_correo_contacto, p_sitio_web, p_activo);

    RETURN TRUE;
EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error transaccional en %: %', 'fun_c_transportadora', SQLERRM;
        RETURN FALSE;
END;
$$ LANGUAGE plpgsql;
