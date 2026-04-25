<?php
/**
 * Warm-up Script para Emails Registrados
 * Carga todos los emails de PostgreSQL a Redis Set usando Pipeline
 */

require_once __DIR__ . '/Config/RedisConfig.php';

try {
    // 1. Conexión a Redis
    $redis = RedisConfig::getConnection();
    $prefix = RedisConfig::getPrefix();

    // 2. Conexión a PostgreSQL (leyendo .env o usando variables directas)
    if (!isset($_ENV['DB_HOST'])) {
        $envPath = dirname(__DIR__, 2) . '/.env';
        if (file_exists($envPath)) {
            $envVars = parse_ini_file($envPath);
            foreach ($envVars as $k => $v) {
                $_ENV[$k] = $v;
            }
        }
    }

    if (empty($_ENV['DB_HOST']) || empty($_ENV['DB_NAME']) || empty($_ENV['DB_USERNAME']) || empty($_ENV['DB_PASSWORD'])) {
        throw new Exception("Error de configuración: Faltan credenciales de base de datos en el entorno (.env). Fail fast.");
    }

    $host = $_ENV['DB_HOST'];
    $dbname = trim($_ENV['DB_NAME'], "'\"");
    $user = trim($_ENV['DB_USERNAME'], "'\"");
    $pass = trim($_ENV['DB_PASSWORD'], "'\"");

    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // 3. Obtener todos los emails
    echo "[*] Obteniendo emails de la base de datos...\n";
    $stmt = $pdo->query("SELECT mail_user FROM tab_users");
    $emails = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "[*] Se encontraron " . count($emails) . " emails.\n";

    if (count($emails) > 0) {
        // 4. Usar Pipeline para insertar masivamente en Redis
        echo "[*] Cargando a Redis en batch (Pipeline)...\n";
        
        $pipeline = $redis->pipeline();
        
        foreach ($emails as $email) {
            $pipeline->sadd($prefix . 'emails:registrados', $email);
        }
        
        $pipeline->execute();
        
        echo "[✓] Warm-up completado. Emails cacheados en Redis Set: {$prefix}emails:registrados\n";
    } else {
        echo "[!] No hay emails para cargar.\n";
    }

} catch (Exception $e) {
    throw $e;
}
