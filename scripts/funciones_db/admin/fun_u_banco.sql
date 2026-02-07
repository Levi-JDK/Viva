CREATE OR REPLACE FUNCTION fun_u_banco(
    p_id_banco  tab_bancos.id_banco%TYPE,
    p_nom_banco tab_bancos.nom_banco%TYPE,
    p_dir_banco tab_bancos.dir_banco%TYPE
) RETURNS BOOLEAN AS $$
DECLARE
    v_nom_banco tab_bancos.nom_banco%TYPE;
    v_dir_banco tab_bancos.dir_banco%TYPE;
BEGIN
    -- Validar ID
    IF p_id_banco IS NULL OR TRIM(p_id_banco) = '' THEN
        -- Validacion: El ID del banco es obligatorio para actualizar.
        RETURN FALSE;
    END IF;

    -- Verificar existencia y obtener valores actuales (solo activos?) - Asumimos actualizacion general
    SELECT nom_banco, dir_banco
      INTO v_nom_banco, v_dir_banco
      FROM tab_bancos
     WHERE id_banco = p_id_banco
       AND is_deleted = FALSE;
    
    IF NOT FOUND THEN
        -- Validacion: Banco no encontrado o eliminado.
        RETURN FALSE;
    END IF;

    -- Actualizar valores si se proporcionan
    IF p_nom_banco IS NOT NULL AND TRIM(p_nom_banco) <> '' THEN
        v_nom_banco := p_nom_banco;
    END IF;

    IF p_dir_banco IS NOT NULL AND TRIM(p_dir_banco) <> '' THEN
        v_dir_banco := p_dir_banco;
    END IF;

    -- Update
    UPDATE tab_bancos
       SET nom_banco  = v_nom_banco,
           dir_banco  = v_dir_banco,
           updated_by = current_user,
           updated_at = CURRENT_TIMESTAMP
     WHERE id_banco   = p_id_banco;

    RETURN TRUE;
END;
$$ LANGUAGE plpgsql;
