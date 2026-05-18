<?php
/**
 * Secure file downloader for docs/ directory.
 * Forces download with proper Content-Disposition headers.
 * Prevents directory traversal and restricts to allowed extensions.
 */

$allowed_exts = ['xlsx', 'xls', 'csv', 'pdf', 'md', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'webp', 'pptx', 'sql', 'docx'];
$docs_dir = __DIR__;
$file = $_GET['file'] ?? '';

// Prevent directory traversal
$file = basename($file);
$path = $docs_dir . '/' . $file;

// Validate file exists
if (!$file || !file_exists($path) || !is_file($path)) {
    http_response_code(404);
    echo 'Archivo no encontrado.';
    exit;
}

// Validate extension
$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
if (!in_array($ext, $allowed_exts, true)) {
    http_response_code(403);
    echo 'Tipo de archivo no permitido.';
    exit;
}

// MIME types
$mime_types = [
    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'xls'  => 'application/vnd.ms-excel',
    'csv'  => 'text/csv',
    'pdf'  => 'application/pdf',
    'md'   => 'text/markdown',
    'png'  => 'image/png',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'gif'  => 'image/gif',
    'svg'  => 'image/svg+xml',
    'webp' => 'image/webp',
    'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'sql'  => 'text/plain',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
];

$mime = $mime_types[$ext] ?? 'application/octet-stream';

// Headers
header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . $file . '"');
header('Content-Length: ' . filesize($path));
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Output file
readfile($path);
exit;
