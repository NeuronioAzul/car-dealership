<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Exceptions;

use App\Domain\Exceptions\InvalidCredentialsException;
use App\Domain\Exceptions\DomainException;
use PHPUnit\Framework\TestCase;

class InvalidCredentialsExceptionTest extends TestCase
{
    public function test_constructor(): void
    {
        $exception = new InvalidCredentialsException();
        
        $this->assertInstanceOf(DomainException::class, $exception);
        $this->assertEquals('Invalid credentials provided', $exception->getMessage());
        $this->assertEquals(401, $exception->getCode());
    }

    public function test_exception_inheritance(): void
    {
        $exception = new InvalidCredentialsException();
        
        $this->assertInstanceOf(DomainException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
    }

    public function test_message_is_fixed(): void
    {
        $exception1 = new InvalidCredentialsException();
        $exception2 = new InvalidCredentialsException();
        
        $this->assertEquals($exception1->getMessage(), $exception2->getMessage());
        $this->assertEquals('Invalid credentials provided', $exception1->getMessage());
    }

    public function test_code_is_fixed(): void
    {
        $exception1 = new InvalidCredentialsException();
        $exception2 = new InvalidCredentialsException();
        
        $this->assertEquals($exception1->getCode(), $exception2->getCode());
        $this->assertEquals(401, $exception1->getCode());
    }

    public function test_no_previous_exception(): void
    {
        $exception = new InvalidCredentialsException();
        
        $this->assertNull($exception->getPrevious());
    }
}
