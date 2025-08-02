<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Exceptions;

use App\Domain\Exceptions\UserNotFoundException;
use PHPUnit\Framework\TestCase;

class UserNotFoundExceptionTest extends TestCase
{
    public function test_constructor_without_identifier(): void
    {
        $exception = new UserNotFoundException();
        $this->assertSame('Invalid credentials provided', $exception->getMessage());
        $this->assertSame(401, $exception->getCode());
    }

    public function test_constructor_with_identifier(): void
    {
        $exception = new UserNotFoundException('user123');
        $this->assertSame('Invalid credentials provided', $exception->getMessage());
        $this->assertSame(401, $exception->getCode());
    }

    public function test_constructor_with_null_identifier(): void
    {
        $exception = new UserNotFoundException(null);
        $this->assertSame('Invalid credentials provided', $exception->getMessage());
        $this->assertSame(401, $exception->getCode());
    }

    public function test_constructor_with_empty_string_identifier(): void
    {
        $exception = new UserNotFoundException('');
        $this->assertSame('Invalid credentials provided', $exception->getMessage());
        $this->assertSame(401, $exception->getCode());
    }
}
