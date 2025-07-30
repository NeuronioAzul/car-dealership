<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Exceptions;

use App\Application\Exceptions\ApplicationException;
use Exception;
use PHPUnit\Framework\TestCase;

class ConcreteApplicationException extends ApplicationException
{
    // Concrete implementation for testing
}

class ApplicationExceptionTest extends TestCase
{
    public function test_constructor_with_default_values(): void
    {
        $exception = new ConcreteApplicationException();
        
        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertEquals('', $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function test_constructor_with_message(): void
    {
        $message = 'Test application exception message';
        $exception = new ConcreteApplicationException($message);
        
        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function test_constructor_with_message_and_code(): void
    {
        $message = 'Test application exception message';
        $code = 1001;
        $exception = new ConcreteApplicationException($message, $code);
        
        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function test_constructor_with_all_parameters(): void
    {
        $message = 'Test application exception message';
        $code = 1001;
        $previous = new Exception('Previous exception');
        $exception = new ConcreteApplicationException($message, $code, $previous);
        
        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function test_constructor_with_null_previous(): void
    {
        $message = 'Test application exception message';
        $code = 1001;
        $exception = new ConcreteApplicationException($message, $code, null);
        
        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
