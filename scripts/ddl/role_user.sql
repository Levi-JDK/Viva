-- Crear un rol de usuario con capacidad de inicio de sesión
CREATE ROLE artesanias_user WITH
    LOGIN
    PASSWORD 'secure_password_2025'
    VALID UNTIL '2026-08-24 06:27:00-05'
    NOSUPERUSER
    NOCREATEDB
    NOCREATEROLE
    NOREPLICATION;
-- Otorgar permisos de conexión a la base de datos (reemplaza 'artesanias_db' con el nombre de tu base de datos)
GRANT CONNECT ON DATABASE db_viva TO artesanias_user;
GRANT USAGE ON SCHEMA public TO artesanias_user;
GRANT SELECT, INSERT, UPDATE ON TABLE tab_users, tab_clientes, tab_productores, tab_producto_productor TO artesanias_user;
