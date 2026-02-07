CREATE OR REPLACE FUNCTION fun_c_productor(
    p_tipo_doc_productor  tab_productores.tipo_doc_productor%TYPE,
    p_id_productor        tab_productores.id_productor%TYPE,
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
    w_id_user       tab_users.id_user%TYPE;
    w_id_productor  tab_productores.id_productor%TYPE;
    w_id_banco      tab_bancos.id_banco%TYPE;
    w_id_grupo      tab_grupos.id_grupo%TYPE;
    w_id_region     tab_regiones.id_region%TYPE;
    w_id_ciudad     tab_ciudades.id_ciudad%TYPE;
    w_id_pais       tab_ciudades.id_pais%TYPE;
BEGIN
    -- Usuario activo
    SELECT u.id_user INTO w_id_user
      FROM tab_users u
     WHERE u.id_user = p_id_user
       AND u.is_deleted = FALSE;
    IF NOT FOUND THEN
        -- Validacion: Usuario no existe o está eliminado
        RETURN FALSE;
    END IF;

    -- id_productor no debe existir
    SELECT pr.id_productor INTO w_id_productor
      FROM tab_productores pr
     WHERE pr.id_productor = p_id_productor;
    IF FOUND THEN
        -- Validacion: Ya existe id_productor
        RETURN FALSE;
    END IF;

    -- El user no debe tener otro productor activo (reuso w_id_productor)
    SELECT pr.id_productor INTO w_id_productor
      FROM tab_productores pr
     WHERE pr.id_user = p_id_user
       AND pr.is_deleted = FALSE
     LIMIT 1;
    IF FOUND THEN
        -- Validacion: El usuario ya tiene productor activo
        RETURN FALSE;
    END IF;

    -- FKs: banco, grupo, región
    SELECT b.id_banco INTO w_id_banco
      FROM tab_bancos b
     WHERE b.id_banco = p_id_banco
       AND b.is_deleted = FALSE;
    IF NOT FOUND THEN
        -- Validacion: Banco no válido
        RETURN FALSE;
    END IF;

    SELECT g.id_grupo INTO w_id_grupo
      FROM tab_grupos g
     WHERE g.id_grupo = p_id_grupo
       AND g.is_deleted = FALSE;
    IF NOT FOUND THEN
        -- Validacion: Grupo no válido
        RETURN FALSE;
    END IF;

    SELECT r.id_region INTO w_id_region
      FROM tab_regiones r
     WHERE r.id_region = p_id_region
       AND r.is_deleted = FALSE;
    IF NOT FOUND THEN
        -- Validacion: Región no válida
        RETURN FALSE;
    END IF;

    -- FK compuesta ciudad+país
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

    -- Insert
    INSERT INTO tab_productores(
        tipo_doc_productor, id_productor, nom_prod, ape_prod, id_user,
        dir_prod, nom_emprend, rut_prod, cam_prod, img_prod,
        id_pais, id_ciudad, id_grupo, id_region, id_banco, id_cuenta_prod
    ) VALUES (
        p_tipo_doc_productor, p_id_productor, p_nom_prod, p_ape_prod, p_id_user,
        p_dir_prod, p_nom_emprend, p_rut_prod, p_cam_prod, p_img_prod,
        p_id_pais, p_id_ciudad, p_id_grupo, p_id_region, p_id_banco, p_id_cuenta_prod
    );

    RETURN TRUE;
END;
$$ LANGUAGE plpgsql;
--SELECT * FROM tab_productores;

-- SELECT fun_ins_productor(
--   1,              -- p_tipo_doc_productor (CC)
--   1005359658,      -- p_id_productor (nuevo)
--   'Camilo',       -- p_nom_prod
--   'Ruiz',         -- p_ape_prod
--   11,             -- p_id_user (sin productor)
--   'Calle 12 #34', -- p_dir_prod
--   'Tejidos CR',   -- p_nom_emprend
--   'rut_cr.pdf',   -- p_rut_prod
--   'cam_cr.pdf',   -- p_cam_prod
--   NULL,           -- p_img_prod (opcional)
--   1,              -- p_id_pais (Colombia)
--   2,              -- p_id_ciudad (Bogotá)
--   1,              -- p_id_grupo
--   2,              -- p_id_region (Andina)
--   'BANC01',       -- p_id_banco
--   1122334455      -- p_id_cuenta_prod
-- )