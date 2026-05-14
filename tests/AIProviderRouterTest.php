<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class AIProviderRouterTest extends TestCase
{
    private array $envBackup = [];

    protected function setUp(): void
    {
        $this->envBackup = $_ENV;
        $_ENV['OPENROUTER_API_KEY'] = 'openrouter-test-key';
        $_ENV['NVIDIA_API_KEY'] = 'nvidia-test-key';
        $_ENV['AI_PRIMARY_PROVIDER'] = 'openrouter';
        $_ENV['AI_SECONDARY_PROVIDER'] = 'nvidia';
        $_ENV['AI_EMBEDDING_MODEL'] = 'test-embedding-model';
        $_ENV['AI_DECISION_MODEL'] = 'test-decision-model';
        $_ENV['AI_PROVIDER_TIMEOUT'] = '12';
    }

    protected function tearDown(): void
    {
        $_ENV = $this->envBackup;
        $this->setRouterClient(null);
    }

    public function testOpenRouterIsCalledFirstAndReturnsEmbedding(): void
    {
        $history = [];
        $this->setRouterClient($this->mockClient([
            new Response(200, [], $this->embeddingBody([0.1, 0.2, 0.3])),
        ], $history));

        $result = AIProviderRouter::generateTextEmbedding('texto artesanal');

        $this->assertSame([0.1, 0.2, 0.3], $result['embedding']);
        $this->assertSame('openrouter', $result['provider']);
        $this->assertSame('test-embedding-model', $result['model']);
        $this->assertCount(1, $history);
        $this->assertSame('https://openrouter.ai/api/v1/embeddings', (string) $history[0]['request']->getUri());
    }

    public function testNvidiaIsCalledWhenOpenRouterFails(): void
    {
        $history = [];
        $this->setRouterClient($this->mockClient([
            new Response(500, [], '{"error":"fail"}'),
            new Response(200, [], $this->embeddingBody([0.4, 0.5])),
        ], $history));

        $result = AIProviderRouter::generateTextEmbedding('texto artesanal');

        $this->assertSame('nvidia', $result['provider']);
        $this->assertSame([0.4, 0.5], $result['embedding']);
        $this->assertCount(2, $history);
        $this->assertSame('https://openrouter.ai/api/v1/embeddings', (string) $history[0]['request']->getUri());
        $this->assertSame('https://integrate.api.nvidia.com/v1/chat/completions', (string) $history[1]['request']->getUri());
    }

    public function testAIProviderExceptionIsThrownWhenBothProvidersFail(): void
    {
        $history = [];
        $this->setRouterClient($this->mockClient([
            new Response(500, [], '{"error":"openrouter down"}'),
            new Response(429, [], '{"error":"rate limit"}'),
        ], $history));

        try {
            AIProviderRouter::generateTextEmbedding('texto artesanal');
            $this->fail('Expected AIProviderException was not thrown.');
        } catch (AIProviderException $exception) {
            $this->assertSame('nvidia', $exception->getProvider());
            $this->assertSame(429, $exception->getHttpStatus());
            $this->assertStringContainsString('Respuesta HTTP inválida', $exception->getMessage());
        }
    }

    public function testNetworkFailureFallsBackToNvidia(): void
    {
        $history = [];
        $request = new Request('POST', 'https://openrouter.ai/api/v1/embeddings');
        $this->setRouterClient($this->mockClient([
            new ConnectException('timeout', $request),
            new Response(200, [], $this->embeddingBody([0.9])),
        ], $history));

        $result = AIProviderRouter::generateImageEmbedding('https://example.com/image.jpg');

        $this->assertSame('nvidia', $result['provider']);
        $this->assertSame([0.9], $result['embedding']);
    }

    public function testEmbeddingExtractionFromResponse(): void
    {
        $method = new ReflectionMethod(AIProviderRouter::class, 'extractEmbeddingFromResponse');
        $method->setAccessible(true);

        $embedding = $method->invoke(null, [
            'data' => [['embedding' => [1, 2.5, '3.7'], 'index' => 0]],
        ]);

        $this->assertSame([1.0, 2.5, 3.7], $embedding);
    }

    public function testInvalidEmbeddingExtractionThrows(): void
    {
        $method = new ReflectionMethod(AIProviderRouter::class, 'extractEmbeddingFromResponse');
        $method->setAccessible(true);

        $this->expectException(AIProviderException::class);

        $method->invoke(null, ['choices' => [['message' => ['content' => '{"bad":true}']]]]);
    }

    public function testTimeoutConfigurationIsSentToGuzzle(): void
    {
        $_ENV['AI_PROVIDER_TIMEOUT'] = '7';
        $history = [];
        $this->setRouterClient($this->mockClient([
            new Response(200, [], $this->embeddingBody([0.1])),
        ], $history));

        AIProviderRouter::generateTextEmbedding('texto artesanal');

        $this->assertSame(7.0, $history[0]['options']['timeout']);
        $this->assertSame(7.0, $history[0]['options']['connect_timeout']);
    }

    public function testImagePayloadUsesMultimodalContent(): void
    {
        $history = [];
        $this->setRouterClient($this->mockClient([
            new Response(200, [], $this->embeddingBody([0.1])),
        ], $history));

        AIProviderRouter::generateImageEmbedding('https://example.com/image.jpg');

        $payload = json_decode((string) $history[0]['request']->getBody(), true);
        $this->assertSame('test-embedding-model', $payload['model']);
        $this->assertSame('float', $payload['encoding_format']);
        $this->assertIsArray($payload['input']);
        $this->assertArrayHasKey('content', $payload['input'][0]);
    }

    public function testCallChatUsesDecisionModelAndReturnsContent(): void
    {
        $history = [];
        $this->setRouterClient($this->mockClient([
            new Response(200, [], $this->chatBody('{"decision":"approved"}')),
        ], $history));

        $result = AIProviderRouter::callChat([
            ['role' => 'user', 'content' => 'validá'],
        ]);

        $payload = json_decode((string) $history[0]['request']->getBody(), true);
        $this->assertSame('test-decision-model', $payload['model']);
        $this->assertSame(0.1, $payload['temperature']);
        $this->assertSame(1000, $payload['max_tokens']);
        $this->assertSame('{"decision":"approved"}', $result['content']);
        $this->assertSame('openrouter', $result['provider']);
    }

    public function testCallDecisionModelBuildsMessagesAndParsesJson(): void
    {
        $history = [];
        $this->setRouterClient($this->mockClient([
            new Response(200, [], $this->chatBody('```json {"decision":"revision_humana","motivo_general":"duda"} ```')),
        ], $history));

        $result = AIProviderRouter::callDecisionModel('sistema', 'usuario');
        $payload = json_decode((string) $history[0]['request']->getBody(), true);

        $this->assertSame('system', $payload['messages'][0]['role']);
        $this->assertSame('sistema', $payload['messages'][0]['content']);
        $this->assertSame('user', $payload['messages'][1]['role']);
        $this->assertSame('usuario', $payload['messages'][1]['content']);
        $this->assertSame('revision_humana', $result['parsed']['decision']);
        $this->assertSame('duda', $result['parsed']['motivo_general']);
    }

    private function mockClient(array $queue, array &$history): Client
    {
        $mock = new MockHandler($queue);
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($history));

        return new Client(['handler' => $stack, 'http_errors' => false]);
    }

    private function setRouterClient(?Client $client): void
    {
        $property = new ReflectionProperty(AIProviderRouter::class, 'client');
        $property->setAccessible(true);
        $property->setValue(null, $client);
    }

    private function embeddingBody(array $embedding): string
    {
        return json_encode([
            'data' => [['embedding' => $embedding, 'index' => 0]],
            'model' => 'test-embedding-model',
        ]);
    }

    private function chatBody(string $content): string
    {
        return json_encode([
            'choices' => [[
                'message' => ['content' => $content],
            ]],
        ]);
    }
}
