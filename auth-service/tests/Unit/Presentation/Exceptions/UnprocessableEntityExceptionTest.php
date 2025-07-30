<?php

declare(strict_types=1);

namespace Tests\Unit\Presentation\Exceptions;

use App\Presentation\Exceptions\UnprocessableEntityException;
use App\Presentation\Exceptions\HttpException;
use Exception;
use PHPUnit\Framework\TestCase;

class UnprocessableEntityExceptionTest extends TestCase
{
    public function test_get_status_code(): void
    {
        $exception = new UnprocessableEntityException('Unprocessable entity');
        
        $this->assertEquals(422, $exception->getStatusCode());
    }

    public function test_inherits_from_http_exception(): void
    {
        $exception = new UnprocessableEntityException('Unprocessable entity');
        
        $this->assertInstanceOf(HttpException::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);
    }

    public function test_constructor_with_message(): void
    {
        $message = 'Validation failed';
        $exception = new UnprocessableEntityException($message);
        
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals(500, $exception->getCode()); // Default from parent
        $this->assertEquals(422, $exception->getStatusCode());
    }

    public function test_constructor_with_message_and_code(): void
    {
        $message = 'Invalid input data';
        $code = 422;
        $exception = new UnprocessableEntityException($message, $code);
        
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertEquals(422, $exception->getStatusCode());
    }

    public function test_to_array(): void
    {
        $exception = new UnprocessableEntityException('Validation error occurred');
        
        $array = $exception->toArray();
        
        $this->assertIsArray($array);
        $this->assertTrue($array['error']);
        $this->assertEquals('Validation error occurred', $array['message']);
        $this->assertEquals(500, $array['code']);
    }

    public function test_constructor_with_previous_exception(): void
    {
        $previous = new Exception('Validation rule failed');
        $exception = new UnprocessableEntityException('Unprocessable entity', 422, $previous);
        
        $this->assertEquals('Unprocessable entity', $exception->getMessage());
        $this->assertEquals(422, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
        $this->assertEquals(422, $exception->getStatusCode());
    }

    public function test_typical_validation_use_case(): void
    {
        $exception = new UnprocessableEntityException('The given data was invalid');
        
        $this->assertEquals('The given data was invalid', $exception->getMessage());
        $this->assertEquals(422, $exception->getStatusCode());
        
        $array = $exception->toArray();
        $this->assertTrue($array['error']);
        $this->assertEquals('The given data was invalid', $array['message']);
    }
}
