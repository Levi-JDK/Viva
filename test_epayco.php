<?php
// test_epayco.php
// Script de diagnóstico para probar la conexión con la API de ePayco en el servidor de producción.
// Sube este archivo a la raíz de tu proyecto en el servidor y ábrelo en el navegador.

header('Content-Type: text/plain; charset=utf-8');

echo "=== DIAGNÓSTICO DE CONEXIÓN A EPAYCO ===\n\n";

// 1. Información del servidor
echo "[1] Detalles del Servidor:\n";
echo "PHP Version: " . phpversion() . "\n";
echo "allow_url_fopen: " . (ini_get('allow_url_fopen') ? 'Habilitado (OK)' : 'Deshabilitado (PROBLEMA!)') . "\n";
echo "OpenSSL Extension: " . (extension_loaded('openssl') ? 'Cargada (OK)' : 'NO Cargada (PROBLEMA!)') . "\n\n";

// 2. Prueba de conexión con cURL (Alternativa a file_get_contents)
echo "[2] Prueba de conexión usando cURL:\n";
if (function_exists('curl_init')) {
    $ref_payco = 'c2e2f69f8892d19488dc7e07'; // Referencia de prueba
    $url = 'https://secure.epayco.co/validation/v1/reference/' . $ref_payco;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Ignorar verificación SSL para evitar problemas de certificados desactualizados
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Tiempo de espera de 10 segundos
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    echo "URL consultada: " . $url . "\n";
    echo "Código HTTP de respuesta: " . $http_code . "\n";
    
    if ($response === false) {
        echo "Error de cURL: " . $curl_error . "\n";
    } else {
        echo "Respuesta de ePayco (Primeros 200 caracteres):\n";
        echo substr($response, 0, 200) . "...\n";
    }
} else {
    echo "ADVERTENCIA: La extensión cURL no está instalada o habilitada en este servidor.\n";
}

echo "\n------------------------------------------------------\n";

// 3. Prueba de conexión con file_get_contents (Lo que usa actualmente el checkout)
echo "[3] Prueba de conexión usando file_get_contents:\n";
if (ini_get('allow_url_fopen')) {
    $options = [
        "http" => [
            "method" => "GET",
            "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)\r\n",
            "ignore_errors" => true // Crucial para ver la respuesta real aunque sea un error 400
        ],
        "ssl" => [
            "verify_peer"      => false,
            "verify_peer_name" => false,
        ]
    ];
    $context = stream_context_create($options);
    
    // Suprimimos los warnings de PHP para capturarlos nosotros
    $response = @file_get_contents($url, false, $context);
    
    if ($response === false) {
        echo "file_get_contents FALLÓ. No se pudo obtener respuesta.\n";
        $error = error_get_last();
        echo "Error PHP reportado: " . print_r($error, true) . "\n";
    } else {
        echo "file_get_contents tuvo ÉXITO.\n";
        echo "Respuesta de ePayco (Primeros 200 caracteres):\n";
        echo substr($response, 0, 200) . "...\n";
    }
} else {
    echo "No se puede probar file_get_contents porque allow_url_fopen está deshabilitado en el servidor.\n";
}

echo "\n=== FIN DEL DIAGNÓSTICO ===\n";
?>
