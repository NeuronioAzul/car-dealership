<?php

declare(strict_types=1);

namespace Tests\Unit\Presentation\Exceptions;

use App\Presentation\Exceptions\InternalServerErrorException;
use App\Presentation\Exceptions\HttpException;
use Exception;
use PHPUnit\Framework\TestCase;

class InternalServerErrorExceptionTest extends TestCase
{
    public function test_get_status_code(): void
    {
        $exception = new InternalServerErrorException('Internal server error');
        
        $this->assertEquals(500, $exception->getStatusCode());
    }

    public function test_inherits_from_http_exception(): void
    {
        $exception = new InternalServerErrorException('Internal server error');
        
        $this->assertInstanceOf(HttpException::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);
    }

    public function test_constructor_with_message(): void
    {
        $message = 'Database connection failed';
        $exception = new InternalServerErrorException($message);
        
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals(500, $exception->getCode()); // Default from parent
        $this->assertEquals(500, $exception->getStatusCode());
    }

    public function test_constructor_with_message_and_code(): void
    {
        $message = 'Service unavailable';
        $code = 503;
        $exception = new InternalServerErrorException($message, $code);
        
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertEquals(500, $exception->getStatusCode()); // Always 500 for this exception
    }

    public function test_to_array(): void
    {
        $exception = new InternalServerErrorException('Server error occurred');
        
        $array = $exception->toArray();
        
        $this->assertIsArray($array);
        $this->assertTrue($array['error']);
        $this->assertEquals('Server error occurred', $array['message']);
        $this->assertEquals(500, $array['code']);
    }

    public function test_constructor_with_previous_exception(): void
    {
        $previous = new Exception('Database error');
        $exception = new InternalServerErrorException('Internal server error', 500, $previous);
        
        $this->assertEquals('Internal server error', $exception->getMessage());
        $this->assertEquals(500, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
        $this->assertEquals(500, $exception->getStatusCode());
    }
}
