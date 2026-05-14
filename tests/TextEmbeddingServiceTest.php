<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class TextEmbeddingServiceTest extends TestCase
{
    private mixed $databaseBackup;
    private array $envBackup = [];

    protected function setUp(): void
    {
        $property = new ReflectionProperty(Database::class, 'instance');
        $property->setAccessible(true);
        $this->databaseBackup = $property->getValue();
        $this->envBackup = $_ENV;
        $_ENV['OPENROUTER_API_KEY'] = 'key';
        $_ENV['NVIDIA_API_KEY'] = 'key';
        $_ENV['AI_PRIMARY_PROVIDER'] = 'openrouter';
        $_ENV['AI_SECONDARY_PROVIDER'] = 'nvidia';
        $_ENV['AI_EMBEDDING_MODEL'] = 'embedding-model';
    }

    protected function tearDown(): void
    {
        $this->setDatabaseInstance($this->databaseBackup);
        $this->setRouterClient(null);
        $_ENV = $this->envBackup;
    }

    public function testEmbedTextGeneratesAndStoresEmbedding(): void
    {
        $this->setRouterClient($this->mockClient([new Response(200, [], $this->embeddingBody([1, 0]))]));
        $db = $this->databaseMock();
        $db->expects($this->exactly(2))->method('ejecutar')->willReturnCallback(function (string $query, array $params = []) {
            if ($query === 'ai.fun_val_check_pgvector') {
                return new P3StatementStub(fetchColumn: true);
            }
            TestCase::assertSame('ai.fun_c_text_embedding', $query);
            TestCase::assertSame('product_title', $params[':source_type']);
            TestCase::assertSame(hash('sha256', 'Título artesanal'), $params[':content_hash']);
            TestCase::assertSame(2, $params[':embedding_dimension']);
            TestCase::assertStringStartsWith('[1,0,0,0', $params[':text_embedding']);
            return new P3StatementStub(fetchColumn: 9);
        });
        $this->setDatabaseInstance($db);

        $result = TextEmbeddingService::embedText('Título artesanal', 'product_title');

        $this->assertSame(9, $result['id']);
        $this->assertSame([1.0, 0.0], $result['embedding']);
        $this->assertSame(hash('sha256', 'Título artesanal'), $result['content_hash']);
    }

    public function testEmbedTextThrowsOnBlankText(): void
    {
        $this->expectException(InvalidArgumentException::class);
        TextEmbeddingService::embedText('   ', 'product_title');
    }

    public function testEmbedAndSaveProductDataEmbedsKnownFields(): void
    {
        $this->setRouterClient($this->mockClient([
            new Response(200, [], $this->embeddingBody([1])),
            new Response(200, [], $this->embeddingBody([0.9])),
            new Response(200, [], $this->embeddingBody([0.8])),
            new Response(200, [], $this->embeddingBody([0.7])),
        ]));
        $db = $this->databaseMock();
        $db->expects($this->exactly(8))->method('ejecutar')->willReturnCallback(function (string $query, array $params = []) {
            if ($query === 'ai.fun_val_check_pgvector') {
                return new P3StatementStub(fetchColumn: 't');
            }
            TestCase::assertSame('ai.fun_c_text_embedding', $query);
            TestCase::assertSame(12, $params[':product_id']);
            TestCase::assertSame(44, $params[':producer_id']);
            return new P3StatementStub(fetchColumn: 100);
        });
        $this->setDatabaseInstance($db);

        $rows = TextEmbeddingService::embedAndSaveProductData(12, [
            'producer_id' => 44,
            'title' => 'A',
            'description' => 'B',
            'materials' => 'C',
            'category' => 'D',
        ]);

        $this->assertSame(['product_title', 'product_description', 'product_materials', 'product_category'], array_keys($rows));
    }

    public function testSearchSimilarTextUsesSourceTypeFilter(): void
    {
        $this->setRouterClient($this->mockClient([new Response(200, [], $this->embeddingBody([0.5]))]));
        $expected = [['id' => 1, 'similarity' => '0.91']];
        $db = $this->databaseMock();
        $db->expects($this->exactly(2))->method('ejecutar')->willReturnCallback(function (string $query, array $params = []) use ($expected) {
            if ($query === 'ai.fun_val_check_pgvector') {
                return new P3StatementStub(fetchColumn: 1);
            }
            TestCase::assertSame('ai.fun_val_search_similar_text', $query);
            TestCase::assertSame('artisan_policy', $params[':source_type']);
            TestCase::assertSame(3, $params[':limit']);
            return new P3StatementStub(fetchAll: $expected);
        });
        $this->setDatabaseInstance($db);

        $this->assertSame($expected, TextEmbeddingService::searchSimilarText('regla', 'artisan_policy', 3));
    }

    public function testSearchSimilarTextExcludingProducerUsesProducerId(): void
    {
        $this->setRouterClient($this->mockClient([new Response(200, [], $this->embeddingBody([0.5]))]));
        $db = $this->databaseMock();
        $db->expects($this->exactly(2))->method('ejecutar')->willReturnCallback(function (string $query, array $params = []) {
            if ($query === 'ai.fun_val_check_pgvector') {
                return new P3StatementStub(fetchColumn: true);
            }
            TestCase::assertSame('ai.fun_val_search_similar_text_exclude', $query);
            TestCase::assertSame(7, $params[':producer_id']);
            return new P3StatementStub(fetchAll: [['id' => 2]]);
        });
        $this->setDatabaseInstance($db);

        $this->assertSame([['id' => 2]], TextEmbeddingService::searchSimilarTextExcludingProducer('texto', 7));
    }

    public function testSaveRagRuleAndGetRagRulesByType(): void
    {
        $this->setRouterClient($this->mockClient([new Response(200, [], $this->embeddingBody([0.1]))]));
        $db = $this->databaseMock();
        $db->expects($this->exactly(3))->method('ejecutar')->willReturnCallback(function (string $query, array $params = []) {
            if ($query === 'ai.fun_val_check_pgvector') {
                return new P3StatementStub(fetchColumn: true);
            }
            if ($query === 'ai.fun_c_text_embedding') {
                TestCase::assertNull($params[':product_id']);
                TestCase::assertSame('plagiarism_policy', $params[':source_type']);
                return new P3StatementStub(fetchColumn: 55);
            }
            TestCase::assertSame('ai.fun_val_rag_rules', $query);
            return new P3StatementStub(fetchAll: [['id' => 55]]);
        });
        $this->setDatabaseInstance($db);

        $this->assertSame(55, TextEmbeddingService::saveRagRule('No plagiar', 'plagiarism_policy'));
        $this->assertSame([['id' => 55]], TextEmbeddingService::getRagRulesByType('plagiarism_policy'));
    }

    public function testComputeTextImageCoherenceThresholdsAndEmptyImages(): void
    {
        $this->assertSame('no_evaluada', TextEmbeddingService::computeTextImageCoherenceEmbedding([1], [])['status']);
        $this->assertSame('alta', TextEmbeddingService::computeTextImageCoherenceEmbedding([1, 0], [[1, 0]])['status']);
        $this->assertSame('media', TextEmbeddingService::computeTextImageCoherenceEmbedding([1, 0], [[0.6, 0.8]])['status']);
        $this->assertSame('baja', TextEmbeddingService::computeTextImageCoherenceEmbedding([1, 0], [[0, 1]])['status']);
    }

    public function testPgvectorUnavailableThrowsRuntimeException(): void
    {
        $db = $this->databaseMock();
        $db->expects($this->once())->method('ejecutar')->with('ai.fun_val_check_pgvector')->willReturn(new P3StatementStub(fetchColumn: false));
        $this->setDatabaseInstance($db);

        $this->expectException(RuntimeException::class);
        TextEmbeddingService::embedText('texto', 'product_title');
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

    private function mockClient(array $queue, ?array &$history = null): Client
    {
        $history ??= [];
        $mock = new MockHandler($queue);
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($history));
        return new Client(['handler' => $stack, 'http_errors' => false]);
    }

    private function embeddingBody(array $embedding): string
    {
        return json_encode(['data' => [['embedding' => $embedding, 'index' => 0]]]);
    }
}

final class P3StatementStub
{
    public function __construct(private array $fetchAll = [], private mixed $fetch = false, private mixed $fetchColumn = false) {}
    public function fetchAll(int $mode = PDO::FETCH_ASSOC): array { return $this->fetchAll; }
    public function fetch(int $mode = PDO::FETCH_ASSOC): mixed { return $this->fetch; }
    public function fetchColumn(int $column = 0): mixed { return $this->fetchColumn; }
}
