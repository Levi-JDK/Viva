<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📚 Documentación VIVA</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif;
            background-color: #f6f8fa;
            color: #24292f;
            padding: 40px 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        h1 {
            font-size: 32px;
            margin-bottom: 10px;
            color: #1f2328;
        }
        .subtitle {
            color: #656d76;
            margin-bottom: 30px;
            font-size: 16px;
        }
        .file-list {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.12);
            overflow: hidden;
        }
        .file-item {
            display: flex;
            align-items: center;
            padding: 16px 20px;
            border-bottom: 1px solid #d0d7de;
            text-decoration: none;
            color: #24292f;
            transition: background 0.2s;
        }
        .file-item:last-child {
            border-bottom: none;
        }
        .file-item:hover {
            background-color: #f6f8fa;
        }
        .file-icon {
            font-size: 24px;
            margin-right: 16px;
        }
        .file-info {
            flex: 1;
        }
        .file-name {
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 4px;
        }
        .file-meta {
            font-size: 13px;
            color: #656d76;
        }
        .file-arrow {
            color: #656d76;
            font-size: 18px;
        }
        .file-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .btn-download-sm {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #1f883d;
            color: #fff;
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            border: 1px solid rgba(31,35,40,0.15);
            transition: background 0.2s;
            white-space: nowrap;
        }
        .btn-download-sm:hover {
            background: #1a7f37;
        }
        .empty {
            padding: 40px;
            text-align: center;
            color: #656d76;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📚 Documentación del Proyecto</h1>
        <p class="subtitle">Hacé clic en cualquier archivo para verlo renderizado.</p>
        
        <div class="file-list">
            <?php
$files = glob("*.{md,xlsx,xls,csv,png,jpeg,gif,svg,webp,pptx,sql}", GLOB_BRACE);
            if (empty($files)) {
                echo '<div class="empty">No hay archivos de documentación en esta carpeta.</div>';
            } else {
                foreach ($files as $file) {
                    $size = filesize($file);
                    $size_str = $size > 1024 ? round($size/1024, 1) . ' KB' : $size . ' B';
                    $date = date('d/m/Y H:i', filemtime($file));
                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    $image_exts = ['png', 'jpg', 'jpeg', 'gif', 'svg', 'webp'];
                    if ($ext === 'md') {
                        $icon = '📄';
                    } elseif ($ext === 'sql') {
                        $icon = '🗄️';
                    } elseif (in_array($ext, $image_exts)) {
                        $icon = '🖼️';
                    } else {
                        $icon = '📊';
                    }
                    
                    echo '<div class="file-item">';
                    echo '<a href="viewer.html?file=' . urlencode($file) . '" style="display:flex;align-items:center;text-decoration:none;color:inherit;flex:1;">';
                    echo '<div class="file-icon">' . $icon . '</div>';
                    echo '<div class="file-info">';
                    echo '<div class="file-name">' . htmlspecialchars($file) . '</div>';
                    echo '<div class="file-meta">' . $size_str . ' &middot; Modificado el ' . $date . '</div>';
                    echo '</div>';
                    echo '</a>';
                    echo '<div class="file-actions">';
                    echo '<a href="download.php?file=' . urlencode($file) . '" class="btn-download-sm">⬇️ Descargar</a>';
                    echo '<div class="file-arrow">›</div>';
                    echo '</div>';
                    echo '</div>';
                }
            }
            ?>
        </div>
    </div>
</body>
</html>
