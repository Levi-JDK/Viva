<?php

use PHPUnit\Framework\TestCase;

final class WorkerValidationTest extends TestCase
{
    private array $envBackup = [];

    protected function setUp(): void
    {
        $this->envBackup = $_ENV;
        foreach (['AI_EMBEDDING_MODEL', 'AI_DECISION_MODEL', 'AI_PRIMARY_PROVIDER', 'AI_SECONDARY_PROVIDER', 'AI_PHASH_THRESHOLD', 'AI_DHASH_THRESHOLD', 'AI_VISUAL_SIMILARITY_THRESHOLD', 'AI_TEXT_IMAGE_THRESHOLD'] as $varName) {
            $_ENV[$varName] = 'test';
        }
        ProductValidationJob::setValidatorForTest(null);
    }

    protected function tearDown(): void
    {
        $_ENV = $this->envBackup;
        ProductValidationJob::setValidatorForTest(null);
    }

    public function testProcesarValidacionDispatchesJob(): void
    {
        $called = false;
        ProductValidationJob::setValidatorForTest(static function () use (&$called): array {
            $called = true;
            return ['decision' => 'approved'];
        });

        $this->invokeProcesarValidacion($this->payload());

        $this->assertTrue($called);
    }

    public function testProcesarValidacionDoesNotRetryAiProviderException(): void
    {
        $attempts = 0;
        ProductValidationJob::setValidatorForTest(static function () use (&$attempts): array {
            $attempts++;
            throw new AIProviderException('providers down', 'nvidia', 503);
        });

        $redis = $this->invokeProcesarValidacion($this->payload());

        $this->assertSame(1, $attempts);
        $this->assertSame([], $redis->pushed);
    }

    public function testProcesarValidacionMovesToDlqAfterMaxRetries(): void
    {
        $attempts = 0;
        ProductValidationJob::setValidatorForTest(static function () use (&$attempts): array {
            $attempts++;
            throw new RuntimeException('db down');
        });

        $redis = $this->invokeProcesarValidacion($this->payload());

        $this->assertSame(3, $attempts);
        $this->assertCount(1, $redis->pushed);
        $this->assertSame('viva:cola:deadletter', $redis->pushed[0][0]);
        $dlqPayload = json_decode($redis->pushed[0][1], true);
        $this->assertSame('validacion', $dlqPayload['tipo']);
        $this->assertSame(10, $dlqPayload['id']);
    }

    private function invokeProcesarValidacion(array $payload): WorkerRedisStub
    {
        $worker = (new ReflectionClass(ValidationWorker::class))->newInstanceWithoutConstructor();
        $redis = new WorkerRedisStub();

        foreach ([['redis', $redis], ['backoff', [0, 0, 0, 0]]] as [$propertyName, $value]) {
            $property = new ReflectionProperty(ValidationWorker::class, $propertyName);
            $property->setAccessible(true);
            $property->setValue($worker, $value);
        }

        $method = new ReflectionMethod(ValidationWorker::class, 'procesarValidacion');
        $method->setAccessible(true);
        $method->invoke($worker, json_encode($payload));

        return $redis;
    }

    private function payload(): array
    {
        return [
            'product_id' => 10,
            'producer_id' => 20,
            'productData' => ['images' => [], 'title' => 'Taza', 'description' => 'Manual', 'materials' => '', 'category' => '1'],
        ];
    }
}

final class WorkerRedisStub extends Predis\Client
{
    public array $pushed = [];

    public function __construct() {}

    public function lpush($key, array|string $value): mixed
    {
        $this->pushed[] = [$key, $value];
        return 1;
    }
}
