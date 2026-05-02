#!/usr/bin/env php
<?php
/**
 * VIVA - Batch Regenerate Image Variants
 *
 * Procesa imágenes PNG/JPEG existentes y genera variantes WebP
 * (thumb, medium, full) usando el nuevo image_processing.php
 *
 * Uso: php batch-regenerate-variants.php [directorio] [--dry-run]
 */

require_once __DIR__ . '/../src/utils/image_processing.php';

$dryRun = in_array('--dry-run', $argv, true);
$targetDir = $argv[1] ?? __DIR__ . '/../images';
$targetDir = realpath($targetDir);

if ($targetDir === false || !is_dir($targetDir)) {
    fwrite(STDERR, "Directorio no válido: {$argv[1]}\n");
    exit(1);
}

$extensions = ['png', 'jpg', 'jpeg', 'gif'];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($targetDir, RecursiveDirectoryIterator::SKIP_DOTS)
);

$processed = 0;
$failed = 0;
$skipped = 0;
$bytesBefore = 0;
$bytesAfter = 0;

foreach ($iterator as $file) {
    if (!$file->isFile()) continue;

    $ext = strtolower($file->getExtension());
    if (!in_array($ext, $extensions, true)) continue;

    $path = $file->getPathname();
    $sizeBefore = filesize($path);
    $bytesBefore += $sizeBefore;

    echo "Procesando: " . basename($path) . " (" . number_format($sizeBefore / 1024, 1) . " KB)\n";

    if ($dryRun) {
        $skipped++;
        continue;
    }

    try {
        // Backup de la original
        $backupPath = $path . '.backup';
        if (!file_exists($backupPath)) {
            copy($path, $backupPath);
        }

        $variants = generateVariants($path);

        if ($variants === false || empty($variants)) {
            echo "  ❌ Falló generación de variantes\n";
            $failed++;
            continue;
        }

        $sizeAfter = 0;
        foreach ($variants as $name => $variantPath) {
            $vSize = filesize($variantPath);
            $sizeAfter += $vSize;
            echo "  ✅ {$name}: " . basename($variantPath) . " (" . number_format($vSize / 1024, 1) . " KB)\n";
        }

        $bytesAfter += $sizeAfter;
        $processed++;

        // Si la original era WebP ya generada por el uploader, no la borramos
        // Si es PNG/JPEG vieja, la renombramos a .backup.original para dejar solo WebPs activos
        if ($ext !== 'webp') {
            rename($path, $path . '.original');
        }

    } catch (Throwable $e) {
        echo "  ❌ Error: " . $e->getMessage() . "\n";
        $failed++;
    }
}

echo "\n========================================\n";
echo "Resumen:\n";
echo "  Procesadas: {$processed}\n";
echo "  Fallidas:   {$failed}\n";
echo "  Skipped:    {$skipped}\n";
echo "  Tamaño original total: " . number_format($bytesBefore / 1024 / 1024, 2) . " MB\n";
echo "  Tamaño variantes total: " . number_format($bytesAfter / 1024 / 1024, 2) . " MB\n";
if ($bytesBefore > 0) {
    $saved = (1 - ($bytesAfter / $bytesBefore)) * 100;
    echo "  Ahorro estimado: " . number_format($saved, 1) . "%\n";
}
echo "========================================\n";
