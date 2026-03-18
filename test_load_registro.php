<?php
/**
 * Script de Load Testing para la Cola de Registro (Write-Behind en Redis).
 */

require_once __DIR__ . '/vendor/autoload.php';

// === CONFIGURACIÓN DE TEST ===
$TOTAL_USERS = 1000;  // ⬅️ CAMBIA ESTO PARA PROBAR MÁS O MENOS USUARIOS

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

if (empty($_ENV['DB_HOST']) || empty($_ENV['DB_NAME']) || empty($_ENV['DB_USERNAME']) || empty($_ENV['DB_PASSWORD'])) {
    die("❌ Error de configuración: Faltan credenciales de base de datos en el entorno (.env). Fail fast.\n");
}

$dbHost = $_ENV['DB_HOST'];
$dbPort = $_ENV['DB_PORT'] ?? 5432;
$dbName = trim($_ENV['DB_NAME'], "'\"");
$dbUser = trim($_ENV['DB_USERNAME'], "'\"");
$dbPass = trim($_ENV['DB_PASSWORD'], "'\"");

// === CONFIGURACIÓN DE URL ===
$url = "http://localhost/viva/src/functions/auth_controller.php";

try {
    $pdo = new PDO("pgsql:host=$dbHost;port=$dbPort;dbname=$dbName", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (Exception $e) {
    die("❌ Error conectando a DB PostgreSQL: " . $e->getMessage() . "\n");
}

$prefix = "testload_" . time() . "_";

echo "\n";
echo "=================================================================\n";
echo "🚀 INICIANDO TEST DE CARGA EXTREMA: REGISTRO ASÍNCRONO REDIS\n";
echo "=================================================================\n";
echo "🎯 Objetivo:   $TOTAL_USERS Usuarios\n";
echo "📦 Modalidad:  Secuencial (uno por uno)\n";
echo "🌐 Destino:    $url\n";
echo "📧 Patrón:     {$prefix}X@example.com\n";
echo "=================================================================\n\n";

// ==========================================
// FASE 1: API & REDIS (ENCOLADO)
// ==========================================
echo "⏱️  [FASE 1] INYECTANDO USUARIOS VÍA API (REDIS WRITE-BEHIND)...\n";
echo "-----------------------------------------------------------------\n";

$successCount = 0;
$failCount = 0;
$totalApiTime = 0; // Tiempo puro de red/API sin contar los sleeps

$globalStartTime = microtime(true);

for ($i = 0; $i < $TOTAL_USERS; $i++) {
    $reqIndex = $i;
    $email = $prefix . $reqIndex . "@example.com";
    
    $postData = [
        'accion' => 'registro',
        'nombre' => 'User' . $reqIndex,
        'apellido' => 'LoadTest',
        'email' => $email,
        'contrasena' => 'TestPass123!#'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    
    $reqStartTime = microtime(true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    $reqEndTime = microtime(true);
    $reqDuration = $reqEndTime - $reqStartTime;
    $totalApiTime += $reqDuration;
    
    curl_close($ch);
    
    if ($httpCode == 200 && strpos($response, 'mensaje-exito') !== false) {
        $successCount++;
        echo "   ✅ Usuario " . str_pad($reqIndex + 1, 4, '0', STR_PAD_LEFT) . " subido a Redis en " . round($reqDuration, 4) . "s\n";
    } else {
        $failCount++;
        echo "   ❌ Fallo Usuario " . str_pad($reqIndex + 1, 4, '0', STR_PAD_LEFT) . " (HTTP $httpCode)\n";
        if ($failCount <= 3) { // Mostrar solo primeros errores para no ensuciar
            echo "      ⚠️ Detalle: " . trim(strip_tags(substr($response, 0, 100))) . "\n";
        }
    }
}

$globalEndTime = microtime(true);
$globalDuration = $globalEndTime - $globalStartTime;

echo "-----------------------------------------------------------------\n";
echo "🏁 [FASE 1] RESUMEN DE ENCOLADO\n";
echo "   ✅ Exitosos:  $successCount / $TOTAL_USERS\n";
echo "   ❌ Fallidos:  $failCount\n";
echo "   ⏱️  Tiempo total sec.: " . round($globalDuration, 3) . " segundos\n";
echo "   ⚡ Promedio API:      " . round($totalApiTime / ($successCount ?: 1), 4) . " s/req\n";
echo "=================================================================\n\n";

if ($successCount === 0) {
    die("❌ ERROR FATAL: Ninguna petición fue exitosa. Abortando Fase 2.\n");
}

// ==========================================
// FASE 2: WORKERS & POSTGRESQL (DESPACHO)
// ==========================================
echo "⏱️  [FASE 2] POLLING: ESPERANDO DESPACHO DEL WORKER A DB...\n";
echo "-----------------------------------------------------------------\n";
echo "   Asegurate de tener el worker corriendo:\n";
echo "   > php src/workers/Runner.php viva:cola:registros\n";
echo "-----------------------------------------------------------------\n";

// Conteo inicial antes de medir para saber cuántos entraron antes de empezar el polling formal
// (En teoría 0, pero el worker puede estar corriendo rapidísimo)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM tab_users WHERE mail_user LIKE :pattern");

$startTimeDb = microtime(true); 
$timeOut = 120; // 120 segundos max
$insertados = 0;
$lastCount = -1;

echo "   ⏳ Progreso DB: ";

while (true) {
    $stmt->execute(['pattern' => $prefix . '%']);
    $insertados = $stmt->fetchColumn();
    
    if ($insertados !== $lastCount) {
        echo "[$insertados]...";
        $lastCount = $insertados;
    }
    
    if ($insertados >= $successCount) {
        echo " ¡LISTO!\n";
        break; // Todos los enviados exitosamente ya están en BD
    }
    
    $elapsed = microtime(true) - $startTimeDb;
    if ($elapsed > $timeOut) {
        echo "\n   ⚠️ TIMEOUT: Pasaron $timeOut segundos y se quedaron $insertados/$successCount.\n";
        break;
    }
    
    usleep(250000); // Polling cada 250ms
}

$endTimeDb = microtime(true);
$timeDbTotal = $endTimeDb - $startTimeDb;

echo "\n-----------------------------------------------------------------\n";
echo "🏁 [FASE 2] RESUMEN DE DESPACHO (WORKER)\n";
echo "   ✅ Insertados DB: $insertados / $successCount\n";
echo "   ⏱️  Tiempo Worker: " . round($timeDbTotal, 3) . " segundos\n";
echo "   ⚡ Rendimiento:   " . round($insertados / ($timeDbTotal ?: 1), 2) . " inserts/seg\n";
echo "=================================================================\n";
echo "🎉 TEST COMPLETADO CON ÉXITO\n";
echo "=================================================================\n\n";

?>