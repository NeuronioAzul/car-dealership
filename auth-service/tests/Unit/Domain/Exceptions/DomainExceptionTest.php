<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Exceptions;

use App\Domain\Exceptions\DomainException;
use Exception;
use PHPUnit\Framework\TestCase;

class ConcreteDomainException extends DomainException
{
    // Concrete implementation for testing
}

class DomainExceptionTest extends TestCase
{
    public function test_constructor_with_default_values(): void
    {
        $exception = new ConcreteDomainException();
        
        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertEquals('', $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
    
    public function test_constructor_with_message(): void
    {
        $message = 'Test domain exception message';
        $exception = new ConcreteDomainException($message);
        
        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
    
    public function test_constructor_with_message_and_code(): void
    {
        $message = 'Test domain exception message';
        $code = 500;
        $exception = new ConcreteDomainException($message, $code);
        
        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
    
    public function test_constructor_with_all_parameters(): void
    {
        $message = 'Test domain exception message';
        $code = 500;
        $previous = new Exception('Previous exception');
        $exception = new ConcreteDomainException($message, $code, $previous);
        
        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
    
    public function test_constructor_with_null_previous(): void
    {
        $message = 'Test domain exception message';
        $code = 500;
        $exception = new ConcreteDomainException($message, $code, null);
        
        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
