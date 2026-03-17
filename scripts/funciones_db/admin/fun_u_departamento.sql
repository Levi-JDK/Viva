CREATE OR REPLACE FUNCTION fun_u_departamento(
    p_id_pais          tab_departamentos.id_pais%TYPE,
    p_id_departamento  tab_departamentos.id_departamento%TYPE,
    p_nom_departamento tab_departamentos.nom_departamento%TYPE
) RETURNS BOOLEAN AS $$
DECLARE
    v_nom_departamento tab_departamentos.nom_departamento%TYPE;
BEGIN
    -- Validaciones
    IF p_id_pais IS NULL THEN RETURN FALSE; END IF;
    IF p_id_departamento IS NULL THEN RETURN FALSE; END IF;

    -- Verificar que exista el departamento
    SELECT nom_departamento INTO v_nom_departamento 
      FROM tab_departamentos 
     WHERE id_pais = p_id_pais 
       AND id_departamento = p_id_departamento 
       AND is_deleted = FALSE;
    IF NOT FOUND THEN RETURN FALSE; END IF;

    -- Actualizar nombre si se proporciona
    IF p_nom_departamento IS NOT NULL AND TRIM(p_nom_departamento) <> '' THEN 
        v_nom_departamento := p_nom_departamento; 
    END IF;

    -- Actualizar
    UPDATE tab_departamentos
       SET nom_departamento = v_nom_departamento,
           updated_by = current_user,
           updated_at = CURRENT_TIMESTAMP
     WHERE id_pais = p_id_pais 
       AND id_departamento = p_id_departamento;

    RETURN TRUE;
END;
$$ LANGUAGE plpgsql;
