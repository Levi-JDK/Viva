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
    r record;
    trig_name  text;
BEGIN
    -- Recorrer todas las tablas que empiecen por 'tab_' en el esquema public
    FOR r IN
        SELECT table_name
        FROM information_schema.tables
        WHERE table_schema = 'public'
          AND table_name LIKE 'tab_%'
          AND table_type = 'BASE TABLE'
    LOOP
        trig_name := 'trigger_set_updated_' || r.table_name;

        -- Borrar trigger anterior si existe
        EXECUTE format('DROP TRIGGER IF EXISTS %I ON %I;', trig_name, r.table_name);

        -- Crear el trigger
        EXECUTE format($fmt$
            CREATE TRIGGER %I
            BEFORE UPDATE ON %I
            FOR EACH ROW
            EXECUTE FUNCTION set_updated();
        $fmt$, trig_name, r.table_name);
        
        RAISE NOTICE 'Trigger updated for table: %', r.table_name;
    END LOOP;
END $$;

-- CREATE TRIGGER trigger_set_updated_<tabla>
-- BEFORE UPDATE ON <tabla>
-- FOR EACH ROW
-- EXECUTE FUNCTION set_updated();
