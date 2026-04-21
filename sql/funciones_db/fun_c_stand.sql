CREATE OR REPLACE FUNCTION fun_c_stand(
    p_id_productor      tab_stand.id_productor%TYPE,
    p_nom_stand         tab_stand.nom_stand%TYPE,
    p_slogan_stand      tab_stand.slogan_stand%TYPE,
    p_descripcion_stand tab_stand.descripcion_stand%TYPE,
    p_img_stand         tab_stand.img_stand%TYPE,
    p_portada_stand     tab_stand.portada_stand%TYPE
) RETURNS BOOLEAN AS $$
DECLARE
    v_stand_id tab_stand.id_stand%TYPE;
BEGIN
    -- 1. Validar campos obligatorios
    IF p_id_productor IS NULL OR p_nom_stand IS NULL OR 
       p_slogan_stand IS NULL OR p_descripcion_stand IS NULL OR 
       p_img_stand IS NULL OR p_portada_stand IS NULL THEN
        RAISE NOTICE 'Todos los campos son obligatorios';
        RETURN FALSE;
    END IF;

    -- 2. Validar FK Productor
    IF NOT EXISTS (SELECT 1 FROM tab_productores WHERE id_productor = p_id_productor AND is_deleted = FALSE) THEN
        RAISE NOTICE 'El productor % no existe o está inactivo', p_id_productor;
        RETURN FALSE;
    END IF;
    
    -- 3. Generar ID Stand (consecutivo a nivel global)
    SELECT COALESCE(MAX(id_stand), 0) + 1 INTO v_stand_id 
    FROM tab_stand;

    -- 4. Insertar
    INSERT INTO tab_stand (
        id_productor, 
        id_stand, 
        nom_stand, 
        slogan_stand, 
        descripcion_stand, 
        img_stand, 
        portada_stand
    ) VALUES (
        p_id_productor,
        v_stand_id,
        p_nom_stand,
        p_slogan_stand,
        p_descripcion_stand,
        p_img_stand,
        p_portada_stand
    );

    -- 5. Asignar el menú Mi Stand (ID 11) al usuario si aún no lo tiene
    INSERT INTO tab_menu_user (id_user, id_menu)
    SELECT id_user, 11
    FROM tab_productores
    WHERE id_productor = p_id_productor
    ON CONFLICT DO NOTHING;

    RETURN TRUE;

EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error en fun_c_stand: %', SQLERRM;
        RETURN FALSE;
END;
$$ LANGUAGE plpgsql;