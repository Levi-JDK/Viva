CREATE OR REPLACE FUNCTION fun_c_color(
    p_id_color  tab_color.id_color%TYPE,
    p_nom_color tab_color.nom_color%TYPE
) RETURNS BOOLEAN AS $$
BEGIN
    -- Validaciones
    IF p_id_color IS NULL OR TRIM(p_id_color) = '' THEN
        -- Validacion: El ID del color (Hex) es obligatorio.
        RETURN FALSE;
    END IF;

    IF p_nom_color IS NULL OR TRIM(p_nom_color) = '' THEN
        -- Validacion: El nombre del color es obligatorio.
        RETURN FALSE;
    END IF;

    -- Verificar existencia
    PERFORM 1 FROM tab_color WHERE id_color = p_id_color;
    IF FOUND THEN
        -- Validacion: El color ya existe.
        RETURN FALSE;
    END IF;

    INSERT INTO tab_color (id_color, nom_color)
    VALUES (p_id_color, p_nom_color);

    RETURN TRUE;
END;
$$ LANGUAGE plpgsql;
