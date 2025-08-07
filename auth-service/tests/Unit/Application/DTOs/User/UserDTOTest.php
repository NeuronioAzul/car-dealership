<?php

declare(strict_types=1);

namespace Tests\Unit\Application\DTOs\User;

use PHPUnit\Framework\TestCase;
use App\Application\DTOs\User\UserDTO;

class UserDTOTest extends TestCase
{
    public function test_user_dto_can_be_created_with_all_parameters(): void
    {

        $userDTO = new UserDTO(
            'uuid-123',
            'João Silva',
            'joao@test.com',
            '11999999999',
            '1990-01-01',
            'customer',
            true,
            true,
            false,
            '2023-01-01 10:00:00',
            '2023-01-01 11:00:00',
            null
        );

        $this->assertEquals('uuid-123', $userDTO->id);
        $this->assertEquals('João Silva', $userDTO->name);
        $this->assertEquals('joao@test.com', $userDTO->email);
        $this->assertEquals('11999999999', $userDTO->phone);
        $this->assertEquals('1990-01-01', $userDTO->birth_date);
        $this->assertEquals('customer', $userDTO->role);
        $this->assertTrue($userDTO->accept_terms);
        $this->assertTrue($userDTO->accept_privacy);
        $this->assertFalse($userDTO->accept_communications);
        $this->assertEquals('2023-01-01 10:00:00', $userDTO->created_at);
        $this->assertEquals('2023-01-01 11:00:00', $userDTO->updated_at);
        $this->assertNull($userDTO->deleted_at);
    }


    public function test_user_dto_properties_are_readonly(): void
    {
        $reflection = new \ReflectionClass(UserDTO::class);
        $properties = $reflection->getProperties();

        foreach ($properties as $property) {
            $this->assertTrue($property->isReadOnly(), "Property {$property->getName()} should be readonly");
            $this->assertTrue($property->isPublic(), "Property {$property->getName()} should be public");
        }
    }

    public function test_user_dto_constructor_parameters(): void
    {
        $reflection = new \ReflectionClass(UserDTO::class);
        $constructor = $reflection->getConstructor();
        $parameters = $constructor->getParameters();

        $this->assertCount(12, $parameters);

        $expectedParameters = [
            'id', 'name', 'email', 'phone', 'birth_date', 'role',
            'accept_terms', 'accept_privacy', 'accept_communications',
            'created_at', 'updated_at', 'deleted_at'
        ];

        foreach ($parameters as $index => $parameter) {
            $this->assertEquals($expectedParameters[$index], $parameter->getName());
        }
    }

    public function test_user_dto_has_correct_namespace(): void
    {
        $reflection = new \ReflectionClass(UserDTO::class);
        $this->assertEquals('App\Application\DTOs\User\UserDTO', $reflection->getName());
        $this->assertEquals('App\Application\DTOs\User', $reflection->getNamespaceName());
        $this->assertEquals('UserDTO', $reflection->getShortName());
    }

    public function test_user_dto_with_different_roles(): void
    {
        // Test customer role
        $customerDTO = new UserDTO(
            'uuid-customer',
            'Cliente Teste',
            'cliente@test.com',
            '11999999999',
            '1990-01-01',
            'customer',
            true,
            true,
            true,
            '2023-01-01 10:00:00',
            '2023-01-01 11:00:00',
            null
        );

        $this->assertEquals('customer', $customerDTO->role);

        // Test admin role
        $adminDTO = new UserDTO(
            'uuid-admin',
            'Admin Teste',
            'admin@test.com',
            '11888888888',
            '1985-01-01',
            'admin',
            true,
            true,
            false,
            '2023-01-01 10:00:00',
            '2023-01-01 11:00:00',
            null
        );

        $this->assertEquals('admin', $adminDTO->role);
    }

    public function test_user_dto_boolean_values(): void
    {
        $userDTO = new UserDTO(
            'uuid-bool-test',
            'Boolean Test',
            'bool@test.com',
            '11777777777',
            '1995-01-01',
            'customer',
            true,
            false,
            false,
            '2023-01-01 10:00:00',
            null,
            null
        );

        $this->assertIsBool($userDTO->accept_terms);
        $this->assertIsBool($userDTO->accept_privacy);
        $this->assertIsBool($userDTO->accept_communications);
        $this->assertTrue($userDTO->accept_terms);
        $this->assertFalse($userDTO->accept_privacy);
        $this->assertFalse($userDTO->accept_communications);
    }

    public function test_user_dto_string_values(): void
    {
        $userDTO = new UserDTO(
            'uuid-string-test',
            'String Test',
            'string@test.com',
            '11666666666',
            '2000-12-31',
            'customer',
            true,
            true,
            true,
            '2023-12-31 23:59:59',
            '2024-01-01 00:00:00',
            null
        );

        $this->assertIsString($userDTO->id);
        $this->assertIsString($userDTO->name);
        $this->assertIsString($userDTO->email);
        $this->assertIsString($userDTO->phone);
        $this->assertIsString($userDTO->birth_date);
        $this->assertIsString($userDTO->role);
        $this->assertIsString($userDTO->created_at);
        $this->assertIsString($userDTO->updated_at);
    }
}
