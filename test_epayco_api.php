<?php
$ref = 'c2e2f69f8892d19488dc7e07'; // Example reference, just to see if connection works
$url = 'https://secure.epayco.co/validation/v1/reference/' . $ref;

echo "URL: $url\n";

$options = [
    "http" => [
        "method" => "GET",
        "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)\r\n"
    ],
    "ssl" => [
        "verify_peer"      => false,
        "verify_peer_name" => false,
    ]
];

$context = stream_context_create($options);

$response = @file_get_contents($url, false, $context);

if ($response === false) {
    echo "file_get_contents failed.\n";
    $error = error_get_last();
    print_r($error);
} else {
    echo "Response received:\n";
    echo substr($response, 0, 200) . "...\n";
}
