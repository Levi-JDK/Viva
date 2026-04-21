<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class MailService
{
    private static ?MailService $instance = null;
    private PHPMailer $mailer;
    private string $fromEmail;
    private string $fromName;
    private string $lastError = '';

    private function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->mailer->CharSet = 'UTF-8';
        
        // Cargamos remitente desde .env con fail-fast
        $this->fromEmail = self::getRequiredEnv('MAIL_FROM_ADDRESS');
        $this->fromName = self::getRequiredEnv('MAIL_FROM_NAME');
        
        $this->configureTransport();
    }

    private static function getRequiredEnv(string $key): string
    {
        if (!isset($_ENV[$key]) || trim((string) $_ENV[$key]) === '') {
            throw new \RuntimeException($key . ' debe estar configurado en .env');
        }

        return trim((string) $_ENV[$key]);
    }

    public static function getInstance(): MailService
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getLastError(): string
    {
        return $this->lastError;
    }

    public function sendWelcomeEmail(string $toEmail, string $toName): bool
    {
        $safeName = htmlspecialchars($toName, ENT_QUOTES, 'UTF-8');
        $subject = 'Bienvenido(a) a VIVA';
        $html = "
            <div style=\"font-family: Arial, sans-serif; line-height:1.5; color: #333;\">
                <h2 style=\"color: #d97706;\">Hola {$safeName},</h2>
                <p>Tu cuenta en <strong>VIVA</strong> fue creada correctamente.</p>
                <p>Ya puedes iniciar sesión y explorar nuestro catálogo de artesanías indígenas.</p>
                <hr style=\"border: none; border-top: 1px solid #eee; margin: 20px 0;\">
                <small style=\"color: #888;\">Este correo fue generado automáticamente.</small>
            </div>
        ";
        
        if (!$this->sendEmail($toEmail, $toName, $subject, $html)) {
            $this->lastError = $this->mailer->ErrorInfo;
            return false;
        }
        return true;
    }

    public function sendPasswordRecoveryEmail(string $toEmail, string $toName, string $token): bool
    {
        $safeName = htmlspecialchars($toName, ENT_QUOTES, 'UTF-8');
        $safeToken = htmlspecialchars($token, ENT_QUOTES, 'UTF-8');
        $minutes = (int) self::getRequiredEnv('RESET_TOKEN_EXP_MINUTES');
        $subject = 'Recuperación de contraseña - VIVA';
        $html = "
            <div style=\"font-family: Arial, sans-serif; line-height:1.5; color: #333;\">
                <h2 style=\"color: #d97706;\">Hola {$safeName},</h2>
                <p>Recibimos una solicitud para recuperar tu contraseña.</p>
                <p>Tu código de verificación es:</p>
                <p style=\"font-size: 28px; font-weight: bold; letter-spacing: 4px; color: #d97706;\">{$safeToken}</p>
                <p>Este código expira en {$minutes} minutos.</p>
                <p>Si no solicitaste este cambio, ignora este correo de forma segura.</p>
                <hr style=\"border: none; border-top: 1px solid #eee; margin: 20px 0;\">
                <small style=\"color: #888;\">Este correo fue generado automáticamente.</small>
            </div>
        ";
        return $this->sendEmail($toEmail, $toName, $subject, $html);
    }

    private function sendEmail(string $toEmail, string $toName, string $subject, string $html): bool
    {
        try {
            $this->mailer->clearAllRecipients();
            $this->mailer->clearAttachments();
            
            $this->mailer->setFrom($this->fromEmail, $this->fromName);
            $this->mailer->addAddress($toEmail, $toName);
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $html;
            
            $this->mailer->send();
            $this->lastError = '';
            return true;
        } catch (Exception $e) {
            $this->lastError = $this->mailer->ErrorInfo;
            throw $e;
        }
    }

    private function configureTransport(): void
    {
        // FAIL-FAST: Credenciales SMTP son obligatorias — sin fallbacks inseguros
        $smtpUsername = self::getRequiredEnv('SMTP_USERNAME');
        $smtpPassword = self::getRequiredEnv('SMTP_PASSWORD');

        $this->mailer->isSMTP();
        $this->mailer->Host = self::getRequiredEnv('SMTP_HOST');
        $this->mailer->SMTPAuth = true;
        
        $this->mailer->Username = $smtpUsername;
        $this->mailer->Password = $smtpPassword;
        
        $secure = strtolower(self::getRequiredEnv('SMTP_SECURE'));
        $this->mailer->SMTPSecure = match($secure) {
            'tls' => PHPMailer::ENCRYPTION_STARTTLS,
            'ssl' => PHPMailer::ENCRYPTION_SMTPS,
            default => throw new \RuntimeException('SMTP_SECURE debe ser tls o ssl'),
        };
        $this->mailer->Port = (int) self::getRequiredEnv('SMTP_PORT');
    }
}
