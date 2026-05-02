<?php
/**
 * VIVA - Generic Image Upload Handler
 *
 * Centraliza validación, guardado y conversión a WebP.
 */

require_once __DIR__ . '/image_processing.php';

function cleanupUploadedFiles(array $paths)
{
    foreach ($paths as $path) {
        if (is_string($path) && $path !== '' && file_exists($path)) {
            unlink($path);
        }
    }
}

function hasUploadedImages($files)
{
    if (!is_array($files) || !isset($files['error'])) {
        return false;
    }

    if (!is_array($files['error'])) {
        return $files['error'] !== UPLOAD_ERR_NO_FILE;
    }

    foreach ($files['error'] as $error) {
        if ($error !== UPLOAD_ERR_NO_FILE) {
            return true;
        }
    }

    return false;
}

/**
 * Procesa una o varias imágenes.
 *
 * Fail-fast real:
 * - Si una sola imagen falla validación, aborta todo.
 * - Si falla guardado/conversión de una imagen, limpia archivos previos y aborta.
 *
 * @param array|null $files Archivo directo de $_FILES['campo']
 * @param string $target_dir Ruta ABSOLUTA del directorio destino
 * @param string $prefix Prefijo para nombre de archivo
 * @param string $web_path_folder Carpeta relativa para guardar en BD
 * @return array
 */
function processAndUploadImages($files, $target_dir, $prefix = 'img_', $web_path_folder = 'images/')
{
    if (!is_array($files) || !isset($files['name'], $files['tmp_name'], $files['error'], $files['size'])) {
        return ['success' => false, 'message' => 'No se seleccionó ningún archivo'];
    }

    $allowedFormats = ['jpg', 'jpeg', 'png', 'webp'];
    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
    $maxSize = 5 * 1024 * 1024;
    $filesToProcess = [];

    if (!is_array($files['name'])) {
        $files = [
            'name' => [$files['name']],
            'type' => [isset($files['type']) ? $files['type'] : ''],
            'tmp_name' => [$files['tmp_name']],
            'error' => [$files['error']],
            'size' => [$files['size']]
        ];
    }

    $totalFiles = count($files['name']);

    for ($index = 0; $index < $totalFiles; $index++) {
        $currentFile = [
            'name' => $files['name'][$index] ?? '',
            'type' => $files['type'][$index] ?? '',
            'tmp_name' => $files['tmp_name'][$index] ?? '',
            'error' => $files['error'][$index] ?? UPLOAD_ERR_NO_FILE,
            'size' => $files['size'][$index] ?? 0
        ];

        if (($currentFile['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            continue;
        }

        if (($currentFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Error al subir imagen: ' . $currentFile['name']];
        }

        if (($currentFile['size'] ?? 0) > $maxSize) {
            return ['success' => false, 'message' => 'La imagen ' . $currentFile['name'] . ' excede el tamaño máximo de 5MB'];
        }

        $fileExtension = strtolower(pathinfo($currentFile['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExtension, $allowedFormats, true)) {
            return ['success' => false, 'message' => 'La imagen ' . $currentFile['name'] . ' tiene un formato no válido. Use JPG, PNG o WEBP'];
        }

        $imageInfo = getimagesize($currentFile['tmp_name']);
        if ($imageInfo === false || !isset($imageInfo['mime']) || !in_array($imageInfo['mime'], $allowedMimeTypes, true)) {
            return ['success' => false, 'message' => 'La imagen ' . $currentFile['name'] . ' no es válida'];
        }

        $currentFile['extension'] = $fileExtension;
        $filesToProcess[] = $currentFile;
    }

    if (empty($filesToProcess)) {
        return ['success' => false, 'message' => 'No se seleccionó ningún archivo'];
    }

    if (!is_dir($target_dir) && !mkdir($target_dir, 0775, true)) {
        return ['success' => false, 'message' => 'No se pudo crear el directorio de destino'];
    }

    if (!is_writable($target_dir)) {
        return ['success' => false, 'message' => 'El directorio no tiene permisos de escritura'];
    }

    $targetDir = rtrim($target_dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    $webPathFolder = rtrim($web_path_folder, '/') . '/';
    $storedPaths = [];
    $relativePaths = [];
    $uploadedResults = [];

    foreach ($filesToProcess as $index => $currentFile) {
        try {
            $baseName = $prefix . time() . '_' . $index . '_' . bin2hex(random_bytes(4));
        } catch (Exception $e) {
            cleanupUploadedFiles($storedPaths);
            throw $e;
        }

        $tempFile = $targetDir . $baseName . '.' . $currentFile['extension'];

        if (!move_uploaded_file($currentFile['tmp_name'], $tempFile)) {
            cleanupUploadedFiles($storedPaths);
            return ['success' => false, 'message' => 'Error al guardar la imagen ' . $currentFile['name']];
        }

        try {
            $variants = generateVariants($tempFile);
        } catch (Throwable $e) {
            cleanupUploadedFiles([$tempFile]);
            cleanupUploadedFiles($storedPaths);
            return ['success' => false, 'message' => 'No se pudieron generar variantes para la imagen ' . $currentFile['name']];
        }

        if ($variants === false || !isset($variants['thumb'], $variants['medium'], $variants['full'])) {
            cleanupUploadedFiles([$tempFile]);
            cleanupUploadedFiles($storedPaths);
            return ['success' => false, 'message' => 'No se pudieron generar variantes para la imagen ' . $currentFile['name']];
        }

        cleanupUploadedFiles([$tempFile]);

        $relativeVariants = [];
        foreach ($variants as $variantName => $variantPath) {
            $storedPaths[] = $variantPath;
            $relativeVariants[$variantName] = $webPathFolder . basename($variantPath);
        }

        $primaryPath = $relativeVariants['full'];
        $relativePaths[] = $primaryPath;
        $uploadedResults[] = [
            'path' => $primaryPath,
            'paths' => array_values($relativeVariants),
            'variants' => $relativeVariants,
            'filename' => basename($variants['thumb']),
            'filenames' => array_map('basename', array_values($variants))
        ];
    }

    if (count($uploadedResults) === 1) {
        return array_merge([
            'success' => true,
        ], $uploadedResults[0]);
    }

    return [
        'success' => true,
        'paths' => $relativePaths,
        'images' => $uploadedResults,
        'variants' => array_column($uploadedResults, 'variants'),
        'filenames' => array_map('basename', $storedPaths)
    ];
}

function handleImageUpload($file, $target_dir, $prefix = 'img_', $web_path_folder = 'images/')
{
    return processAndUploadImages($file, $target_dir, $prefix, $web_path_folder);
}
