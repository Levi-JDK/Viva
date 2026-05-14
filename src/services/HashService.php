<?php

class HashService
{
    public static function hashFile(string $imagePath): array
    {
        return [
            'file_hash' => self::sha256($imagePath),
            'phash' => self::pHash($imagePath),
            'dhash' => self::dHash($imagePath),
        ];
    }

    public static function hash(string $imagePath): array
    {
        return self::hashFile($imagePath);
    }

    public static function sha256(string $imagePath): string
    {
        self::assertReadableFile($imagePath);

        $hash = hash_file('sha256', $imagePath);
        if ($hash === false) {
            throw new InvalidArgumentException('No se pudo calcular el hash SHA256 de la imagen.');
        }

        return strtolower($hash);
    }

    public static function pHash(string $imagePath): string
    {
        $image = self::loadImage($imagePath);
        $resized = null;

        try {
            $resized = self::resize($image, 32, 32);
            $matrix = self::toGrayscale($resized, 32, 32);
            $dct = self::dct2D($matrix, 32);
            $values = [];

            for ($y = 0; $y < 8; $y++) {
                for ($x = 0; $x < 8; $x++) {
                    $values[] = $dct[$y][$x];
                }
            }

            $median = self::median($values);
            $bits = '';

            foreach ($values as $value) {
                $bits .= $value >= $median ? '1' : '0';
            }

            return self::bitsToBin64($bits);
        } finally {
            if ($resized instanceof GdImage) {
                imagedestroy($resized);
            }
            imagedestroy($image);
        }
    }

    public static function dHash(string $imagePath): string
    {
        $image = self::loadImage($imagePath);
        $resized = null;

        try {
            $resized = self::resize($image, 9, 8);
            $matrix = self::toGrayscale($resized, 9, 8);
            $bits = '';

            for ($y = 0; $y < 8; $y++) {
                for ($x = 0; $x < 8; $x++) {
                    $bits .= $matrix[$y][$x] > $matrix[$y][$x + 1] ? '1' : '0';
                }
            }

            return self::bitsToBin64($bits);
        } finally {
            if ($resized instanceof GdImage) {
                imagedestroy($resized);
            }
            imagedestroy($image);
        }
    }

    public static function supportedFormats(): array
    {
        $formats = [];

        if (function_exists('imagecreatefromjpeg')) {
            $formats[] = 'jpeg';
        }

        if (function_exists('imagecreatefrompng')) {
            $formats[] = 'png';
        }

        if (function_exists('imagecreatefromwebp')) {
            $formats[] = 'webp';
        }

        return $formats;
    }

    private static function assertReadableFile(string $imagePath): void
    {
        if ($imagePath === '' || !is_file($imagePath) || !is_readable($imagePath)) {
            throw new InvalidArgumentException('La imagen no existe o no se puede leer.');
        }
    }

    private static function loadImage(string $path): GdImage
    {
        self::assertReadableFile($path);

        $type = @exif_imagetype($path);
        $image = false;

        if ($type === IMAGETYPE_JPEG && function_exists('imagecreatefromjpeg')) {
            $image = @imagecreatefromjpeg($path);
        } elseif ($type === IMAGETYPE_PNG && function_exists('imagecreatefrompng')) {
            $image = @imagecreatefrompng($path);
        } elseif ($type === IMAGETYPE_WEBP && function_exists('imagecreatefromwebp')) {
            $image = @imagecreatefromwebp($path);
        }

        if (!$image instanceof GdImage) {
            throw new InvalidArgumentException('Formato de imagen no soportado o archivo inválido.');
        }

        return $image;
    }

    private static function resize(GdImage $image, int $width, int $height): GdImage
    {
        $resized = imagecreatetruecolor($width, $height);
        if (!$resized instanceof GdImage) {
            throw new RuntimeException('No se pudo crear el recurso GD de destino.');
        }

        imagealphablending($resized, false);
        imagesavealpha($resized, true);

        $sourceWidth = imagesx($image);
        $sourceHeight = imagesy($image);

        if (!imagecopyresampled($resized, $image, 0, 0, 0, 0, $width, $height, $sourceWidth, $sourceHeight)) {
            imagedestroy($resized);
            throw new RuntimeException('No se pudo redimensionar la imagen.');
        }

        return $resized;
    }

    private static function toGrayscale(GdImage $image, int $width, int $height): array
    {
        $matrix = [];

        for ($y = 0; $y < $height; $y++) {
            $row = [];
            for ($x = 0; $x < $width; $x++) {
                $rgb = imagecolorat($image, $x, $y);
                $red = ($rgb >> 16) & 0xFF;
                $green = ($rgb >> 8) & 0xFF;
                $blue = $rgb & 0xFF;
                $row[] = ($red + $green + $blue) / 3;
            }
            $matrix[] = $row;
        }

        return $matrix;
    }

    private static function dct2D(array $matrix, int $size): array
    {
        $result = [];
        $factor = pi() / (2 * $size);

        for ($u = 0; $u < $size; $u++) {
            $row = [];
            $alphaU = $u === 0 ? sqrt(1 / $size) : sqrt(2 / $size);

            for ($v = 0; $v < $size; $v++) {
                $alphaV = $v === 0 ? sqrt(1 / $size) : sqrt(2 / $size);
                $sum = 0.0;

                for ($y = 0; $y < $size; $y++) {
                    $cosY = cos((2 * $y + 1) * $u * $factor);
                    for ($x = 0; $x < $size; $x++) {
                        $sum += $matrix[$y][$x] * $cosY * cos((2 * $x + 1) * $v * $factor);
                    }
                }

                $row[] = $alphaU * $alphaV * $sum;
            }

            $result[] = $row;
        }

        return $result;
    }

    private static function median(array $values): float
    {
        sort($values, SORT_NUMERIC);
        $count = count($values);
        $middle = intdiv($count, 2);

        if ($count % 2 === 0) {
            return ((float) $values[$middle - 1] + (float) $values[$middle]) / 2;
        }

        return (float) $values[$middle];
    }

    private static function bitsToBin64(string $bits): string
    {
        if (strlen($bits) !== 64 || preg_match('/^[01]+$/', $bits) !== 1) {
            throw new InvalidArgumentException('El hash perceptual debe contener exactamente 64 bits.');
        }

        return $bits;
    }
}
