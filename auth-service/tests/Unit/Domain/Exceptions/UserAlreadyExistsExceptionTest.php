<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Exceptions;

use App\Domain\Exceptions\UserAlreadyExistsException;
use App\Domain\Exceptions\DomainException;
use PHPUnit\Framework\TestCase;

class UserAlreadyExistsExceptionTest extends TestCase
{
    public function test_constructor_with_email(): void
    {
        $email = 'test@example.com';
        $exception = new UserAlreadyExistsException($email);
        
        $this->assertInstanceOf(DomainException::class, $exception);
        $this->assertEquals("User with email 'test@example.com' already exists", $exception->getMessage());
        $this->assertEquals(409, $exception->getCode());
    }

    public function test_constructor_with_different_email(): void
    {
        $email = 'another@example.com';
        $exception = new UserAlreadyExistsException($email);
        
        $this->assertEquals("User with email 'another@example.com' already exists", $exception->getMessage());
        $this->assertEquals(409, $exception->getCode());
    }

    public function test_constructor_with_empty_email(): void
    {
        $email = '';
        $exception = new UserAlreadyExistsException($email);
        
        $this->assertEquals("User with email '' already exists", $exception->getMessage());
        $this->assertEquals(409, $exception->getCode());
    }

    public function test_constructor_with_special_characters_in_email(): void
    {
        $email = 'user+test@example.com';
        $exception = new UserAlreadyExistsException($email);
        
        $this->assertEquals("User with email 'user+test@example.com' already exists", $exception->getMessage());
        $this->assertEquals(409, $exception->getCode());
    }

    public function test_exception_inheritance(): void
    {
        $exception = new UserAlreadyExistsException('test@example.com');
        
        $this->assertInstanceOf(DomainException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
    }

    public function test_code_is_conflict(): void
    {
        $exception = new UserAlreadyExistsException('test@example.com');
        
        $this->assertEquals(409, $exception->getCode());
    }

    public function test_no_previous_exception(): void
    {
        $exception = new UserAlreadyExistsException('test@example.com');
        
        $this->assertNull($exception->getPrevious());
    }
}
