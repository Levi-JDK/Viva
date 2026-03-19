<?php
// Requerir el autoload de Composer para cargar la librería Predis
require __DIR__ . '/vendor/autoload.php';
$client = new Predis\Client([
    'scheme'   => 'tcp',
    'host'     => '52.165.89.247', // <-- REEMPLAZAR con la IP de tu VM
    'port'     => 6379,
    'password' => 'LybayAckerman10##' // <-- REEMPLAZAR con tu contraseña
]);
// Prueba de fuego
try {
    $client->connect();
    echo "¡Conectado a Redis en Azure! 🚀\n";
    
    // Opcional: Probar escribir y leer algo
    $client->set('prueba_conexion', 'Todo funciona correctamente');
    echo "Valor guardado: " . $client->get('prueba_conexion') . "\n";
    
} catch (Exception $e) {
    echo "Error de conexión: " . $e->getMessage() . "\n";
}