<?php
require_once __DIR__ . '/src/functions/database.php';

try {
    $db = Database::getInstance();
    
    // Leer el contenido del SQL
    $sql_c = file_get_contents(__DIR__ . '/scripts/funciones_db/fun_c_reset_token.sql');
    $sql_v = file_get_contents(__DIR__ . '/scripts/funciones_db/fun_v_reset_token.sql');
    $sql_u = file_get_contents(__DIR__ . '/scripts/funciones_db/fun_u_password.sql');
    // Actualizar Base de datos
    $db->connection->exec($sql_c);
    $db->connection->exec($sql_v);
    $db->connection->exec($sql_u);

    echo "Funciones de base de datos parcheadas correctamente.\n";
    
} catch (PDOException $e) {
    echo "PDO Error: " . $e->getMessage();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
