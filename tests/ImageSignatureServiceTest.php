<?php

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ImageSignatureServiceTest extends TestCase
{
    private mixed $databaseBackup;

    protected function setUp(): void
    {
        $property = new ReflectionProperty(Database::class, 'instance');
        $property->setAccessible(true);
        $this->databaseBackup = $property->getValue();
    }

    protected function tearDown(): void
    {
        $this->setDatabaseInstance($this->databaseBackup);
    }

    public function testSaveImageHashesUpdatesTabImagenes(): void
    {
        $db = $this->databaseMock();
        $db->expects($this->once())
            ->method('ejecutar')
            ->with('updateImageHashes', [
                ':id_producto' => 123,
                ':id_imagen' => 9,
                ':file_hash' => str_repeat('a', 64),
                ':phash' => str_repeat('0', 64),
                ':dhash' => str_repeat('1', 64),
            ])
            ->willReturn(new StatementStub());
        $this->setDatabaseInstance($db);

        ImageSignatureService::saveImageHashes(123, 9, str_repeat('a', 64), str_repeat('0', 64), str_repeat('1', 64));

        $this->addToAssertionCount(1);
    }

    public function testFindHashMatchesUnifiedPassesBitStrings(): void
    {
        $rows = [['id_imagen' => 1, 'detection_method' => 'hash_perceptual']];
        $db = $this->databaseMock();
        $db->expects($this->once())
            ->method('ejecutar')
            ->with('ai.fun_val_unified_hash_search', [
                ':file_hash' => str_repeat('b', 64),
                ':phash' => str_repeat('0', 64),
                ':dhash' => str_repeat('1', 64),
                ':exclude_product_id' => 123,
                ':exclude_image_id' => 9,
                ':phash_threshold' => (int) ($_ENV['AI_PHASH_THRESHOLD'] ?? 10),
                ':dhash_threshold' => (int) ($_ENV['AI_DHASH_THRESHOLD'] ?? 10),
                ':limit' => 20,
            ])
            ->willReturn(new StatementStub(fetchAll: $rows));
        $this->setDatabaseInstance($db);

        $this->assertSame(
            $rows,
            ImageSignatureService::findHashMatchesUnified(str_repeat('b', 64), str_repeat('0', 64), str_repeat('1', 64), 123, 9)
        );
    }

    public function testFindSimilarByVectorThrowsWhenPgvectorIsUnavailable(): void
    {
        $db = $this->databaseMock();
        $db->expects($this->once())
            ->method('ejecutar')
            ->with('ai.fun_val_check_pgvector')
            ->willReturn(new StatementStub(fetchColumn: false));
        $this->setDatabaseInstance($db);

        $this->expectException(RuntimeException::class);

        ImageSignatureService::findSimilarByVector([0.1, 0.2]);
    }

    public function testFindSimilarByVectorReturnsRows(): void
    {
        $rows = [['id_imagen' => 1, 'similarity' => '0.95']];
        $db = $this->databaseMock();
        $db->expects($this->exactly(2))
            ->method('ejecutar')
            ->willReturnCallback(function (string $query, array $params = []) use ($rows) {
                if ($query === 'ai.fun_val_check_pgvector') {
                    return new StatementStub(fetchColumn: true);
                }

                TestCase::assertSame('ai.fun_val_similar_by_vector', $query);
                TestCase::assertSame('[0.1,0.2]', $params[':embedding']);
                TestCase::assertSame(0.9, $params[':threshold']);
                TestCase::assertSame(5, $params[':limit']);

                return new StatementStub(fetchAll: $rows);
            });
        $this->setDatabaseInstance($db);

        $this->assertSame($rows, ImageSignatureService::findSimilarByVector([0.1, 0.2], 0.9, 5));
    }

    public function testFindSimilarByVectorExcludingProducerUsesProducerId(): void
    {
        $db = $this->databaseMock();
        $db->expects($this->exactly(2))
            ->method('ejecutar')
            ->willReturnCallback(function (string $query, array $params = []) {
                if ($query === 'ai.fun_val_check_pgvector') {
                    return new StatementStub(fetchColumn: 't');
                }

                TestCase::assertSame('ai.fun_val_similar_by_vector_exclude', $query);
                TestCase::assertSame(44, $params[':producer_id']);

                return new StatementStub(fetchAll: [['id_imagen' => 9]]);
            });
        $this->setDatabaseInstance($db);

        $rows = ImageSignatureService::findSimilarByVectorExcludingProducer([0.3], 44);

        $this->assertSame([['id_imagen' => 9]], $rows);
    }

    public function testSaveVisualEmbeddingCallsExpectedQuery(): void
    {
        $db = $this->databaseMock();
        $db->expects($this->once())
            ->method('ejecutar')
            ->with('ai.fun_c_visual_embedding', [
                ':id_producto' => 123,
                ':id_imagen' => 5,
                ':visual_embedding' => '[1,2.5]',
                ':embedding_model' => 'model-x',
            ])
            ->willReturn(new StatementStub());
        $this->setDatabaseInstance($db);

        ImageSignatureService::saveVisualEmbedding(123, 5, [1, 2.5], 'model-x');

        $this->addToAssertionCount(1);
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
        return $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['ejecutar'])
            ->getMock();
    }
}

final class StatementStub
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
