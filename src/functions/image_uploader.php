<?php
/**
 * VIVA - Generic Image Upload Handler
 * 
 * Maneja la carga, validación y procesamiento de imágenes.
 * Flujo optimizado con conversión automática a WebP.
 */

require_once __DIR__ . '/image_processing.php';

/**
 * Procesa la subida de una imagen.
 * 
 * FLUJO OPTIMIZADO:
 * 1. Validar archivo (tipo, tamaño, formato)
 * 2. Generar nombre base único (sin extensión)
 * 3. Guardar temporalmente con extensión original
 * 4. Convertir a WebP usando GD
 * 5. Renombrar con extensión .webp
 * 6. Eliminar archivo original
 * 7. Retornar ruta relativa para BD
 *
 * @param array $file Archivo de $_FILES (ej: $_FILES['imagen_perfil'])
 * @param string $target_dir Ruta ABSOLUTA del directorio de destino
 * @param string $prefix Prefijo para el nombre del archivo (default: 'img_')
 * @param string $web_path_folder Carpeta para la ruta relativa (ej: 'images/profiles/')
 * @return array ['success' => bool, 'path' => string, 'message' => string]
 */
function handleImageUpload($file, $target_dir, $prefix = 'img_', $web_path_folder = 'images/') {
    
    // ============================================================================
    // PASO 1: VALIDACIONES INICIALES
    // ============================================================================
    
    // Verificar que se recibió un archivo
    if (!$file || !isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return ['success' => false, 'message' => 'No se seleccionó ningún archivo'];
    }

    // Verificar que es una imagen válida
    $image_info = @getimagesize($file["tmp_name"]);
    if ($image_info === false) {
        return ['success' => false, 'message' => 'El archivo no es una imagen válida'];
    }

    // Obtener extensión del archivo
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Formatos permitidos
    $allowed_formats = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($file_extension, $allowed_formats)) {
        return ['success' => false, 'message' => 'Formato no permitido. Use: JPG, PNG o WEBP'];
    }

    // Validar tamaño máximo (5MB)
    $max_size = 5 * 1024 * 1024; // 5MB en bytes
    if ($file["size"] > $max_size) {
        return ['success' => false, 'message' => 'Archivo demasiado grande. Máximo 5MB'];
    }

    // ============================================================================
    // PASO 2: PREPARAR DIRECTORIO
    // ============================================================================
    
    if (!is_dir($target_dir)) {
        // Intentar crear directorio con permisos 0775 (más seguro que 0777)
        if (!@mkdir($target_dir, 0775, true)) {
            return [
                'success' => false, 
                'message' => 'No se pudo crear el directorio. Verifica permisos del servidor.'
            ];
        }
    }
    
    // Verificar permisos de escritura ANTES de intentar subir
    if (!is_writable($target_dir)) {
        return [
            'success' => false,
            'message' => 'El directorio no tiene permisos de escritura. Contacta al administrador del servidor.'
        ];
    }

    // ============================================================================
    // PASO 3: GENERAR NOMBRE BASE ÚNICO (SIN EXTENSIÓN)
    // ============================================================================
    
    /**
     * Estrategia de naming:
     * - Prefijo: Personalizable para identificación
     * - Timestamp: Evita colisiones por tiempo
     * - Random: 8 caracteres hex para unicidad adicional
     * - Sin extensión: Permite cambiar formato después
     * 
     * Ejemplo: user_1707331440_a1b2c3d4
     */
    $timestamp = time();
    $random = bin2hex(random_bytes(4));
    $base_name = "{$prefix}{$timestamp}_{$random}";

    // ============================================================================
    // PASO 4: GUARDAR TEMPORALMENTE CON EXTENSIÓN ORIGINAL
    // ============================================================================
    
    $temp_file = $target_dir . $base_name . ".{$file_extension}";
    
    if (!move_uploaded_file($file["tmp_name"], $temp_file)) {
        return ['success' => false, 'message' => 'Error al guardar el archivo'];
    }

    // ============================================================================
    // PASO 5: CONVERTIR A WEBP
    // ============================================================================
    
    $final_filename = $base_name . ".{$file_extension}"; // Fallback si falla conversión
    
    // Intentar conversión a WebP (requiere GD extension)
    if ($file_extension !== 'webp') {
        $webp_path = convertToWebP($temp_file);
        
        if ($webp_path !== false) {
            // ✅ Conversión exitosa
            $final_filename = $base_name . ".webp";
            $final_file = $target_dir . $final_filename;
            
            // Renombrar archivo convertido con nombre base consistente
            if (rename($webp_path, $final_file)) {
                // Eliminar archivo original (ya no se necesita)
                @unlink($temp_file);
            } else {
                // Si falla el rename, usar el archivo temporal
                $final_filename = $base_name . ".{$file_extension}";
            }
        } else {
            // ⚠️ Conversión falló, mantener formato original
            $final_filename = $base_name . ".{$file_extension}";
        }
    } else {
        // El archivo ya es WebP, solo mantener nombre temporal
        $final_filename = $base_name . ".webp";
    }

    // ============================================================================
    // PASO 6: RETORNAR RUTA RELATIVA
    // ============================================================================
    
    /**
     * Ruta relativa desde la raíz del proyecto
     * Esto es lo que se guarda en la BD y se usa en URLs
     * 
     * Ejemplo: images/profiles/user_1707331440_a1b2c3d4.webp
     * URL final: http://localhost:3000/vivaServer/images/profiles/user_1707331440_a1b2c3d4.webp
     */
    // Asegurar que termine en /
    $web_path_folder = rtrim($web_path_folder, '/') . '/';
    $relative_path = $web_path_folder . $final_filename;
    
    return [
        'success' => true,
        'path' => $relative_path,
        'filename' => $final_filename
    ];
}