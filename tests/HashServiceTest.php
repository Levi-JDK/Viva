<?php

use PHPUnit\Framework\TestCase;

final class HashServiceTest extends TestCase
{
    private static string $fixturesDir;
    private static string $jpegPath;
    private static string $pngPath;

    public static function setUpBeforeClass(): void
    {
        if (!extension_loaded('gd')) {
            self::markTestSkipped('La extensión GD no está disponible.');
        }

        self::$fixturesDir = __DIR__ . '/fixtures';
        if (!is_dir(self::$fixturesDir)) {
            mkdir(self::$fixturesDir, 0775, true);
        }

        self::$jpegPath = self::$fixturesDir . '/hashservice-solid.jpg';
        self::$pngPath = self::$fixturesDir . '/hashservice-gradient.png';

        self::createSolidJpeg(self::$jpegPath);
        self::createGradientPng(self::$pngPath);
    }

    public function testSha256Consistency(): void
    {
        $hash1 = HashService::sha256(self::$jpegPath);
        $hash2 = HashService::sha256(self::$jpegPath);

        $this->assertSame($hash1, $hash2);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $hash1);
    }

    public function testSha256DeterminismAgainstHashFile(): void
    {
        $this->assertSame(hash_file('sha256', self::$pngPath), HashService::sha256(self::$pngPath));
    }

    public function testPHashDeterminism(): void
    {
        $hash1 = HashService::pHash(self::$pngPath);
        $hash2 = HashService::pHash(self::$pngPath);

        $this->assertSame($hash1, $hash2);
        $this->assertMatchesRegularExpression('/^[01]{64}$/', $hash1);
    }

    public function testDHashDeterminism(): void
    {
        $hash1 = HashService::dHash(self::$pngPath);
        $hash2 = HashService::dHash(self::$pngPath);

        $this->assertSame($hash1, $hash2);
        $this->assertMatchesRegularExpression('/^[01]{64}$/', $hash1);
    }

    public function testHashFileReturnsAllHashes(): void
    {
        $hashes = HashService::hashFile(self::$jpegPath);

        $this->assertArrayHasKey('file_hash', $hashes);
        $this->assertArrayHasKey('phash', $hashes);
        $this->assertArrayHasKey('dhash', $hashes);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $hashes['file_hash']);
        $this->assertMatchesRegularExpression('/^[01]{64}$/', $hashes['phash']);
        $this->assertMatchesRegularExpression('/^[01]{64}$/', $hashes['dhash']);
    }

    public function testNonExistentFileThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        HashService::sha256(self::$fixturesDir . '/missing.jpg');
    }

    public function testInvalidImageThrows(): void
    {
        $path = self::$fixturesDir . '/empty.jpg';
        file_put_contents($path, '');

        $this->expectException(InvalidArgumentException::class);

        HashService::pHash($path);
    }

    public function testSupportedFormatsDetection(): void
    {
        $formats = HashService::supportedFormats();

        $this->assertContains('jpeg', $formats);
        $this->assertContains('png', $formats);

        if (function_exists('imagecreatefromwebp')) {
            $this->assertContains('webp', $formats);
        }
    }

    private static function createSolidJpeg(string $path): void
    {
        $image = imagecreatetruecolor(1, 1);
        $color = imagecolorallocate($image, 120, 80, 40);
        imagefill($image, 0, 0, $color);
        imagejpeg($image, $path);
        imagedestroy($image);
    }

    private static function createGradientPng(string $path): void
    {
        $image = imagecreatetruecolor(16, 16);

        for ($y = 0; $y < 16; $y++) {
            for ($x = 0; $x < 16; $x++) {
                $value = (int) round(($x + $y) / 30 * 255);
                $color = imagecolorallocate($image, $value, 255 - $value, (int) round($value / 2));
                imagesetpixel($image, $x, $y, $color);
            }
        }

        imagepng($image, $path);
        imagedestroy($image);
    }
}
