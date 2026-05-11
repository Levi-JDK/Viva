-- DROP FUNCTION obtener_ciudades
CREATE OR REPLACE FUNCTION obtener_ciudades(p_id_dpto tab_departamentos.id_departamento%TYPE)
RETURNS TABLE (
    id 		DECIMAL(5,0),
    nombre 	VARCHAR
) 
AS $$
BEGIN
    RETURN QUERY
    SELECT id_ciudad as id, nom_ciudad as nombre 
	FROM tab_ciudades 
	WHERE id_departamento = p_id_dpto  AND id_pais = 1 
	ORDER BY nombre ASC;
END;
$$ LANGUAGE plpgsql;

	