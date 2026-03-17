<?php

/**
 * Error Handler Centralizado para APIs de VIVA
 * 
 * Maneja errores de forma segura: loguea internamente y devuelve
 * mensajes genéricos al usuario para evitar exposición de estructura de BD.
 */

class ErrorHandler
{

    /**
     * Maneja una excepción y devuelve respuesta JSON segura
     * 
     * @param Exception|PDOException $e La excepción capturada
     * @param string $context Contexto opcional (nombre del archivo/API)
     * @return array Respuesta JSON segura
     */
    public static function handle($e, string $context = ''): array
    {
        // Loguear el error real internamente (para debugging del desarrollador)
        $logMessage = self::buildLogMessage($e, $context);
        error_log($logMessage);

        // Devolver mensaje genérico al usuario
        return [
            'success' => false,
            'message' => self::getUserMessage($e)
        ];
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
     * Helper estático para usar en catch blocks de API
     * 
     * Ejemplo de uso:
     * 
     * } catch (Exception $e) {
     *     echo json_encode(ErrorHandler::jsonResponse($e, 'mi_api'));
     * }
     */
    public static function jsonResponse($e, string $context = ''): array
    {
        return self::handle($e, $context);
    }
}