<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Exceptions;

use Tests\TestCase;
use App\Application\Exceptions\ValidationException;

class ValidationExceptionTest extends TestCase
{
    public function testConstructorWithDefaultMessage(): void
    {
        $errors = ['email' => 'Email is required', 'password' => 'Password too weak'];
        $exception = new ValidationException($errors);

        $this->assertEquals('Validation failed', $exception->getMessage());
        $this->assertEquals(422, $exception->getCode());
        $this->assertEquals($errors, $exception->getErrors());
    }

    public function testConstructorWithCustomMessage(): void
    {
        $errors = ['name' => 'Name is required'];
        $customMessage = 'Custom validation error';
        $exception = new ValidationException($errors, $customMessage);

        $this->assertEquals($customMessage, $exception->getMessage());
        $this->assertEquals(422, $exception->getCode());
        $this->assertEquals($errors, $exception->getErrors());
    }

    public function testGetErrors(): void
    {
        $errors = [
            'email' => 'Invalid email format',
            'phone' => 'Phone number is required',
        ];
        $exception = new ValidationException($errors);

        $retrievedErrors = $exception->getErrors();

        $this->assertEquals($errors, $retrievedErrors);
        $this->assertIsArray($retrievedErrors);
        $this->assertCount(3, $retrievedErrors);
    }

    public function testWithEmptyErrors(): void
    {
        $errors = [];
        $exception = new ValidationException($errors);

        $this->assertEquals([], $exception->getErrors());
        $this->assertEquals('Validation failed', $exception->getMessage());
        $this->assertEquals(422, $exception->getCode());
    }
}
