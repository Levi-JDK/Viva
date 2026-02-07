<?php
// Función para convertir imágenes a WebP
function convertToWebP($sourcePath, $quality = 80) {
    if (!file_exists($sourcePath)) {
        return false;
    }

    $fileInfo = getimagesize($sourcePath);
    $mimeType = $fileInfo['mime'];

    switch ($mimeType) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($sourcePath);
            break;
        case 'image/png':
            $image = imagecreatefrompng($sourcePath);
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);
            break;
        case 'image/gif':
            $image = imagecreatefromgif($sourcePath);
            break;
        default:
            return false;
    }

    $destinationPath = pathinfo($sourcePath, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR . pathinfo($sourcePath, PATHINFO_FILENAME) . '.webp';

    if (imagewebp($image, $destinationPath, $quality)) {
        imagedestroy($image);
        return $destinationPath;
    }

    imagedestroy($image);
    return false;
}
?>
