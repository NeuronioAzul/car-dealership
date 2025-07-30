<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Exceptions;

use App\Application\Exceptions\UserCreationFailedException;
use App\Application\Exceptions\ApplicationException;
use Exception;
use PHPUnit\Framework\TestCase;

class UserCreationFailedExceptionTest extends TestCase
{
    public function test_constructor_with_default_message(): void
    {
        $exception = new UserCreationFailedException();
        
        $this->assertInstanceOf(ApplicationException::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertEquals('Failed to create user', $exception->getMessage());
        $this->assertEquals(500, $exception->getCode());
    }

    public function test_constructor_with_custom_reason(): void
    {
        $reason = 'Database connection error while creating user';
        $exception = new UserCreationFailedException($reason);
        
        $this->assertInstanceOf(ApplicationException::class, $exception);
        $this->assertEquals($reason, $exception->getMessage());
        $this->assertEquals(500, $exception->getCode());
    }

    public function test_constructor_with_empty_reason(): void
    {
        $exception = new UserCreationFailedException('');
        
        $this->assertEquals('', $exception->getMessage());
        $this->assertEquals(500, $exception->getCode());
    }

    public function test_constructor_with_specific_database_error(): void
    {
        $reason = 'Unique constraint violation: email already exists';
        $exception = new UserCreationFailedException($reason);
        
        $this->assertEquals($reason, $exception->getMessage());
        $this->assertEquals(500, $exception->getCode());
    }

    public function test_constructor_with_validation_error(): void
    {
        $reason = 'Invalid user data provided during creation';
        $exception = new UserCreationFailedException($reason);
        
        $this->assertEquals($reason, $exception->getMessage());
        $this->assertEquals(500, $exception->getCode());
    }
}
