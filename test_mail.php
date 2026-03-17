<?php
require_once __DIR__ . '/vendor/autoload.php';

// Cargamos variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Cargamos el servicio de correos
require_once __DIR__ . '/src/functions/mail_service.php';

// Instanciamos el servicio
$mailService = MailService::getInstance();

// CONFIGURACIÓN: Cambia este correo por el tuyo para probar si llega
$toEmail = 'correo_de_prueba@example.com'; 
$toName = 'Usuario de Prueba';

echo "Enviando correo de prueba a {$toEmail}...\n";
echo "Asegúrate de haber configurado SMTP_HOST, SMTP_USERNAME y SMTP_PASSWORD en tu archivo .env\n\n";

// Intentamos enviar un correo de bienvenida de prueba
$result = $mailService->sendWelcomeEmail($toEmail, $toName);

if ($result) {
    echo "✅ ¡Correo enviado con éxito!\n";
} else {
    echo "❌ Error al enviar el correo:\n";
    echo $mailService->getLastError() . "\n";
}
