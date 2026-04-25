<?php
/**
 * Script temporal para ejecutar fun_softdel.sql en la base de datos.
 * Ejecutar una sola vez, luego eliminar.
 */
require_once __DIR__ . '/../src/functions/database.php';

try {
    $db = Database::getInstance();
    $conn = $db->connection;
    
    $sqlFile = __DIR__ . '/funciones_db/fun_softdel.sql';
    if (!file_exists($sqlFile)) {
        die("ERROR: No se encontró el archivo: $sqlFile\n");
    }
    
    $sql = file_get_contents($sqlFile);
    $conn->exec($sql);
    
    echo "✅ Todas las funciones de soft delete fueron creadas/actualizadas exitosamente.\n";
    echo "Puedes eliminar este archivo (run_softdel.php) ahora.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
