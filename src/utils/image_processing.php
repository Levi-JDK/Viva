<?php
if (!defined('IMAGE_VARIANTS')) {
    define('IMAGE_VARIANTS', [
        'thumb' => ['w' => 300, 'h' => 300],
        'medium' => ['w' => 600, 'h' => 600],
        'full' => ['w' => 1200, 'h' => 0],
    ]);
}

/**
 * Estimate the memory GD needs to process an image.
 */
function estimateMemoryNeeded($width, $height) {
    return (int) ceil($width * $height * 4 * 2.5);
}

/**
 * Return currently available PHP memory in bytes.
 */
function getAvailableMemory() {
    $memoryLimit = ini_get('memory_limit');

    if ($memoryLimit === false || $memoryLimit === '' || $memoryLimit === '-1') {
        return PHP_INT_MAX;
    }

    $unit = strtolower(substr(trim($memoryLimit), -1));
    $value = (float) $memoryLimit;

    switch ($unit) {
        case 'g':
            $value *= 1024;
            // no break
        case 'm':
            $value *= 1024;
            // no break
        case 'k':
            $value *= 1024;
            break;
    }

    return max(0, (int) $value - memory_get_usage(true));
}

function createImageResourceFromPath($sourcePath, $mimeType) {
    switch ($mimeType) {
        case 'image/jpeg':
            return imagecreatefromjpeg($sourcePath);
        case 'image/png':
            $image = imagecreatefrompng($sourcePath);
            if ($image !== false) {
                imagepalettetotruecolor($image);
                imagealphablending($image, true);
                imagesavealpha($image, true);
            }
            return $image;
        case 'image/gif':
            return imagecreatefromgif($sourcePath);
        case 'image/webp':
            return function_exists('imagecreatefromwebp') ? imagecreatefromwebp($sourcePath) : false;
        default:
            return false;
    }
}

// Función para convertir imágenes a WebP
function convertToWebP($sourcePath, $quality = 80) {
    if (!file_exists($sourcePath)) {
        return false;
    }

    $fileInfo = getimagesize($sourcePath);
    if ($fileInfo === false || !isset($fileInfo['mime'])) {
        return false;
    }

    $mimeType = $fileInfo['mime'];
    $image = createImageResourceFromPath($sourcePath, $mimeType);

    if ($image === false) {
        return false;
    }

    $destinationPath = pathinfo($sourcePath, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR . pathinfo($sourcePath, PATHINFO_FILENAME) . '.webp';
    if ($mimeType === 'image/webp' && $destinationPath === $sourcePath) {
        imagedestroy($image);
        return $sourcePath;
    }

    if (imagewebp($image, $destinationPath, $quality)) {
        imagedestroy($image);
        return $destinationPath;
    }

    imagedestroy($image);
    return false;
}

/**
 * Resize an image preserving aspect ratio, without upscaling.
 *
 * @return GdImage|resource|false
 */
function resizeImage($source, $maxWidth, $maxHeight) {
    if (!file_exists($source)) {
        return false;
    }

    $imageInfo = getimagesize($source);
    if ($imageInfo === false || !isset($imageInfo[0], $imageInfo[1], $imageInfo['mime'])) {
        return false;
    }

    $sourceWidth = (int) $imageInfo[0];
    $sourceHeight = (int) $imageInfo[1];
    $maxWidth = (int) $maxWidth;
    $maxHeight = (int) $maxHeight;

    if ($sourceWidth <= 0 || $sourceHeight <= 0 || $maxWidth <= 0) {
        return false;
    }

    $scale = min(1, $maxWidth / $sourceWidth);
    if ($maxHeight > 0) {
        $scale = min($scale, $maxHeight / $sourceHeight);
    }

    $targetWidth = max(1, (int) round($sourceWidth * $scale));
    $targetHeight = max(1, (int) round($sourceHeight * $scale));

    $sourceImage = createImageResourceFromPath($source, $imageInfo['mime']);
    if ($sourceImage === false) {
        return false;
    }

    $targetImage = imagecreatetruecolor($targetWidth, $targetHeight);
    if ($targetImage === false) {
        imagedestroy($sourceImage);
        return false;
    }

    if ($imageInfo['mime'] === 'image/png' || $imageInfo['mime'] === 'image/webp') {
        imagealphablending($targetImage, false);
        imagesavealpha($targetImage, true);
        $transparent = imagecolorallocatealpha($targetImage, 0, 0, 0, 127);
        imagefilledrectangle($targetImage, 0, 0, $targetWidth, $targetHeight, $transparent);
    }

    if (!imagecopyresampled($targetImage, $sourceImage, 0, 0, 0, 0, $targetWidth, $targetHeight, $sourceWidth, $sourceHeight)) {
        imagedestroy($sourceImage);
        imagedestroy($targetImage);
        return false;
    }

    imagedestroy($sourceImage);
    return $targetImage;
}

/**
 * Generate WebP image variants in the source directory.
 */
function generateVariants($source, $config = null) {
    if (!file_exists($source)) {
        return false;
    }

    $variants = is_array($config) ? $config : IMAGE_VARIANTS;
    $imageInfo = getimagesize($source);
    if ($imageInfo === false || !isset($imageInfo[0], $imageInfo[1])) {
        return false;
    }

    $directory = pathinfo($source, PATHINFO_DIRNAME);
    $baseName = pathinfo($source, PATHINFO_FILENAME);
    $createdFiles = [];
    $result = [];
    $memoryNeeded = estimateMemoryNeeded((int) $imageInfo[0], (int) $imageInfo[1]);
    $canResize = $memoryNeeded <= getAvailableMemory();

    if (!$canResize) {
        throw new RuntimeException('Insufficient memory to process image variants');
    }

    try {
        foreach ($variants as $name => $variant) {
            $maxWidth = isset($variant['w']) ? (int) $variant['w'] : 0;
            $maxHeight = isset($variant['h']) ? (int) $variant['h'] : 0;
            // Nombres predecibles: basename.webp (full), basename_thumb.webp, basename_medium.webp
            $suffix = ($name === 'full') ? '' : '_' . $name;
            $destination = $directory . DIRECTORY_SEPARATOR . $baseName . $suffix . '.webp';

            if ($maxWidth <= 0) {
                throw new RuntimeException('Invalid image variant width: ' . $name);
            }

            $resizedImage = resizeImage($source, $maxWidth, $maxHeight);
            if ($resizedImage === false || !imagewebp($resizedImage, $destination, 80)) {
                if ($resizedImage !== false) {
                    imagedestroy($resizedImage);
                }
                throw new RuntimeException('Could not generate image variant: ' . $name);
            }
            imagedestroy($resizedImage);

            $createdFiles[] = $destination;
            $result[$name] = $destination;
        }
    } catch (Throwable $e) {
        cleanupGeneratedVariants($createdFiles);
        throw $e;
    }

    return $result;
}

function cleanupGeneratedVariants(array $paths) {
    foreach ($paths as $path) {
        if (is_string($path) && $path !== '' && file_exists($path)) {
            unlink($path);
        }
    }
}

/**
 * Obtiene la URL de una variante a partir de la URL base (full).
 * 
 * @param string $baseUrl URL base de la imagen full (ej: images/products/prod_123.webp)
 * @param string $variant Nombre de la variante: 'thumb', 'medium', 'full'
 * @return string URL de la variante solicitada
 */
function getImageVariant(string $baseUrl, string $variant = 'full'): string {
    if ($variant === 'full') {
        return $baseUrl;
    }
    
    // Insertar el sufijo antes de .webp
    return str_replace('.webp', '_' . $variant . '.webp', $baseUrl);
}
?>
