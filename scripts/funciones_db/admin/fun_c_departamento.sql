CREATE OR REPLACE FUNCTION fun_c_departamento(
    p_id_pais        tab_departamentos.id_pais%TYPE,
    p_id_departamento tab_departamentos.id_departamento%TYPE,
    p_nom_departamento tab_departamentos.nom_departamento%TYPE
) RETURNS BOOLEAN AS $$
BEGIN
    -- Validaciones
    IF p_id_pais IS NULL THEN RETURN FALSE; END IF;
    IF p_id_departamento IS NULL THEN RETURN FALSE; END IF;
    IF p_nom_departamento IS NULL OR TRIM(p_nom_departamento) = '' THEN RETURN FALSE; END IF;

    -- Verificar que no exista
    PERFORM 1 FROM tab_departamentos 
     WHERE id_pais = p_id_pais AND id_departamento = p_id_departamento;
    IF FOUND THEN RETURN FALSE; END IF;

    -- Insertar
    INSERT INTO tab_departamentos (id_pais, id_departamento, nom_departamento)
    VALUES (p_id_pais, p_id_departamento, p_nom_departamento);

    RETURN TRUE;
END;
$$ LANGUAGE plpgsql;
