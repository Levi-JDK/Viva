CREATE OR REPLACE FUNCTION fun_u_productor(
    p_id_productor        tab_productores.id_productor%TYPE,
    p_tipo_doc_productor  tab_productores.tipo_doc_productor%TYPE,
    p_nom_prod            tab_productores.nom_prod%TYPE,
    p_ape_prod            tab_productores.ape_prod%TYPE,
    p_id_user             tab_productores.id_user%TYPE,
    p_dir_prod            tab_productores.dir_prod%TYPE,
    p_nom_emprend         tab_productores.nom_emprend%TYPE,
    p_rut_prod            tab_productores.rut_prod%TYPE,
    p_cam_prod            tab_productores.cam_prod%TYPE,
    p_img_prod            tab_productores.img_prod%TYPE,
    p_id_pais             tab_productores.id_pais%TYPE,
    p_id_ciudad           tab_productores.id_ciudad%TYPE,
    p_id_grupo            tab_productores.id_grupo%TYPE,
    p_id_region           tab_productores.id_region%TYPE,
    p_id_banco            tab_productores.id_banco%TYPE,
    p_id_cuenta_prod      tab_productores.id_cuenta_prod%TYPE
) RETURNS BOOLEAN AS $$
DECLARE
    w_id_productor  tab_productores.id_productor%TYPE;
    w_is_deleted    tab_productores.is_deleted%TYPE;

    w_id_user       tab_users.id_user%TYPE;
    w_id_banco      tab_bancos.id_banco%TYPE;
    w_id_grupo      tab_grupos.id_grupo%TYPE;
    w_id_region     tab_regiones.id_region%TYPE;
    w_id_ciudad     tab_ciudades.id_ciudad%TYPE;
    w_id_pais       tab_ciudades.id_pais%TYPE;

    w_id_productor2 tab_productores.id_productor%TYPE;
