CREATE OR REPLACE FUNCTION fun_c_categoria(
    p_nom_categoria VARCHAR,
    p_img_cat VARCHAR DEFAULT NULL
)
RETURNS BOOLEAN
LANGUAGE plpgsql
AS $function$
    DECLARE v_next_id NUMERIC(12,0);
    BEGIN
        IF p_nom_categoria IS NULL THEN RETURN FALSE; END IF;

        SELECT COALESCE(MAX(id_categoria), 0) + 1 INTO v_next_id FROM tab_categorias;

        INSERT INTO tab_categorias (id_categoria, nom_categoria, img_cat)
        VALUES (v_next_id, p_nom_categoria, COALESCE(p_img_cat, 'images/default_category.webp'));
        RETURN TRUE;
    EXCEPTION WHEN OTHERS THEN RETURN FALSE;
    END;
$function$;
