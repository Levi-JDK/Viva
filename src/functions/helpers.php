<?php
/**
 * Funciones helper compartidas por scripts y servicios PHP.
 */

if (!function_exists('hamming_distance_php')) {
    /**
     * Calcula la distancia Hamming entre dos hashes perceptuales BIGINT de 64 bits.
     *
     * Es equivalente a `fun_hamming_distance()` en PostgreSQL: compara bit a bit
     * los 64 bits, incluyendo el bit de signo cuando PHP representa el BIGINT
     * como entero firmado.
     */
    function hamming_distance_php(int $a, int $b): int
    {
        $xor = $a ^ $b;
        $distance = 0;

        for ($bit = 0; $bit < 64; $bit++) {
            $distance += ($xor >> $bit) & 1;
        }

        return $distance;
    }
}

if (!function_exists('bigint64_to_unsigned_hex')) {
    /**
     * Representa un BIGINT firmado de PHP como hexadecimal unsigned de 64 bits.
     */
    function bigint64_to_unsigned_hex(int $value): string
    {
        $high = ($value >> 32) & 0xFFFFFFFF;
        $low = $value & 0xFFFFFFFF;

        return sprintf('%08x%08x', $high, $low);
    }
}
