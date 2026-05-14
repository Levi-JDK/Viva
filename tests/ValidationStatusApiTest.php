<?php

use PHPUnit\Framework\TestCase;

final class ValidationStatusApiTest extends TestCase
{
    public function testQueryNameExistsForLatestValidationResult(): void
    {
        $queries = require __DIR__ . '/../src/functions/queries.php';

        $this->assertArrayHasKey('ai.fun_val_latest_validation_result', $queries);
        $this->assertStringContainsString('fun_val_latest_validation_result', $queries['ai.fun_val_latest_validation_result']);
        $this->assertStringContainsString(':product_id', $queries['ai.fun_val_latest_validation_result']);
    }

    public function testValidationStatusEndpointFileUsesAuthAndPostOnly(): void
    {
        $source = file_get_contents(__DIR__ . '/../src/api/validation_status.php');

        $this->assertStringContainsString('AuthHelper::protectRoute()', $source);
        $this->assertStringContainsString("!in_array(\$_SERVER['REQUEST_METHOD'], ['GET', 'POST'], true)", $source);
        $this->assertStringContainsString('ai.fun_val_latest_validation_result', $source);
        $this->assertStringContainsString('product_id es requerido', $source);
    }
}
