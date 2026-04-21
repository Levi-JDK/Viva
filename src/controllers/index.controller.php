<?php
require_once ROOT_PATH . 'src/services/HomeService.php';

extract(HomeService::obtenerDatosLanding());

require_once ROOT_PATH . 'src/views/index.view.php';
?>
