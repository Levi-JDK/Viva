<?php

/**
 * Error Handler Centralizado para APIs de VIVA
 * 
 * Maneja errores de forma segura: loguea internamente y devuelve
 * mensajes genéricos al usuario para evitar exposición de estructura de BD.
 */

class ErrorHandler
{
    private static ?SplObjectStorage $loggedExceptions = null;

    /**
     * Registra una excepción y devuelve un payload seguro.
     *
     * Usar en services/functions (capas internas) para loguear con contexto y
     * propagar la excepción hacia el controller externo. NO usar como respuesta
     * final en controllers API; para eso usar jsonResponse().
     *
     * Ejemplo de uso en capa interna:
     *
     * try {
     *     // lógica de negocio
     * } catch (Exception $e) {
     *     ErrorHandler::handle($e, 'checkout.facturarCarrito');
     *     throw $e;
     * }
     *
     * @param Exception|PDOException|Throwable $e Excepción capturada.
     * @param string $context Contexto descriptivo en formato modulo.metodo.
     * @return array{exito: false, mensaje: string} Payload seguro para API.
     */
    public static function handle($e, string $context = ''): array
    {
        // Loguear el error real una sola vez por instancia de excepción.
        if (!self::wasLogged($e)) {
            $logMessage = self::buildLogMessage($e, $context);
            error_log($logMessage);
            self::markLogged($e);
        }

        // Devolver mensaje genérico al usuario
        return [
            'success' => false,
            'message' => self::getUserMessage($e)
        ];
    }

    private static function wasLogged($e): bool
    {
        if (!is_object($e)) {
            return false;
        }

        if (self::$loggedExceptions === null) {
            self::$loggedExceptions = new SplObjectStorage();
        }

        return self::$loggedExceptions->contains($e);
    }

    private static function markLogged($e): void
    {
        if (!is_object($e)) {
            return;
        }

        if (self::$loggedExceptions === null) {
            self::$loggedExceptions = new SplObjectStorage();
        }

        self::$loggedExceptions->attach($e);
    }

    /**
     * Construye mensaje de log con detalles técnicos
     */
    private static function buildLogMessage($e, string $context): string
    {
        $timestamp = date('Y-m-d H:i:s');
        $file = $e->getFile();
        $line = $e->getLine();
        $message = $e->getMessage();
        $trace = $e->getTraceAsString();

        $log = "[{$timestamp}]";
        if ($context) {
            $log .= " [{$context}]";
        }
        $log .= " ERROR: {$message}";
        $log .= " | File: {$file}:{$line}";
        $log .= " | Trace: {$trace}";

        return $log;
    }

    /**
     * Devuelve mensaje seguro para el usuario
     * No expone detalles técnicos de la excepción
     */
    private static function getUserMessage($e): string
    {
        // Si es PDOException, es error de base de datos
        if ($e instanceof PDOException) {
            return 'Error en el servidor. Por favor, intente más tarde.';
        }

        // Para otras excepciones, devolver mensaje genérico
        // a menos que sea un mensaje de validación de negocio
        $message = $e->getMessage();

        // Mensajes de validación de negocio seguros (no exponen estructura)
        $businessMessages = [
            'Todos los campos marcados con * son obligatorios',
            'El documento ya está registrado',
            'El correo electrónico ya está en uso',
            'Usuario o contraseña incorrectos',
            'Token inválido o expirado',
            'Sesión expirada',
            'No tiene permisos para esta acción'
        ];

        foreach ($businessMessages as $safeMessage) {
            if (strpos($message, $safeMessage) !== false) {
                return $message;
            }
        }

        // Cualquier otro mensaje genérico
        return 'Error en el servidor. Por favor, intente más tarde.';
    }

    /**
     * Genera respuesta JSON segura para controllers API (capa externa).
     *
     * Usar SOLO en controllers/endpoints API que van a imprimir JSON y cortar
     * ejecución. No usar en services/functions porque esas capas deben loguear
     * con handle() y relanzar.
     *
     * Ejemplo de uso en controller API:
     *
     * } catch (Exception $e) {
     *     $resp = ErrorHandler::jsonResponse($e, 'carrito.gestionarItems');
     *     echo json_encode($resp);
     *     exit;
     * }
     *
     * @param Exception|PDOException|Throwable $e Excepción capturada.
     * @param string $context Contexto descriptivo en formato modulo.metodo.
     * @return array{exito: false, mensaje: string} Respuesta JSON segura.
     */
    public static function jsonResponse($e, string $context = ''): array
    {
        return self::handle($e, $context);
    }
}
