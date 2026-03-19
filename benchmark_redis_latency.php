<?php
require_once __DIR__ . '/src/workers/Config/RedisConfig.php';

echo "Iniciando Benchmark de Latencia Redis...\n\n";

try {
    $redis = RedisConfig::getConnection();
    $prefix = RedisConfig::getPrefix() . 'bench:';
    
    $operations = [
        'PING', 'SET', 'GET', 'INCR', 'EXISTS', 'HSET', 'HGET', 'HGETALL', 'LPUSH', 'SISMEMBER', 'SADD', 'SREM'
    ];
    
    $results = [];
    $iterations = 10;
    
    // Preparar datos de prueba
    $testKey = $prefix . 'test_key';
    $testHash = $prefix . 'test_hash';
    $testList = $prefix . 'test_list';
    $testSet = $prefix . 'test_set';
    
    // Limpiar antes de empezar
    $redis->del([$testKey, $testHash, $testList, $testSet]);
    
    foreach ($operations as $op) {
        $times = [];
        
        for ($i = 0; $i < $iterations; $i++) {
            $start = microtime(true);
            
            switch ($op) {
                case 'PING':
                    $redis->ping();
                    break;
                case 'SET':
                    $redis->set($testKey, 'value');
                    break;
                case 'GET':
                    $redis->get($testKey);
                    break;
                case 'INCR':
                    $redis->incr($testKey . '_counter');
                    break;
                case 'EXISTS':
                    $redis->exists($testKey);
                    break;
                case 'HSET':
                    $redis->hset($testHash, 'field1', 'value1');
                    break;
                case 'HGET':
                    $redis->hget($testHash, 'field1');
                    break;
                case 'HGETALL':
                    $redis->hgetall($testHash);
                    break;
                case 'LPUSH':
                    $redis->lpush($testList, 'item');
                    break;
                case 'SISMEMBER':
                    $redis->sismember($testSet, 'member');
                    break;
                case 'SADD':
                    $redis->sadd($testSet, 'member');
                    break;
                case 'SREM':
                    $redis->srem($testSet, 'member');
                    break;
            }
            
            $end = microtime(true);
            $times[] = ($end - $start) * 1000; // Convertir a ms
        }
        
        $avg = array_sum($times) / count($times);
        $min = min($times);
        $max = max($times);
        
        $results[] = [
            'op' => $op,
            'avg' => round($avg, 2),
            'min' => round($min, 2),
            'max' => round($max, 2)
        ];
    }
    
    // Limpiar después de terminar
    $redis->del([$testKey, $testHash, $testList, $testSet, $testKey . '_counter']);
    
    // Imprimir tabla
    $mask = "| %-12s | %-10s | %-10s | %-10s |\n";
    printf($mask, 'Operación', 'Promedio(ms)', 'Min(ms)', 'Max(ms)');
    echo str_repeat('-', 55) . "\n";
    
    foreach ($results as $r) {
        printf($mask, $r['op'], $r['avg'], $r['min'], $r['max']);
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
