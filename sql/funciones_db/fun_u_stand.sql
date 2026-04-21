CREATE OR REPLACE FUNCTION fun_u_stand(
    p_id_productor      tab_stand.id_productor%TYPE,
    p_id_stand          tab_stand.id_stand%TYPE,
    p_nom_stand         tab_stand.nom_stand%TYPE,
    p_slogan_stand      tab_stand.slogan_stand%TYPE,
    p_descripcion_stand tab_stand.descripcion_stand%TYPE,
    p_img_stand         tab_stand.img_stand%TYPE,
    p_portada_stand     tab_stand.portada_stand%TYPE
) RETURNS BOOLEAN AS $$
BEGIN
    -- 1. Validar existencia
    IF NOT EXISTS (SELECT 1 FROM tab_stand WHERE id_productor = p_id_productor AND id_stand = p_id_stand) THEN
        RAISE NOTICE 'El stand % del productor % no existe', p_id_stand, p_id_productor;
        RETURN FALSE;
    END IF;

    -- 2. Actualizar
    UPDATE tab_stand
    SET 
        nom_stand = COALESCE(p_nom_stand, nom_stand),
        slogan_stand = COALESCE(p_slogan_stand, slogan_stand),
        descripcion_stand = COALESCE(p_descripcion_stand, descripcion_stand),
        img_stand = COALESCE(p_img_stand, img_stand),
        portada_stand = COALESCE(p_portada_stand, portada_stand),
        updated_at = CURRENT_TIMESTAMP
    WHERE id_productor = p_id_productor AND id_stand = p_id_stand;

    -- 3. Asignar el menú Mi Stand (ID 11) al usuario si aún no lo tiene 
    -- (redundancia segura por si se llenó por update)
    INSERT INTO tab_menu_user (id_user, id_menu)
    SELECT id_user, 11
    FROM tab_productores
    WHERE id_productor = p_id_productor
    ON CONFLICT DO NOTHING;

    RETURN TRUE;

EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error en fun_u_stand: %', SQLERRM;
        RETURN FALSE;
END;
$$ LANGUAGE plpgsql;
