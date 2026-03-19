<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/workers/Config/RedisConfig.php';

$redis = RedisConfig::getConnection();
$prefix = RedisConfig::getPrefix();

$redis->multi();
$redis->sismember($prefix . 'emails:registrados', 'test@test.com');
$redis->setnx('lock:test', 1);
$results = $redis->exec();

var_dump($results);
