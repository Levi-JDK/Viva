<?php

require 'vendor/autoload.php';

use Predis\Client as PredisClient;

$r = new PredisClient([
                'scheme'   => 'tcp',
                'host'     => '127.0.0.1',
                'port'     => 6379,
                'password' => '',
                'database' => 0,
            ]);

$nombre = 'Sebas';
$apellido = 'Duran';
$mail = 'ssdsdasadasdsds@mail.com';
$pass = 'Sebaslomama10#';

$hash = password_hash($pass, PASSWORD_ARGON2ID);

$respuesta = $r-> set('registro:'.$mail,'1','NX');
if ($respuesta) { 
    $id = $r-> incr('contador:usuarios');
    if($r-> hset('user:'.$id,'nombre',$nombre,'apellido',$apellido,'mail',$mail,'password',$hash)){
        $r->lpush('cola:registros', $id);
        echo 'Registro en cola, esperando por subir. Ticket #'.$id;
        } else {
            echo ' no creo :D';
    }
} else {    
    echo 'Este correo ya este registrado, utiliza otro';
}
