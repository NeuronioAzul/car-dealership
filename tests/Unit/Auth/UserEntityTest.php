<?php

namespace Tests\Unit\Auth;

use PHPUnit\Framework\TestCase;
use DateTime;

// Incluir classes do Auth Service
require_once __DIR__ . '/../../../auth-service/src/Domain/Entities/User.php';
require_once __DIR__ . '/../../../auth-service/src/Domain/ValueObjects/Address.php';
require_once __DIR__ . '/../../../auth-service/src/Application/Services/JWTService.php';

use App\Domain\Entities\User;
use App\Domain\ValueObjects\Address;
use App\Application\Services\JWTService;

class UserEntityTest extends TestCase
{
    private User $user;
    private Address $address;

    protected function setUp(): void
    {
        $this->address = new Address(
            'Rua Teste',
            '123',
            'Centro',
            'São Paulo',
            'SP',
            '01234-567'
        );

        $this->user = new User(
            'João Silva',
            'joao@teste.com',
            'senha123',
            '12345678901',
            (new DateTime('01/01/2000')),
            $this->address,
            'customer',
            true,
            true,
            true
        );
    }

    public function testUserCreation(): void
    {
        $this->assertInstanceOf(User::class, $this->user);
        $this->assertEquals('João Silva', $this->user->getName());
        $this->assertEquals('joao@teste.com', $this->user->getEmail());
        $this->assertEquals('11999999999', $this->user->getPhone());
        $this->assertEquals('customer', $this->user->getRole());
        $this->assertFalse($this->user->isDeleted());
    }

    public function testUserPasswordHashing(): void
    {
        $plainPassword = 'senha123';
        $this->user->setPassword($plainPassword);
        
        // Verificar se a senha foi hasheada
        $this->assertNotEquals($plainPassword, $this->user->getPassword());
        $this->assertTrue(password_verify($plainPassword, $this->user->getPassword()));
    }

    public function testUserValidation(): void
    {
        // Testar email válido
        $this->assertTrue(filter_var('teste@email.com', FILTER_VALIDATE_EMAIL) !== false);
        $this->assertFalse(filter_var('email-invalido', FILTER_VALIDATE_EMAIL) !== false);
        // Testar telefone válido (apenas dígitos, 11 caracteres)
        $this->assertTrue(preg_match('/^\d{11}$/', '11999999999') === 1);
        $this->assertFalse(preg_match('/^\d{11}$/', '123') === 1);
    }

    public function testUserSoftDelete(): void
    {
        $this->assertFalse($this->user->isDeleted());
        
        $this->user->softDelete();
        
        $this->assertTrue($this->user->isDeleted());
        $this->assertNotNull($this->user->getDeletedAt());
    }

    public function testUserToArray(): void
    {
        $userArray = $this->user->toArray();
        
        $this->assertIsArray($userArray);
        $this->assertArrayHasKey('id', $userArray);
        $this->assertArrayHasKey('name', $userArray);
        $this->assertArrayHasKey('email', $userArray);
        $this->assertArrayHasKey('cpf', $userArray);
        $this->assertArrayHasKey('phone', $userArray);
        $this->assertArrayHasKey('role', $userArray);
        $this->assertArrayHasKey('address', $userArray);
        $this->assertArrayHasKey('created_at', $userArray);
        
        // Verificar se a senha não está no array
        $this->assertArrayNotHasKey('password', $userArray);
    }

    public function testAddressValueObject(): void
    {
        $address = $this->user->getAddress();
        
        $this->assertInstanceOf(Address::class, $address);
        $this->assertEquals('Rua Teste, 123', $address->getStreet());
        $this->assertEquals('São Paulo', $address->getCity());
        $this->assertEquals('SP', $address->getState());
        $this->assertEquals('01234-567', $address->getZipCode());
    }

    public function testUserRoleValidation(): void
    {
        // Testar roles válidos
        $this->assertTrue($this->user->isValidRole('customer'));
        $this->assertTrue($this->user->isValidRole('admin'));
        
        // Testar role inválido
        $this->assertFalse($this->user->isValidRole('invalid_role'));
    }

    public function testUserUpdate(): void
    {
        $newName = 'João Silva Santos';
        $newPhone = '11888888888';
        
        $this->user->setName($newName);
        $this->user->setPhone($newPhone);
        
        $this->assertEquals($newName, $this->user->getName());
        $this->assertEquals($newPhone, $this->user->getPhone());
        $this->assertNotNull($this->user->getUpdatedAt());
    }
}

