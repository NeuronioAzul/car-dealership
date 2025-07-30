<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Entities;

use App\Domain\Entities\User;
use App\Domain\ValueObjects\Address;
use DateTime;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class UserTest extends TestCase
{
    private Address $validAddress;
    private DateTime $validBirthDate;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->validAddress = new Address(
            'Rua das Flores',
            '123',
            'Centro',
            'São Paulo',
            'SP',
            '01234-567'
        );
        
        $this->validBirthDate = new DateTime('1990-01-01');
    }

    public function testUserCanBeCreatedWithValidData(): void
    {
        $user = new User(
            'João Silva',
            'joao@email.com',
            'password123',
            '11999999999',
            $this->validBirthDate,
            $this->validAddress
        );

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('João Silva', $user->getName());
        $this->assertEquals('joao@email.com', $user->getEmail());
        $this->assertEquals('11999999999', $user->getPhone());
        $this->assertEquals($this->validBirthDate, $user->getBirthDate());
        $this->assertEquals($this->validAddress, $user->getAddress());
        $this->assertEquals('customer', $user->getRole());
        $this->assertFalse($user->getAcceptTerms());
        $this->assertFalse($user->getAcceptPrivacy());
        $this->assertFalse($user->getAcceptCommunications());
    }

    public function testUserIdIsGeneratedAsUuid(): void
    {
        $user = new User(
            'João Silva',
            'joao@email.com',
            'password123',
            '11999999999',
            $this->validBirthDate,
            $this->validAddress
        );

        $this->assertNotEmpty($user->getId());
        $this->assertTrue(Uuid::isValid($user->getId()));
    }

    public function testPasswordIsHashedOnCreation(): void
    {
        $plainPassword = 'password123';
        
        $user = new User(
            'João Silva',
            'joao@email.com',
            $plainPassword,
            '11999999999',
            $this->validBirthDate,
            $this->validAddress
        );

        $this->assertNotEquals($plainPassword, $user->getPassword());
        $this->assertTrue(password_verify($plainPassword, $user->getPassword()));
    }

    public function testUserCanBeCreatedWithCustomRole(): void
    {
        $user = new User(
            'Admin User',
            'admin@email.com',
            'password123',
            '11999999999',
            $this->validBirthDate,
            $this->validAddress,
            'admin'
        );

        $this->assertEquals('admin', $user->getRole());
    }

    public function testUserCanBeCreatedWithAcceptanceFlags(): void
    {
        $user = new User(
            'João Silva',
            'joao@email.com',
            'password123',
            '11999999999',
            $this->validBirthDate,
            $this->validAddress,
            'customer',
            true,  // acceptTerms
            true,  // acceptPrivacy
            false  // acceptCommunications
        );

        $this->assertTrue($user->getAcceptTerms());
        $this->assertTrue($user->getAcceptPrivacy());
        $this->assertFalse($user->getAcceptCommunications());
    }

    public function testUserHasTimestamps(): void
    {
        $beforeCreation = new DateTime();
        
        $user = new User(
            'João Silva',
            'joao@email.com',
            'password123',
            '11999999999',
            $this->validBirthDate,
            $this->validAddress
        );
        
        $afterCreation = new DateTime();

        $this->assertInstanceOf(DateTime::class, $user->getCreatedAt());
        $this->assertInstanceOf(DateTime::class, $user->getUpdatedAt());
        $this->assertNull($user->getDeletedAt());
        
        // Verificar se o timestamp está em um intervalo razoável
        $this->assertGreaterThanOrEqual($beforeCreation->getTimestamp(), $user->getCreatedAt()->getTimestamp());
        $this->assertLessThanOrEqual($afterCreation->getTimestamp(), $user->getCreatedAt()->getTimestamp());
    }

    public function testUserCanBeUpdated(): void
    {
        $user = new User(
            'João Silva',
            'joao@email.com',
            'password123',
            '11999999999',
            $this->validBirthDate,
            $this->validAddress
        );

        $originalUpdatedAt = $user->getUpdatedAt();
        
        // Simular uma pequena pausa para garantir diferença no timestamp
        sleep(1);
        
        $user->setName('João Santos');
        
        $this->assertEquals('João Santos', $user->getName());
        $this->assertGreaterThan($originalUpdatedAt->getTimestamp(), $user->getUpdatedAt()->getTimestamp());
    }

    public function testUserCanBeMarkedAsDeleted(): void
    {
        $user = new User(
            'João Silva',
            'joao@email.com',
            'password123',
            '11999999999',
            $this->validBirthDate,
            $this->validAddress
        );

        $this->assertNull($user->getDeletedAt());
        $this->assertFalse($user->isDeleted());

        $user->delete();

        $this->assertInstanceOf(DateTime::class, $user->getDeletedAt());
        $this->assertTrue($user->isDeleted());
    }

    public function testUserVerifyPassword(): void
    {
        $plainPassword = 'password123';
        
        $user = new User(
            'João Silva',
            'joao@email.com',
            $plainPassword,
            '11999999999',
            $this->validBirthDate,
            $this->validAddress
        );

        $this->assertTrue($user->verifyPassword($plainPassword));
        $this->assertFalse($user->verifyPassword('wrong_password'));
    }

    public function testUserRoleMethods(): void
    {
        $customer = new User(
            'João Silva',
            'joao@email.com',
            'password123',
            '11999999999',
            $this->validBirthDate,
            $this->validAddress,
            'customer'
        );

        $admin = new User(
            'Admin User',
            'admin@email.com',
            'password123',
            '11999999999',
            $this->validBirthDate,
            $this->validAddress,
            'admin'
        );

        $this->assertTrue($customer->isCustomer());
        $this->assertFalse($customer->isAdmin());
        
        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isCustomer());
    }

    public function testUserToArray(): void
    {
        $user = new User(
            'João Silva',
            'joao@email.com',
            'password123',
            '11999999999',
            $this->validBirthDate,
            $this->validAddress,
            'customer',
            true,
            true,
            false
        );

        $array = $user->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('João Silva', $array['name']);
        $this->assertEquals('joao@email.com', $array['email']);
        $this->assertEquals('11999999999', $array['phone']);
        $this->assertEquals('1990-01-01', $array['birth_date']);
        $this->assertEquals('customer', $array['role']);
        $this->assertTrue($array['accept_terms']);
        $this->assertTrue($array['accept_privacy']);
        $this->assertFalse($array['accept_communications']);
        $this->assertArrayHasKey('address', $array);
        $this->assertArrayHasKey('created_at', $array);
        $this->assertArrayHasKey('updated_at', $array);
        $this->assertNull($array['deleted_at']);
    }

    public function testTwoUsersHaveDifferentIds(): void
    {
        $user1 = new User(
            'João Silva',
            'joao1@email.com',
            'password123',
            '11999999999',
            $this->validBirthDate,
            $this->validAddress
        );

        $user2 = new User(
            'Maria Silva',
            'maria@email.com',
            'password123',
            '11888888888',
            $this->validBirthDate,
            $this->validAddress
        );

        $this->assertNotEquals($user1->getId(), $user2->getId());
    }
}
