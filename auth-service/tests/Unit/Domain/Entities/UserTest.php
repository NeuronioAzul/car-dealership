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

    public function test_get_accept_privacy(): void
    {
        $user = new User(
            'Test User',
            'test@email.com',
            'password123',
            '11999999999',
            $this->validBirthDate,
            $this->validAddress,
            'customer',
            true,
            true, // acceptPrivacy
            false
        );

        $this->assertTrue($user->getAcceptPrivacy());
    }

    public function test_get_accept_communications(): void
    {
        $user = new User(
            'Test User',
            'test@email.com',
            'password123',
            '11999999999',
            $this->validBirthDate,
            $this->validAddress,
            'customer',
            true,
            true,
            true // acceptCommunications
        );

        $this->assertTrue($user->getAcceptCommunications());
    }

    public function test_get_created_at(): void
    {
        $beforeCreation = new DateTime();
        
        $user = new User(
            'Test User',
            'test@email.com',
            'password123',
            '11999999999',
            $this->validBirthDate,
            $this->validAddress
        );

        $afterCreation = new DateTime();

        $this->assertInstanceOf(DateTime::class, $user->getCreatedAt());
        $this->assertGreaterThanOrEqual($beforeCreation->getTimestamp(), $user->getCreatedAt()->getTimestamp());
        $this->assertLessThanOrEqual($afterCreation->getTimestamp(), $user->getCreatedAt()->getTimestamp());
    }

    public function test_get_updated_at(): void
    {
        $beforeCreation = new DateTime();
        
        $user = new User(
            'Test User',
            'test@email.com',
            'password123',
            '11999999999',
            $this->validBirthDate,
            $this->validAddress
        );

        $afterCreation = new DateTime();

        $this->assertInstanceOf(DateTime::class, $user->getUpdatedAt());
        $this->assertGreaterThanOrEqual($beforeCreation->getTimestamp(), $user->getUpdatedAt()->getTimestamp());
        $this->assertLessThanOrEqual($afterCreation->getTimestamp(), $user->getUpdatedAt()->getTimestamp());
    }

    public function test_get_deleted_at_initially_null(): void
    {
        $user = new User(
            'Test User',
            'test@email.com',
            'password123',
            '11999999999',
            $this->validBirthDate,
            $this->validAddress
        );

        $this->assertNull($user->getDeletedAt());
    }

    public function test_set_name(): void
    {
        $user = new User(
            'Old Name',
            'test@email.com',
            'password123',
            '11999999999',
            $this->validBirthDate,
            $this->validAddress
        );

        $newName = 'New Name';
        $user->setName($newName);

        $this->assertEquals($newName, $user->getName());
    }

    public function test_set_email(): void
    {
        $user = new User(
            'Test User',
            'old@email.com',
            'password123',
            '11999999999',
            $this->validBirthDate,
            $this->validAddress
        );

        $newEmail = 'new@email.com';
        $user->setEmail($newEmail);

        $this->assertEquals($newEmail, $user->getEmail());
    }

    public function test_set_phone(): void
    {
        $user = new User(
            'Test User',
            'test@email.com',
            'password123',
            '11999999999',
            $this->validBirthDate,
            $this->validAddress
        );

        $newPhone = '11888888888';
        $user->setPhone($newPhone);

        $this->assertEquals($newPhone, $user->getPhone());
    }

    public function test_set_address(): void
    {
        $user = new User(
            'Test User',
            'test@email.com',
            'password123',
            '11999999999',
            $this->validBirthDate,
            $this->validAddress
        );

        $newAddress = new Address(
            'Nova Rua',
            '456',
            'Novo Bairro',
            'Rio de Janeiro',
            'RJ',
            '20000-000'
        );

        $user->setAddress($newAddress);

        $this->assertEquals($newAddress, $user->getAddress());
    }

    public function test_set_role(): void
    {
        $user = new User(
            'Test User',
            'test@email.com',
            'password123',
            '11999999999',
            $this->validBirthDate,
            $this->validAddress,
            'customer'
        );

        $user->setRole('admin');

        $this->assertEquals('admin', $user->getRole());
    }

    public function test_update_acceptance_flags(): void
    {
        $user = new User(
            'Test User',
            'test@email.com',
            'password123',
            '11999999999',
            $this->validBirthDate,
            $this->validAddress,
            'customer',
            false,
            false,
            false
        );

        $user->setAcceptTerms(true);
        $user->setAcceptPrivacy(true);
        $user->setAcceptCommunications(true);

        $this->assertTrue($user->getAcceptTerms());
        $this->assertTrue($user->getAcceptPrivacy());
        $this->assertTrue($user->getAcceptCommunications());
    }

    public function test_change_password(): void
    {
        $user = new User(
            'Test User',
            'test@email.com',
            'oldpassword',
            '11999999999',
            $this->validBirthDate,
            $this->validAddress
        );

        $oldPasswordHash = $user->getPassword();
        $user->setPassword('newpassword');
        $newPasswordHash = $user->getPassword();

        $this->assertNotEquals($oldPasswordHash, $newPasswordHash);
        $this->assertTrue($user->verifyPassword('newpassword'));
        $this->assertFalse($user->verifyPassword('oldpassword'));
    }
}
