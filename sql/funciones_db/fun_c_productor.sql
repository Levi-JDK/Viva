-- SELECT * FROM tab_productores
-- SELECT fun_c_productor(1,'1004912749',1,'jjhj',1,1,1,1,1,'300546544',1);
CREATE OR REPLACE FUNCTION fun_c_productor(
    p_id_tipo_doc		  tab_productores.id_tipo_doc%TYPE,
    p_id_productor        VARCHAR,
    p_id_user             tab_users.id_user%TYPE,
    p_dir_prod            tab_productores.dir_prod%TYPE,
    p_id_pais             tab_productores.id_pais%TYPE,
    p_id_departamento     tab_productores.id_departamento%TYPE,
    p_id_ciudad           tab_productores.id_ciudad%TYPE,
    p_id_grupo            tab_productores.id_grupo%TYPE,
    p_id_banco            tab_productores.id_banco%TYPE,
    p_id_cuenta_prod      VARCHAR,
    p_tipo_cuenta         tab_productores.tipo_cuenta%TYPE
) RETURNS BOOLEAN AS $$
DECLARE
    w_id_user       tab_users.id_user%TYPE;
    w_id_productor  tab_productores.id_productor%TYPE;
BEGIN
    -- 1. Validar Usuario activo
    SELECT u.id_user INTO w_id_user
      FROM tab_users u
     WHERE u.id_user = p_id_user
       AND u.is_deleted = FALSE;
    IF NOT FOUND THEN
        RETURN FALSE;
    END IF;

    -- 2. Validar que el ID Productor no exista ya
    PERFORM 1 
      FROM tab_productores pr
     WHERE pr.id_productor = p_id_productor::NUMERIC;
    IF FOUND THEN
        RETURN FALSE;
    END IF;

    -- 3. Validar que el usuario no tenga ya un productor activo (1 a 1)
    PERFORM 1
      FROM tab_productores pr
     WHERE pr.id_user = p_id_user
       AND pr.is_deleted = FALSE;
    IF FOUND THEN
        RETURN FALSE;
    END IF;

    -- 4. Validar FKs (Banco, Grupo, Departamento, Ciudad, Pais, TipoDoc)
    
    -- Banco
    PERFORM 1 FROM tab_bancos b WHERE b.id_banco = p_id_banco AND b.is_deleted = FALSE;
    IF NOT FOUND THEN RETURN FALSE; END IF;

    -- Grupo
    PERFORM 1 FROM tab_grupos g WHERE g.id_grupo = p_id_grupo AND g.is_deleted = FALSE;
    IF NOT FOUND THEN RETURN FALSE; END IF;

    -- Departamento y Pais
    PERFORM 1 FROM tab_departamentos d 
     WHERE d.id_departamento = p_id_departamento 
       AND d.id_pais = p_id_pais 
       AND d.is_deleted = FALSE;
    IF NOT FOUND THEN RETURN FALSE; END IF;

    -- Ciudad y Pais
    PERFORM 1 FROM tab_ciudades c 
     WHERE c.id_ciudad = p_id_ciudad 
       AND c.id_pais = p_id_pais 
       AND c.is_deleted = FALSE;
    IF NOT FOUND THEN RETURN FALSE; END IF;

    -- Tipo Documento
    PERFORM 1 FROM tab_tipos_doc t WHERE t.id_tipo_doc = p_id_tipo_doc AND t.is_deleted = FALSE;
    IF NOT FOUND THEN RETURN FALSE; END IF;

    -- 5. Insertar con CAST explícito
    INSERT INTO tab_productores(
        id_tipo_doc, id_productor, id_user,
        dir_prod, id_pais, id_ciudad, id_departamento, id_grupo,
        id_banco, id_cuenta_prod, tipo_cuenta
    ) VALUES (
        p_id_tipo_doc, p_id_productor::NUMERIC, p_id_user,
        p_dir_prod, p_id_pais, p_id_ciudad, p_id_departamento, p_id_grupo,
        p_id_banco, p_id_cuenta_prod::NUMERIC, p_tipo_cuenta
    );

    RETURN TRUE;
END;
$$ LANGUAGE plpgsql;