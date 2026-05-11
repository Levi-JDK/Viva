CREATE OR REPLACE FUNCTION fun_u_configuracion_productor(
    p_id_productor        tab_productores.id_productor%TYPE,
    p_id_banco            tab_productores.id_banco%TYPE,
    p_id_cuenta_prod      tab_productores.id_cuenta_prod%TYPE,
    p_tipo_cuenta         tab_productores.tipo_cuenta%TYPE,
    p_dir_prod            tab_productores.dir_prod%TYPE,
    p_id_pais             tab_productores.id_pais%TYPE,
    p_id_departamento     tab_productores.id_departamento%TYPE,
    p_id_ciudad           tab_productores.id_ciudad%TYPE,
    p_id_grupo            tab_productores.id_grupo%TYPE
) RETURNS BOOLEAN AS $$
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM tab_productores
        WHERE id_productor = p_id_productor
          AND is_deleted = FALSE
    ) THEN
        RETURN FALSE;
    END IF;

    IF NOT EXISTS (
        SELECT 1
        FROM tab_bancos
        WHERE id_banco = p_id_banco
          AND is_deleted = FALSE
    ) THEN
        RETURN FALSE;
    END IF;

    IF NOT EXISTS (
        SELECT 1
        FROM tab_grupos
        WHERE id_grupo = p_id_grupo
          AND is_deleted = FALSE
    ) THEN
        RETURN FALSE;
    END IF;

    IF NOT EXISTS (
        SELECT 1
        FROM tab_departamentos
        WHERE id_pais = p_id_pais
          AND id_departamento = p_id_departamento
          AND is_deleted = FALSE
    ) THEN
        RETURN FALSE;
    END IF;

    IF NOT EXISTS (
        SELECT 1
        FROM tab_ciudades
        WHERE id_pais = p_id_pais
          AND id_departamento = p_id_departamento
          AND id_ciudad = p_id_ciudad
          AND is_deleted = FALSE
    ) THEN
        RETURN FALSE;
    END IF;

    UPDATE tab_productores
    SET
        id_banco = p_id_banco,
        id_cuenta_prod = p_id_cuenta_prod,
        tipo_cuenta = p_tipo_cuenta,
        dir_prod = p_dir_prod,
        id_pais = p_id_pais,
        id_departamento = p_id_departamento,
        id_ciudad = p_id_ciudad,
        id_grupo = p_id_grupo,
        updated_by = current_user,
        updated_at = CURRENT_TIMESTAMP
    WHERE id_productor = p_id_productor
      AND is_deleted = FALSE;

    RETURN FOUND;

EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error en fun_u_configuracion_productor: %', SQLERRM;
        RETURN FALSE;
END;
$$ LANGUAGE plpgsql;
