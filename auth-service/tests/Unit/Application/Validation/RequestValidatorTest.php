<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Validation;

use App\Application\Exceptions\ValidationException;
use App\Application\Validation\RequestValidator;
use PHPUnit\Framework\TestCase;

class RequestValidatorTest extends TestCase
{
    private RequestValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new RequestValidator();
    }

    public function testValidRegisterDataPassesValidation(): void
    {
        $validData = [
            'name' => 'João Silva',
            'email' => 'joao@email.com',
            'password' => 'password123',
            'phone' => '11999999999',
            'birth_date' => '1990-01-01',
            'address' => [
                'street' => 'Rua das Flores',
                'number' => '123',
                'neighborhood' => 'Centro',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01234-567'
            ],
            'role' => 'customer',
            'accept_terms' => true,
            'accept_privacy' => true,
            'accept_communications' => false
        ];

        // Should not throw exception
        $this->validator->validate($validData, $this->validator->getRegisterUserConstraints());
        $this->assertTrue(true); // If we reach here, validation passed
    }

    public function testRegisterValidationFailsWithMissingName(): void
    {
        $invalidData = [
            'email' => 'joao@email.com',
            'password' => 'password123',
            'phone' => '11999999999',
            'birth_date' => '1990-01-01',
            'address' => [
                'street' => 'Rua das Flores',
                'number' => '123',
                'neighborhood' => 'Centro',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01234-567'
            ],
            'role' => 'customer',
            'accept_terms' => true,
            'accept_privacy' => true,
            'accept_communications' => false
        ];

        $this->expectException(ValidationException::class);
        $this->validator->validate($invalidData, $this->validator->getRegisterUserConstraints());
    }

    public function testRegisterValidationFailsWithInvalidEmail(): void
    {
        $invalidData = [
            'name' => 'João Silva',
            'email' => 'invalid-email',
            'password' => 'password123',
            'phone' => '11999999999',
            'birth_date' => '1990-01-01',
            'address' => [
                'street' => 'Rua das Flores',
                'number' => '123',
                'neighborhood' => 'Centro',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01234-567'
            ],
            'role' => 'customer',
            'accept_terms' => true,
            'accept_privacy' => true,
            'accept_communications' => false
        ];

        $this->expectException(ValidationException::class);
        $this->validator->validate($invalidData, $this->validator->getRegisterUserConstraints());
    }

    public function testRegisterValidationFailsWithShortPassword(): void
    {
        $invalidData = [
            'name' => 'João Silva',
            'email' => 'joao@email.com',
            'password' => '123',
            'phone' => '11999999999',
            'birth_date' => '1990-01-01',
            'address' => [
                'street' => 'Rua das Flores',
                'number' => '123',
                'neighborhood' => 'Centro',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01234-567'
            ],
            'role' => 'customer',
            'accept_terms' => true,
            'accept_privacy' => true,
            'accept_communications' => false
        ];

        $this->expectException(ValidationException::class);
        $this->validator->validate($invalidData, $this->validator->getRegisterUserConstraints());
    }

    public function testRegisterValidationFailsWithInvalidPhone(): void
    {
        $invalidData = [
            'name' => 'João Silva',
            'email' => 'joao@email.com',
            'password' => 'password123',
            'phone' => '123',
            'birth_date' => '1990-01-01',
            'address' => [
                'street' => 'Rua das Flores',
                'number' => '123',
                'neighborhood' => 'Centro',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01234-567'
            ],
            'role' => 'customer',
            'accept_terms' => true,
            'accept_privacy' => true,
            'accept_communications' => false
        ];

        $this->expectException(ValidationException::class);
        $this->validator->validate($invalidData, $this->validator->getRegisterUserConstraints());
    }

    public function testRegisterValidationFailsWithIncompleteAddress(): void
    {
        $invalidData = [
            'name' => 'João Silva',
            'email' => 'joao@email.com',
            'password' => 'password123',
            'phone' => '11999999999',
            'birth_date' => '1990-01-01',
            'address' => [
                'street' => 'Rua das Flores',
                'number' => '123',
                // missing neighborhood, city, state, zip_code
            ],
            'role' => 'customer',
            'accept_terms' => true,
            'accept_privacy' => true,
            'accept_communications' => false
        ];

        $this->expectException(ValidationException::class);
        $this->validator->validate($invalidData, $this->validator->getRegisterUserConstraints());
    }

    public function testRegisterValidationFailsWithoutAcceptingTerms(): void
    {
        $invalidData = [
            'name' => 'João Silva',
            'email' => 'joao@email.com',
            'password' => 'password123',
            'phone' => '11999999999',
            'birth_date' => '1990-01-01',
            'address' => [
                'street' => 'Rua das Flores',
                'number' => '123',
                'neighborhood' => 'Centro',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01234-567'
            ],
            'role' => 'customer',
            'accept_terms' => false,
            'accept_privacy' => true,
            'accept_communications' => false
        ];

        $this->expectException(ValidationException::class);
        $this->validator->validate($invalidData, $this->validator->getRegisterUserConstraints());
    }

    public function testValidLoginDataPassesValidation(): void
    {
        $validData = [
            'email' => 'joao@email.com',
            'password' => 'password123'
        ];

        // Should not throw exception
        $this->validator->validate($validData, $this->validator->getLoginConstraints());
        $this->assertTrue(true); // If we reach here, validation passed
    }

    public function testLoginValidationFailsWithInvalidEmail(): void
    {
        $invalidData = [
            'email' => 'invalid-email',
            'password' => 'password123'
        ];

        $this->expectException(ValidationException::class);
        $this->validator->validate($invalidData, $this->validator->getLoginConstraints());
    }

    public function testLoginValidationFailsWithMissingPassword(): void
    {
        $invalidData = [
            'email' => 'joao@email.com'
        ];

        $this->expectException(ValidationException::class);
        $this->validator->validate($invalidData, $this->validator->getLoginConstraints());
    }
}
