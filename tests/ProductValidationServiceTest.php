<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ProductValidationServiceTest extends TestCase
{
    private mixed $databaseBackup;
    private array $envBackup = [];
    private array $tempFiles = [];

    protected function setUp(): void
    {
        if (!function_exists('imagecreatetruecolor')) {
            $this->markTestSkipped('GD no está disponible para generar imágenes de prueba.');
        }
        $property = new ReflectionProperty(Database::class, 'instance');
        $property->setAccessible(true);
        $this->databaseBackup = $property->getValue();
        $this->envBackup = $_ENV;
        $_ENV['OPENROUTER_API_KEY'] = 'key';
        $_ENV['NVIDIA_API_KEY'] = 'key';
        $_ENV['AI_PRIMARY_PROVIDER'] = 'openrouter';
        $_ENV['AI_SECONDARY_PROVIDER'] = 'nvidia';
        $_ENV['AI_EMBEDDING_MODEL'] = 'embedding-model';
        $_ENV['AI_DECISION_MODEL'] = 'decision-model';
    }

    protected function tearDown(): void
    {
        $this->setDatabaseInstance($this->databaseBackup);
        $this->setRouterClient(null);
        $_ENV = $this->envBackup;
        foreach ($this->tempFiles as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    public function testHashMatchDifferentProducerShortCircuitsToHumanReview(): void
    {
        $image = $this->createImage();
        $db = $this->databaseMock();
        $db->expects($this->exactly(2))->method('ejecutar')->willReturnCallback(function (string $query, array $params = []) {
            if ($query === 'ai.fun_val_unified_hash_search') {
                TestCase::assertMatchesRegularExpression('/^[01]{64}$/', $params[':phash']);
                TestCase::assertMatchesRegularExpression('/^[01]{64}$/', $params[':dhash']);
                TestCase::assertSame(7, $params[':exclude_image_id']);
                TestCase::assertArrayNotHasKey(':phash_int', $params);
                TestCase::assertArrayNotHasKey(':dhash_int', $params);
            }

            return match ($query) {
                'ai.fun_val_unified_hash_search' => new PVStatementStub(fetchAll: [[
                    'id_imagen' => 5,
                    'id_producto' => 77,
                    'id_productor' => 99,
                    'url_imagen' => '/otra.jpg',
                    'detection_method' => 'hash_exacto',
                    'score' => 1.0,
                ]]),
                'ai.fun_c_validation_result' => new PVStatementStub(fetchColumn: 10),
                default => throw new RuntimeException('Query inesperada: ' . $query),
            };
        });
        $this->setDatabaseInstance($db);

        $result = ProductValidationService::validate(1, 44, [
            'images' => [['id_imagen' => 7, 'path' => $image]],
            'title' => 'Taza',
            'description' => 'Taza hecha a mano',
            'materials' => 'Arcilla',
            'category' => 'Cerámica',
        ]);

        $this->assertSame('revision_humana', $result['decision']);
        $this->assertSame('posible', $result['plagio_visual']['status']);
        $this->assertSame('hash_exacto', $result['plagio_visual']['detection_method']);
        $this->assertSame(77, $result['plagio_visual']['matched_product_id']);
    }

    public function testNoHashMatchCompletesFullFlowAndApprovesWithoutDecisionModel(): void
    {
        $image = $this->createImage();
        $this->setRouterClient($this->mockClient(array_fill(0, 7, new Response(200, [], $this->embeddingBody([1, 0])))));
        $db = $this->databaseMock();
        $db->expects($this->atLeast(1))->method('ejecutar')->willReturnCallback(function (string $query, array $params = []) {
            return match ($query) {
                'ai.fun_val_check_examples_count' => new PVStatementStub(fetchColumn: 20),
                'unifiedHashSearch', 'ai.fun_val_unified_hash_search', 'ai.fun_val_similar_by_vector_exclude', 'ai.fun_val_search_similar_text_exclude', 'ai.fun_val_rag_rules' => new PVStatementStub(fetchAll: []),
                'updateImageHashes' => new PVStatementStub(),
                'ai.fun_c_visual_embedding' => new PVStatementStub(),
                'ai.fun_val_check_pgvector' => new PVStatementStub(fetchColumn: true),
                'ai.fun_c_text_embedding' => new PVStatementStub(fetchColumn: 40),
                'ai.fun_c_validation_result' => new PVStatementStub(fetchColumn: 50),
                default => throw new RuntimeException('Query inesperada: ' . $query),
            };
        });
        $this->setDatabaseInstance($db);

        $result = ProductValidationService::validate(1, 44, [
            'images' => [['id_imagen' => 7, 'path' => $image]],
            'title' => 'Taza',
            'description' => 'Taza hecha a mano',
            'materials' => 'Arcilla',
            'category' => 'Cerámica',
        ]);

        $this->assertSame('approved', $result['decision']);
        $this->assertSame('alta', $result['coherencia_texto_imagen']['status']);
        $this->assertSame('none', $result['plagio_visual']['status']);
    }

    public function testDecisionGateTriggersForPlagiarismCoherenceAndArtisanDoubt(): void
    {
        $method = new ReflectionMethod(ProductValidationService::class, 'shouldCallDecisionModel');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke(null, ['score' => 0.91], ['status' => 'alta'], ['status' => 'artesanal']));
        $this->assertTrue($method->invoke(null, ['score' => 0.0], ['status' => 'media'], ['status' => 'artesanal']));
        $this->assertTrue($method->invoke(null, ['score' => 0.0], ['status' => 'alta'], ['status' => 'dudosa']));
        $this->assertFalse($method->invoke(null, ['score' => 0.0], ['status' => 'alta'], ['status' => 'artesanal']));
    }

    public function testAllAiFailsReturnsPendingValidation(): void
    {
        $this->setRouterClient($this->mockClient([
            new Response(500, [], '{"error":"down"}'),
            new Response(500, [], '{"error":"down"}'),
        ]));
        $db = $this->databaseMock();
        $db->expects($this->exactly(3))->method('ejecutar')->willReturnCallback(function (string $query, array $params = []) {
            return match ($query) {
                'ai.fun_val_check_examples_count' => new PVStatementStub(fetchColumn: 20),
                'ai.fun_val_check_pgvector' => new PVStatementStub(fetchColumn: true),
                'ai.fun_c_validation_result' => new PVStatementStub(fetchColumn: 88),
                default => throw new RuntimeException('Query inesperada: ' . $query),
            };
        });
        $this->setDatabaseInstance($db);

        $result = ProductValidationService::validate(1, 44, [
            'images' => [],
            'title' => 'Taza',
            'description' => 'Taza hecha a mano',
        ]);

        $this->assertSame('pending_validacion_ia', $result['decision']);
        $this->assertTrue($result['fallback_used']);
    }

    public function testEvidenceIsTrimmedToTopLimits(): void
    {
        $method = new ReflectionMethod(ProductValidationService::class, 'buildEvidence');
        $method->setAccessible(true);

        $evidence = $method->invoke(null, ['title' => 'A'], range(1, 50), range(1, 50), ['status' => 'media'], range(1, 50), ['status' => 'dudosa']);

        $this->assertCount(5, $evidence['hash_results']);
        $this->assertCount(5, $evidence['visual_similarity']);
        $this->assertCount(10, $evidence['rag_rules']);
    }

    private function createImage(): string
    {
        $file = tempnam(sys_get_temp_dir(), 'viva-p3-') . '.png';
        $image = imagecreatetruecolor(8, 8);
        imagepng($image, $file);
        imagedestroy($image);
        $this->tempFiles[] = $file;
        return $file;
    }

    private function setDatabaseInstance(mixed $instance): void
    {
        $property = new ReflectionProperty(Database::class, 'instance');
        $property->setAccessible(true);
        $property->setValue(null, $instance);
    }

    /** @return Database&MockObject */
    private function databaseMock(): Database
    {
        return $this->getMockBuilder(Database::class)->disableOriginalConstructor()->onlyMethods(['ejecutar'])->getMock();
    }

    private function setRouterClient(?Client $client): void
    {
        $property = new ReflectionProperty(AIProviderRouter::class, 'client');
        $property->setAccessible(true);
        $property->setValue(null, $client);
    }

    private function mockClient(array $queue): Client
    {
        return new Client(['handler' => HandlerStack::create(new MockHandler($queue)), 'http_errors' => false]);
    }

    private function embeddingBody(array $embedding): string
    {
        return json_encode(['data' => [['embedding' => $embedding, 'index' => 0]]]);
    }
}

final class PVStatementStub
{
    public function __construct(
        private array $fetchAll = [],
        private mixed $fetch = false,
        private mixed $fetchColumn = false,
    ) {
    }

    public function fetchAll(int $mode = PDO::FETCH_ASSOC): array
    {
        return $this->fetchAll;
    }

    public function fetch(int $mode = PDO::FETCH_ASSOC): mixed
    {
        return $this->fetch;
    }

    public function fetchColumn(int $column = 0): mixed
    {
        return $this->fetchColumn;
    }
}
