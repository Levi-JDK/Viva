<?php
/**
 * Seed script: Crea "Sucursal Central Viva" en PuntoEnvio.
 * 
 * NOTA: Lee dir_contacto desde tab_pmtros (Viva). 
 *       Ese campo debe contener SOLO la calle, sin ciudad.
 *       Ejemplo correcto: "Calle 100 # 15-20"
 *       Ejemplo incorrecto: "Calle 100 # 15-20, Bogotá"
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo "Forbidden: CLI only\n";
    exit(1);
}

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

function required_env(string $key): string
{
    if (!isset($_ENV[$key]) || trim((string) $_ENV[$key]) === '') {
        fwrite(STDERR, "❌ Falta configurar {$key}.\n");
        exit(1);
    }
    return trim((string) $_ENV[$key]);
}

function http_json(string $method, string $path, ?array $body = null): ?array
{
    $baseUrl = rtrim(required_env('PUNTOENVIO_API_URL'), '/');
    $ch = curl_init($baseUrl . $path);

    if ($ch === false) {
        fwrite(STDERR, "❌ No se pudo inicializar cURL.\n");
        exit(1);
    }

    $headers = ['Accept: application/json'];
    $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_CONNECTTIMEOUT => 5,
    ];

    if ($body !== null) {
        $jsonBody = json_encode($body, JSON_UNESCAPED_UNICODE);
        if ($jsonBody === false) {
            fwrite(STDERR, "❌ No se pudo serializar JSON.\n");
            exit(1);
        }
        $headers[] = 'Content-Type: application/json';
        $options[CURLOPT_POSTFIELDS] = $jsonBody;
    }

    $options[CURLOPT_HTTPHEADER] = $headers;
    curl_setopt_array($ch, $options);

    $raw = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($raw === false || $status >= 400) {
        fwrite(STDERR, "❌ Error PuntoEnvio HTTP {$status}: {$error}\n");
        exit(1);
    }

    if (trim((string) $raw) === '') {
        return [];
    }

    $decoded = json_decode((string) $raw, true);
    return is_array($decoded) ? $decoded : null;
}

function extract_id(?array $response): ?string
{
    if (!$response) return null;
    foreach (['id_sucursal', 'id', 'sucursal_id'] as $key) {
        if (isset($response[$key]) && (string) $response[$key] !== '') {
            return (string) $response[$key];
        }
    }
    if (isset($response['data']) && is_array($response['data'])) {
        return extract_id($response['data']);
    }
    return null;
}

// ── Leer dirección desde tab_pmtros (Viva) ──
$dbHost = $_ENV['DB_HOST'] ?? 'localhost';
$dbPort = $_ENV['DB_PORT'] ?? '5432';
$dbName = $_ENV['DB_NAME'] ?? '';
$dbUser = $_ENV['DB_USERNAME'] ?? '';
$dbPass = $_ENV['DB_PASSWORD'] ?? '';

$dsn = "pgsql:host={$dbHost};port={$dbPort};dbname={$dbName}";
try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    fwrite(STDERR, "❌ No se pudo conectar a PostgreSQL: " . $e->getMessage() . "\n");
    exit(1);
}

$stmt = $pdo->query("SELECT dir_contacto FROM tab_pmtros WHERE id_parametro = 1 LIMIT 1");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$vivaAddress = $row['dir_contacto'] ?? '';
if ($vivaAddress === '') {
    fwrite(STDERR, "❌ No se encontró dir_contacto en tab_pmtros (id_parametro=1).\n");
    exit(1);
}

// Si la dirección incluye ", ciudad", tomar solo la calle
$parts = explode(',', $vivaAddress);
$vivaCalle = trim($parts[0]);

// IDs en PuntoEnvio: Bogotá D.C. = depto 33, ciudad 1
// NOTA: Estos IDs son de PuntoEnvio, no de Viva. No coinciden entre sistemas.
$PUNTOENVIO_BOGOTA_DEPTO = 33;
$PUNTOENVIO_BOGOTA_CIUDAD = 1;

$name = 'Sucursal Central Viva';
$existing = http_json('GET', '/sucursales');
$existingRows = isset($existing['data']) && is_array($existing['data']) ? $existing['data'] : $existing;

if (is_array($existingRows)) {
    foreach ($existingRows as $row) {
        if (is_array($row) && ($row['name'] ?? '') === $name) {
            $id = extract_id($row);
            echo "ℹ️ Sucursal ya existe con ID: {$id}\n";
            echo "Add to .env: PUNTOENVIO_SUCURSAL_ID={$id}\n";
            exit(0);
        }
    }
}

$created = http_json('POST', '/sucursales', [
    'nom_sucursal'    => $name,
    'id_departamento' => $PUNTOENVIO_BOGOTA_DEPTO,
    'id_ciudad'       => $PUNTOENVIO_BOGOTA_CIUDAD,
    'calle'           => $vivaCalle,
]);

$id = extract_id($created);
if ($id === null) {
    fwrite(STDERR, "❌ PuntoEnvio no retornó id_sucursal.\n");
    exit(1);
}

echo "✅ Sucursal Central Viva creada con ID: {$id}\n";
echo "Add to .env: PUNTOENVIO_SUCURSAL_ID={$id}\n";
