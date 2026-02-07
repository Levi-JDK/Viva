<?php
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2));
$dotenv->load();
class Database{
        public $connection;
        public function __construct($config,$username,$password){    
            $dsn =('pgsql:'.http_build_query($config,'',';'));
            $this->connection= new PDO($dsn, $username,$password,[
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]); 
        }
        public function query($query){
            $statement = $this ->connection->prepare($query);
            $statement->execute();
            return $statement;           
        }
    }
?>
