<?php
require_once __DIR__ . '/src/functions/database.php';
try {
    $db = Database::getInstance();
    $stmt = $db->ejecutar('obtenerDepartamentos');
    $deptos = $stmt->fetchAll();
    echo "Count: " . count($deptos) . "\n";
    print_r($deptos);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
