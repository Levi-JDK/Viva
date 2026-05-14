<?php

require_once __DIR__ . '/../services/TextEmbeddingService.php';

$rules = [
    1 => ['type' => 'artisan_policy',   'content' => 'Se considera ARTESANAL todo producto hecho total o predominantemente a mano, utilizando técnicas tradicionales como telar, crochet, cestería, alfarería, talla en madera, filigrana, barniz de Pasto, trabajo en werregue, orfebrería precolombina o marroquinería artesanal. Las materias primas deben ser naturales o tradicionales: lana, fique, caña flecha, iraca, arcilla, madera, semillas, metales preciosos, bronce, cobre.'],
    2 => ['type' => 'artisan_policy',   'content' => 'Un producto es APROBADO como artesanal si demuestra técnicas manuales tradicionales, uso de materias primas naturales o tradicionales, y refleja el oficio artesanal declarado. Aplica también a piezas vintage, antigüedades precolombinas y artículos de mercadillo con valor cultural comprobable, siempre que NO sean producción industrial.'],
    3 => ['type' => 'artisan_policy',   'content' => 'Alimentos y bebidas artesanales siguen procesos tradicionales de transformación como cacao artesanal, café de origen, viche destilado en alambique, miel nativa, plantas medicinales secadas a mano. Productos con empaque industrial o registro Invima industrial se consideran dudosos → revisión humana.'],
    4 => ['type' => 'plagiarism_policy', 'content' => 'Se considera PLAGIO cuando un producto utiliza la MISMA IMAGEN (hash exacto) que otro producto de DISTINTO productor. Esto aplica incluso si el nombre del producto o la descripción son diferentes. En ese caso → revision_humana directa, sin pasar por IA.'],
    5 => ['type' => 'plagiarism_policy', 'content' => 'Dos productos del MISMO productor con la misma imagen NO son plagio. Si un productor reutiliza sus propias imágenes para distintos productos, no se marca. La similitud perceptual (misma imagen con distinta resolución/compresión) se detecta por pHash/dHash con distancia de Hamming.'],
];

$count = 0;
foreach ($rules as $id => $rule) {
    try {
        TextEmbeddingService::saveRagRule($id, $rule['type'], $rule['content']);
        $count++;
        echo "✅ {$rule['type']} #{$id} guardada\n";
    } catch (Exception $e) {
        echo "❌ {$rule['type']} #{$id}: " . $e->getMessage() . "\n";
    }
}

echo "\n✅ {$count} reglas guardadas correctamente\n";
