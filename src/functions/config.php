<?php 
    return [
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'port' => $_ENV['DB_PORT'] ?? 5432,
        'dbname' => 'db_viva',
    ];

?>