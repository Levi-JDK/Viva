-- Trigger para auto-asignar menús de cliente por defecto al registrar un usuario
CREATE OR REPLACE FUNCTION fn_trg_asignar_menus_defecto()
RETURNS TRIGGER AS $$
BEGIN
    INSERT INTO tab_menu_user (id_user, id_menu)
    VALUES 
        (NEW.id_user, 1),  -- Inicio
        (NEW.id_user, 2),  -- Categorías
        (NEW.id_user, 3),  -- Catálogo
        (NEW.id_user, 4),  -- Mi Perfil
        (NEW.id_user, 5),  -- Mis Pedidos
        (NEW.id_user, 6),  -- Favoritos
        (NEW.id_user, 7),  -- Configuración
        (NEW.id_user, 9)   -- Vender en VIVA
    ON CONFLICT DO NOTHING;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_asignar_menus_defecto ON tab_users;
CREATE TRIGGER trg_asignar_menus_defecto
AFTER INSERT ON tab_users
FOR EACH ROW
EXECUTE FUNCTION fn_trg_asignar_menus_defecto();
