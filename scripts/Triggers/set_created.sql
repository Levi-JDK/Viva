-- Función para autopoblar created_by / created_at
CREATE OR REPLACE FUNCTION set_created()
RETURNS TRIGGER AS $$
BEGIN
    -- Si viene vacío o NULL, usar el rol actual
    NEW.created_by := COALESCE(NULLIF(NEW.created_by, ''), current_user);
    -- Si no viene fecha, poner timestamp actual
    NEW.created_at := COALESCE(NEW.created_at, CURRENT_TIMESTAMP);
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DO $$
DECLARE
    r record;
    trig_name text;
BEGIN
    -- Recorrer todas las tablas que empiecen por 'tab_' en el esquema public
    FOR r IN
        SELECT table_name
        FROM information_schema.tables
        WHERE table_schema = 'public'
          AND table_name LIKE 'tab_%'
          AND table_type = 'BASE TABLE'
    LOOP
        trig_name := 'trigger_set_created_' || r.table_name;

        -- Borrar trigger anterior si existe
        EXECUTE format('DROP TRIGGER IF EXISTS %I ON %I;', trig_name, r.table_name);

        -- Crear el trigger
        EXECUTE format($fmt$
            CREATE TRIGGER %I
            BEFORE INSERT ON %I
            FOR EACH ROW
            EXECUTE FUNCTION set_created();
        $fmt$, trig_name, r.table_name);
        
        RAISE NOTICE 'Trigger created for table: %', r.table_name;
    END LOOP;
END $$;
