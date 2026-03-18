<?php
require_once __DIR__ . '/src/functions/database.php';

try {
    $db = Database::getInstance();
    
    $stmt = $db->ejecutar('actualizarParametrosGlob', [
        ':id_parametro'            => 1,
        ':nom_plataforma'          => 'Test Viva',
        ':dir_contacto'            => 'Test',
        ':correo_contacto'         => 'test@test.com',
        ':val_inifact'             => 1,
        ':val_finfact'             => 1000,
        ':val_actfact'             => 10,
        ':val_observa'             => 'Test',
        ':landing_hero_titulo'     => 'Hero',
        ':landing_hero_subtitulo'  => 'Sub',
        ':landing_hero_btn'        => 'Btn',
        ':landing_conf_1_tit'      => 'T1',
        ':landing_conf_1_sub'      => 'S1',
        ':landing_conf_2_tit'      => 'T2',
        ':landing_conf_2_sub'      => 'S2',
        ':landing_conf_3_tit'      => 'T3',
        ':landing_conf_3_sub'      => 'S3',
        ':landing_filosofia_tit'   => 'FT1',
        ':landing_filosofia_p1'    => 'FP1',
        ':landing_filosofia_p2'    => 'FP2'
    ]);
    
    $resultado = $stmt->fetchColumn();
    var_dump($resultado);
    echo "\nExito\n";
    
} catch (PDOException $e) {
    echo "PDO Error: " . $e->getMessage();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
