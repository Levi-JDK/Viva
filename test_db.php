<?php
require_once 'src/functions/database.php';
$db = Database::getInstance();
$datos = ['id_banco' => 1, 'nom_banco' => 'Bancolombia'];
$res = $db->gestionarCRUDAdmin('delete', 'banco', $datos);
var_dump($res);
