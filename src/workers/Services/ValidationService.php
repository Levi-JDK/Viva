<?php
/**
 * Servicio de validación y sanitización de datos
 * Previene SQL Injection, XSS y otros ataques
 */

class ValidationService {
    
    /**
     * Validar datos de registro de usuario
     */
    public static function validarRegistro(array $data): array {
        $errores = [];
        
        // Validar nombre
        if (empty($data['nombre']) || strlen($data['nombre']) < 2) {
            $errores[] = 'El nombre debe tener al menos 2 caracteres';
        }
        if (strlen($data['nombre']) > 100) {
            $errores[] = 'El nombre no puede exceder 100 caracteres';
        }
        
        // Validar apellido
        if (empty($data['apellido']) || strlen($data['apellido']) < 2) {
            $errores[] = 'El apellido debe tener al menos 2 caracteres';
        }
        if (strlen($data['apellido']) > 100) {
            $errores[] = 'El apellido no puede exceder 100 caracteres';
        }
        
        // Validar email
        if (empty($data['email'])) {
            $errores[] = 'El email es requerido';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'El formato del email es inválido';
        }
        
        // Validar contraseña
        if (empty($data['password'])) {
            $errores[] = 'La contraseña es requerida';
        } elseif (strlen($data['password']) < 8) {
            $errores[] = 'La contraseña debe tener al menos 8 caracteres';
        }
        
        // Validar fortaleza de contraseña
        if (!empty($data['password']) && !self::validarFortalezaPassword($data['password'])) {
            $errores[] = 'La contraseña debe contener al menos: 1 mayúscula, 1 número y 1 carácter especial';
        }
        
        return [
            'valido' => empty($errores),
            'errores' => $errores
        ];
    }
    
    /**
     * Validar datos del carrito
     */
    public static function validarCarrito(array $data): array {
        $errores = [];
        
        if (empty($data['items']) || !is_array($data['items'])) {
            $errores[] = 'El carrito está vacío';
            return ['valido' => false, 'errores' => $errores];
        }
        
        foreach ($data['items'] as $index => $item) {
            if (empty($item['producto_id']) || !is_numeric($item['producto_id'])) {
                $errores[] = "Item $index: ID de producto inválido";
            }
            if (empty($item['cantidad']) || !is_numeric($item['cantidad']) || $item['cantidad'] < 1) {
                $errores[] = "Item $index: Cantidad inválida";
            }
            if (isset($item['precio']) && !is_numeric($item['precio'])) {
                $errores[] = "Item $index: Precio inválido";
            }
        }
        
        if (empty($data['usuario_id']) || !is_numeric($data['usuario_id'])) {
            $errores[] = 'Usuario inválido';
        }
        
        return [
            'valido' => empty($errores),
            'errores' => $errores
        ];
    }
    
    /**
     * Sanitizar datos para guardar en Redis
     * Previene XSS y limpia caracteres unwanted
     */
    public static function sanitizar(array $data): array {
        $sanitizado = [];
        
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                // Trim espacios extra
                $value = trim($value);
                
                // Escapar caracteres HTML (previene XSS)
                $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                
                $sanitizado[$key] = $value;
            } elseif (is_numeric($value)) {
                $sanitizado[$key] = $value;
            } elseif (is_array($value)) {
                // Recursivo para arrays
                $sanitizado[$key] = self::sanitizar($value);
            } else {
                $sanitizado[$key] = $value;
            }
        }
        
        return $sanitizado;
    }
    
    /**
     * Sanitizar email específicamente
     */
    public static function sanitizarEmail(string $email): string {
        $email = trim($email);
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        return strtolower($email);
    }
    
    /**
     * Validar fortaleza de contraseña
     */
    public static function validarFortalezaPassword(string $password): bool {
        $hasMinLength = strlen($password) >= 8;
        $hasUppercase = preg_match('/[A-Z]/', $password);
        $hasLowercase = preg_match('/[a-z]/', $password);
        $hasNumber = preg_match('/[0-9]/', $password);
        $hasSpecial = preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password);
        
        return $hasMinLength && $hasUppercase && $hasLowercase && $hasNumber && $hasSpecial;
    }
    
    /**
     * Validar que un ID sea numérico y positivo
     */
    public static function validarId($id): bool {
        return is_numeric($id) && (int)$id > 0;
    }
    
    /**
     * Validar tipo de operación (para seguridad extra)
     */
    public static function validarOperacion(string $operacion): bool {
        $operacionesPermitidas = ['insert', 'update', 'delete', 'select'];
        return in_array(strtolower($operacion), $operacionesPermitidas);
    }
}
