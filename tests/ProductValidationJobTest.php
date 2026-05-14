<?php

use PHPUnit\Framework\TestCase;

final class ProductValidationJobTest extends TestCase
{
    private array $envBackup = [];

    protected function setUp(): void
    {
        $this->envBackup = $_ENV;
        $this->setRequiredEnv();
        ProductValidationJob::setValidatorForTest(null);
    }

    protected function tearDown(): void
    {
        $_ENV = $this->envBackup;
        ProductValidationJob::setValidatorForTest(null);
    }

    public function testFromRedisValidPayloadHydratesJob(): void
    {
        $job = ProductValidationJob::fromRedis($this->payload());

        $this->assertInstanceOf(ProductValidationJob::class, $job);
    }

    public function testFromRedisMissingProductIdThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $payload = $this->payload();
        unset($payload['product_id']);

        ProductValidationJob::fromRedis($payload);
    }

    public function testFromRedisMissingProducerIdThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $payload = $this->payload();
        unset($payload['producer_id']);

        ProductValidationJob::fromRedis($payload);
    }

    public function testHandleCallsProductValidationServiceWithCorrectArgs(): void
    {
        $called = false;
        ProductValidationJob::setValidatorForTest(function (int $productId, int $producerId, array $images, array $productData) use (&$called): array {
            $called = true;
            $this->assertSame(10, $productId);
            $this->assertSame(20, $producerId);
            $this->assertSame([['path' => '/tmp/a.png', 'url' => 'https://example.test/a.png']], $images);
            $this->assertSame('Taza', $productData['title']);
            return ['decision' => 'approved'];
        });

        ProductValidationJob::fromRedis($this->payload())->handle();

        $this->assertTrue($called);
    }

    public function testHandleThrowsRuntimeExceptionWhenEnvVarMissing(): void
    {
        unset($_ENV['AI_EMBEDDING_MODEL']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('AI_EMBEDDING_MODEL');

        ProductValidationJob::fromRedis($this->payload())->handle();
    }

    public function testHandlePropagatesAiProviderExceptionToWorker(): void
    {
        ProductValidationJob::setValidatorForTest(static function (): array {
            throw new AIProviderException('providers down', 'nvidia', 503);
        });

        $this->expectException(AIProviderException::class);
        $this->expectExceptionMessage('providers down');

        ProductValidationJob::fromRedis($this->payload())->handle();
    }

    public function testHandleRethrowsInfrastructureException(): void
    {
        ProductValidationJob::setValidatorForTest(static function (): array {
            throw new RuntimeException('db down');
        });

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('db down');

        ProductValidationJob::fromRedis($this->payload())->handle();
    }

    private function payload(): array
    {
        return [
            'product_id' => 10,
            'producer_id' => 20,
            'productData' => [
                'images' => [['path' => '/tmp/a.png', 'url' => 'https://example.test/a.png']],
                'title' => 'Taza',
                'description' => 'Hecha a mano',
                'materials' => 'Arcilla',
                'category' => 'Cerámica',
            ],
        ];
    }

    private function setRequiredEnv(): void
    {
        foreach ([
            'AI_EMBEDDING_MODEL',
            'AI_DECISION_MODEL',
            'AI_PRIMARY_PROVIDER',
            'AI_SECONDARY_PROVIDER',
        ] as $varName) {
            $_ENV[$varName] = 'test';
        }
    }
}
