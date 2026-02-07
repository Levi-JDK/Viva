-- Función para autopoblar updated_by / updated_at
CREATE OR REPLACE FUNCTION set_updated()
RETURNS TRIGGER AS $$
BEGIN
    -- Si viene vacío o NULL, usar el rol actual
    NEW.updated_by := COALESCE(NULLIF(NEW.updated_by, ''), current_user);
    -- Siempre sellamos la fecha/hora de actualización
    NEW.updated_at := CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DO $$
DECLARE
    table_name text;
    trig_name  text;
    tables text[] := ARRAY[
        'tab_pmtros',
        'tab_paises',
        'tab_ciudades',
        'tab_grupos',
        'tab_regiones',
        'tab_pais_tipos_doc',
        'tab_tipos_doc',
        'tab_bancos',
        'tab_color',
        'tab_idiomas',
        'tab_monedas',
        'tab_users',
        'tab_menu',
        'tab_menu_user',
        'tab_productores',
        'tab_clientes',
        'tab_oficios',
        'tab_materia_prima',
        'tab_categorias',
        'tab_productos',
        'tab_producto_productor',
        'tab_transportadoras',
        'tab_formas_pago',
        'tab_transito',
        'tab_enc_fact',
        'tab_det_fact',
        'tab_envios',
        'tab_kardex'
    ];
BEGIN
    FOREACH table_name IN ARRAY tables
    LOOP
        trig_name := 'trigger_set_updated_' || table_name;

        EXECUTE format('DROP TRIGGER IF EXISTS %I ON %I;', trig_name, table_name);

        EXECUTE format($fmt$
            CREATE TRIGGER %I
            BEFORE UPDATE ON %I
            FOR EACH ROW
            EXECUTE FUNCTION set_updated();
        $fmt$, trig_name, table_name);
    END LOOP;
END $$;

-- CREATE TRIGGER trigger_set_updated_<tabla>
-- BEFORE UPDATE ON <tabla>
-- FOR EACH ROW
-- EXECUTE FUNCTION set_updated();
