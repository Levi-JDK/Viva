<?php
require_once __DIR__ . '/src/functions/database.php';

try {
    $db = Database::getInstance();
    $sql = file_get_contents(__DIR__ . '/scripts/funciones_db/fun_u_cliente_epayco.sql');
    $db->connection->exec($sql);
    echo "Function updated successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
