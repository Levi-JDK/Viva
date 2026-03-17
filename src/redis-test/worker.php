<?php
require 'vendor/autoload.php';

use Predis\Client as PredisClient;

// 1. Configuración de Redis
$r = new PredisClient([
    'scheme' => 'tcp',
    'host'   => '127.0.0.1',
    'port'   => 6379,
    'read_write_timeout' => -1,
]);

// 2. Configuración de PostgreSQL (Ajusta con tus credenciales)
try {
    $host = '10.5.213.111';
    $db   = 'db_levi';
    $user = 'levi';
    $pass = 'Gerson03#';
    
    $pdo = new PDO("pgsql:host=$host;dbname=$db", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "[*] Conectado a Postgres y esperando datos...\n";
} catch (PDOException $e) {
    die("Error conectando a Postgres: " . $e->getMessage());
}
$procesados = 0;
$inicio_total = 0;

while(true) {
    $tarea = $r->brpop('cola:registros', 0);
    
    // Al recibir el primer registro de la ráfaga, empezamos a contar
    if ($procesados === 0) {
        $inicio_total = microtime(true);
        echo "[!] Iniciando procesamiento masivo...\n";
    }

    $id = $tarea[1];
    $datos = $r->hgetall('user:'. $id);

    if (!empty($datos)) {
        $stmt = $pdo->prepare("SELECT fun_c_user(?, ?, ?, ?)");
        $stmt->execute([$datos['mail'], $datos['password'], $datos['nombre'], $datos['apellido']]);
        $r->del('user:' . $id);
        $procesados++;
    }

    // Al llegar a los 10,000, mostramos el resultado
    if ($procesados === 10000) {
        $fin_total = microtime(true);
        $tiempo_php_total = round($fin_total - $inicio_total, 4);
        echo "2. EVIDENCIA PHP-POSTGRES: 10,000 usuarios procesados en $tiempo_php_total segundos.\n";
        $procesados = 0; // Reiniciar para otra prueba
    }
}



