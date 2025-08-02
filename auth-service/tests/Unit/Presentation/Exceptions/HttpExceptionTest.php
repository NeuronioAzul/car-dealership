<?php

declare(strict_types=1);

namespace Tests\Unit\Presentation\Exceptions;

use App\Presentation\Exceptions\HttpException;
use Exception;
use PHPUnit\Framework\TestCase;

class ConcreteHttpException extends HttpException
{
    public function getStatusCode(): int
    {
        return 418; // I'm a teapot
    }
}

class HttpExceptionTest extends TestCase
{
    public function test_constructor_with_default_values(): void
    {
        $exception = new ConcreteHttpException('Test message');
        
        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertEquals('Test message', $exception->getMessage());
        $this->assertEquals(500, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function test_constructor_with_all_parameters(): void
    {
        $previous = new Exception('Previous exception');
        $exception = new ConcreteHttpException('Test message', 400, $previous);
        
        $this->assertEquals('Test message', $exception->getMessage());
        $this->assertEquals(400, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function test_get_status_code(): void
    {
        $exception = new ConcreteHttpException('Test message');
        
        $this->assertEquals(418, $exception->getStatusCode());
    }

    public function test_to_array(): void
    {
        $exception = new ConcreteHttpException('Test message', 400);
        
        $array = $exception->toArray();
        
        $this->assertIsArray($array);
        $this->assertArrayHasKey('error', $array);
        $this->assertArrayHasKey('message', $array);
        $this->assertArrayHasKey('code', $array);
        
        $this->assertTrue($array['error']);
        $this->assertEquals('Test message', $array['message']);
        $this->assertEquals(418, $array['code']);
    }

    public function test_to_array_with_different_message(): void
    {
        $exception = new ConcreteHttpException('Another error message', 422);
        
        $array = $exception->toArray();
        
        $this->assertTrue($array['error']);
        $this->assertEquals('Another error message', $array['message']);
        $this->assertEquals(418, $array['code']);
    }
}
