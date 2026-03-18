<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    $pdo = new PDO('pgsql:host=localhost;dbname=db_viva', 'postgres', 'Gerson03#');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Conectado. Intentando insertar...\n";
    $stmt = $pdo->prepare('SELECT fun_c_user(?, ?, ?, ?)');
    $stmt->execute(['php_script_test@mail.com', 'hash', 'TestScript', 'PHP']);
    
    print_r($stmt->fetch());
    echo "¡Insertado exitosamente!\n";
    
} catch(Exception $e) {
    echo "EXCEPCIÓN EN BASE DE DATOS:\n";
    echo $e->getMessage() . "\n";
}
