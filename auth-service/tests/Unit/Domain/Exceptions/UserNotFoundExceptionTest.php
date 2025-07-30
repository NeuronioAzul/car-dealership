<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Exceptions;

use App\Domain\Exceptions\UserNotFoundException;
use App\Domain\Exceptions\DomainException;
use PHPUnit\Framework\TestCase;

class UserNotFoundExceptionTest extends TestCase
{
    public function test_constructor_without_identifier(): void
    {
        $exception = new UserNotFoundException();
        
        $this->assertInstanceOf(DomainException::class, $exception);
        $this->assertEquals('User not found', $exception->getMessage());
        $this->assertEquals(404, $exception->getCode());
    }
    
    public function test_constructor_with_identifier(): void
    {
        $identifier = 'user123';
        $exception = new UserNotFoundException($identifier);
        
        $this->assertInstanceOf(DomainException::class, $exception);
        $this->assertEquals("User with identifier 'user123' not found", $exception->getMessage());
        $this->assertEquals(404, $exception->getCode());
    }
    
    public function test_constructor_with_null_identifier(): void
    {
        $exception = new UserNotFoundException(null);
        
        $this->assertInstanceOf(DomainException::class, $exception);
        $this->assertEquals('User not found', $exception->getMessage());
        $this->assertEquals(404, $exception->getCode());
    }
    
    public function test_constructor_with_empty_string_identifier(): void
    {
        $exception = new UserNotFoundException('');
        
        $this->assertInstanceOf(DomainException::class, $exception);
        $this->assertEquals('User not found', $exception->getMessage());
        $this->assertEquals(404, $exception->getCode());
    }
}
