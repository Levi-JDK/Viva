<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/functions/product_validation_queue.php';

final class ControllerTriggerTest extends TestCase
{
    public function testImagePayloadContainsPathAndUrl(): void
    {
        if (!defined('BASE_URL')) {
            define('BASE_URL', 'https://viva.test/');
        }

        $images = viva_product_validation_images(['images/products/prod_1.png']);

        $this->assertSame('images/products/prod_1.png', $images[0]['path']);
        $this->assertSame('https://viva.test/images/products/prod_1.png', $images[0]['url']);
    }

    public function testRedisPushPayloadFormat(): void
    {
        $payload = [
            'product_id' => 1,
            'producer_id' => 2,
            'productData' => [
                'images' => [['path' => 'a.png', 'url' => 'https://viva.test/a.png']],
                'title' => 'Taza',
                'description' => 'Manual',
                'materials' => '',
                'category' => '3',
            ],
        ];

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $decoded = json_decode($json, true);

        $this->assertSame(1, $decoded['product_id']);
        $this->assertSame(2, $decoded['producer_id']);
        $this->assertArrayHasKey('materials', $decoded['productData']);
        $this->assertSame('', $decoded['productData']['materials']);
        $this->assertSame('Taza', $decoded['productData']['title']);
    }
}
