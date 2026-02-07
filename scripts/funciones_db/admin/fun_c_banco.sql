CREATE OR REPLACE FUNCTION fun_c_banco(
    p_id_banco  tab_bancos.id_banco%TYPE,
    p_nom_banco tab_bancos.nom_banco%TYPE,
    p_dir_banco tab_bancos.dir_banco%TYPE
) RETURNS BOOLEAN AS $$
BEGIN
    -- Validaciones
    IF p_id_banco IS NULL OR TRIM(p_id_banco) = '' THEN
        -- Validacion: El ID del banco es obligatorio.
        RETURN FALSE;
    END IF;

    IF p_nom_banco IS NULL OR TRIM(p_nom_banco) = '' THEN
        -- Validacion: El nombre del banco es obligatorio.
        RETURN FALSE;
    END IF;

    IF p_dir_banco IS NULL OR TRIM(p_dir_banco) = '' THEN
        -- Validacion: La dirección del banco es obligatoria.
        RETURN FALSE;
    END IF;

    -- Verificar existencia
    PERFORM 1 FROM tab_bancos WHERE id_banco = p_id_banco;
    IF FOUND THEN
        -- Validacion: El banco ya existe.
        RETURN FALSE;
    END IF;

    INSERT INTO tab_bancos (id_banco, nom_banco, dir_banco)
    VALUES (p_id_banco, p_nom_banco, p_dir_banco);

    RETURN TRUE;
END;
$$ LANGUAGE plpgsql;