BEGIN
    -- 1) No nulos según modelo
    -- Validacion: id_productor requerido
    IF p_id_productor IS NULL THEN RETURN FALSE; END IF;
    -- Validacion: tipo_doc_productor requerido
    IF p_tipo_doc_productor IS NULL THEN RETURN FALSE; END IF;
    -- Validacion: nom_prod requerido
    IF p_nom_prod IS NULL OR length(trim(p_nom_prod))=0 THEN RETURN FALSE; END IF;
    -- Validacion: ape_prod requerido
    IF p_ape_prod IS NULL OR length(trim(p_ape_prod))=0 THEN RETURN FALSE; END IF;
    -- Validacion: id_user requerido
    IF p_id_user IS NULL THEN RETURN FALSE; END IF;
    -- Validacion: id_pais requerido
    IF p_id_pais IS NULL THEN RETURN FALSE; END IF;
    -- Validacion: id_ciudad requerido
    IF p_id_ciudad IS NULL THEN RETURN FALSE; END IF;
    -- Validacion: id_grupo requerido
    IF p_id_grupo IS NULL THEN RETURN FALSE; END IF;
    -- Validacion: id_region requerido
    IF p_id_region IS NULL THEN RETURN FALSE; END IF;
    -- Validacion: id_banco requerido
    IF p_id_banco IS NULL THEN RETURN FALSE; END IF;
    -- Validacion: id_cuenta_prod requerido
    IF p_id_cuenta_prod IS NULL THEN RETURN FALSE; END IF;

    -- 2) Productor debe existir y estar ACTIVO (si está eliminado → FALSE)
    SELECT pr.id_productor, pr.is_deleted
      INTO w_id_productor, w_is_deleted
      FROM tab_productores pr
     WHERE pr.id_productor = p_id_productor;
    IF NOT FOUND THEN
        -- Validacion: No existe productor
        RETURN FALSE;
    END IF;
    IF w_is_deleted THEN
        -- Validacion: Productor está eliminado (inactivo)
        RETURN FALSE;
    END IF;

    -- 3) Usuario ACTIVO (una sola búsqueda)
    SELECT u.id_user INTO w_id_user
      FROM tab_users u
     WHERE u.id_user = p_id_user
       AND u.is_deleted = FALSE;
    IF NOT FOUND THEN
        -- Validacion: Usuario no existe o está eliminado
        RETURN FALSE;
    END IF;

    -- 4) Evitar otro productor activo del mismo usuario (distinto al actual)
    SELECT pr.id_productor INTO w_id_productor2
      FROM tab_productores pr
     WHERE pr.id_user = p_id_user
       AND pr.is_deleted = FALSE
       AND pr.id_productor <> p_id_productor
     LIMIT 1;
    IF FOUND THEN
        -- Validacion: El usuario ya tiene otro productor activo
        RETURN FALSE;
    END IF;

    -- 5) Validar FKs: banco, grupo, región
    SELECT b.id_banco INTO w_id_banco
      FROM tab_bancos b
     WHERE b.id_banco = p_id_banco
       AND b.is_deleted = FALSE;
    -- Validacion: Banco no válido
    IF NOT FOUND THEN RETURN FALSE; END IF;

    SELECT g.id_grupo INTO w_id_grupo
      FROM tab_grupos g
     WHERE g.id_grupo = p_id_grupo
       AND g.is_deleted = FALSE;
    -- Validacion: Grupo no válido
    IF NOT FOUND THEN RETURN FALSE; END IF;

    SELECT r.id_region INTO w_id_region
      FROM tab_regiones r
     WHERE r.id_region = p_id_region
       AND r.is_deleted = FALSE;
    -- Validacion: Región no válida
    IF NOT FOUND THEN RETURN FALSE; END IF;

    -- 6) Validar par ciudad–país (activos)
    SELECT c.id_ciudad, c.id_pais
      INTO w_id_ciudad, w_id_pais
      FROM tab_ciudades c
     WHERE c.id_ciudad = p_id_ciudad
       AND c.id_pais   = p_id_pais
       AND c.is_deleted = FALSE;
    IF NOT FOUND THEN
        -- Validacion: Ciudad no pertenece al país o está eliminada
        RETURN FALSE;
    END IF;

    -- 7) UPDATE directo 
    UPDATE tab_productores
       SET tipo_doc_productor = p_tipo_doc_productor,
           nom_prod           = p_nom_prod,
           ape_prod           = p_ape_prod,
           id_user            = p_id_user,
           dir_prod           = p_dir_prod,
           nom_emprend        = p_nom_emprend,
           rut_prod           = p_rut_prod,
           cam_prod           = p_cam_prod,
           img_prod           = p_img_prod,
           id_pais            = p_id_pais,
           id_ciudad          = p_id_ciudad,
           id_grupo           = p_id_grupo,
           id_region          = p_id_region,
           id_banco           = p_id_banco,
           id_cuenta_prod     = p_id_cuenta_prod
     WHERE id_productor = p_id_productor;

    RETURN TRUE;
END;
$$ LANGUAGE plpgsql;

--  SELECT * from tab_productores;
-- SELECT fun_u_productor(
--   p_tipo_doc_productor => 1::numeric,           
--   p_id_productor       => 1005359658,   
--   p_nom_prod           => 'Camila',             
--   p_ape_prod           => 'Ruiz',               
--   p_id_user            => 11,                   
--   p_dir_prod           => 'Calle 12 #34',       
--   p_nom_emprend        => 'Tejidos CR',         
--   p_rut_prod           => 'rut_cr.pdf',         
--   p_cam_prod           => 'cam_cr.pdf',         
--   p_img_prod           => 'img/producto.jpg',                 
--   p_id_pais            => 1,           
--   p_id_ciudad          => 2,           
--   p_id_grupo           => 1,           
--   p_id_region          => 2,           
--   p_id_banco           => 'BANC01',             
--   p_id_cuenta_prod     => 1122334455 
-- ) 

