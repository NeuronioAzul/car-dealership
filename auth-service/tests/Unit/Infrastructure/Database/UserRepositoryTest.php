<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Database;

use App\Domain\Entities\User;
use App\Domain\ValueObjects\Address;
use App\Infrastructure\Database\UserRepository;
use DateTime;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Application\DTOs\User\UserDTO;

class UserRepositoryTest extends TestCase
{
    private UserRepository $userRepository;
    /** @var MockObject&PDO */
    private MockObject $pdoMock;
    /** @var MockObject&PDOStatement */
    private MockObject $statementMock;

    protected function setUp(): void
    {
        $this->pdoMock = $this->createMock(PDO::class);
        $this->statementMock = $this->createMock(PDOStatement::class);
        $this->userRepository = new UserRepository($this->pdoMock);
    }

    public function test_save_user_successfully(): void
    {
        $address = new Address(
            'Rua A',
            '123',
            'Centro',
            'São Paulo',
            'SP',
            '01000-000'
        );

        $user = new UserDTO(
            'John Doe',
            'john@example.com',
            'password123',
            '11999999999',
            new DateTime('1990-01-01'),
            $address,
            'customer',
            true,
            true,
            false
        );

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($this->statementMock);

        $this->statementMock->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $result = $this->userRepository->save($user);

        $this->assertTrue($result);
    }

    public function test_save_user_fails(): void
    {
        $address = new Address(
            'Rua A',
            '123',
            'Centro',
            'São Paulo',
            'SP',
            '01000-000'
        );

        $user = new User(
            'John Doe',
            'john@example.com',
            'password123',
            '11999999999',
            new DateTime('1990-01-01'),
            $address
        );

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($this->statementMock);

        $this->statementMock->expects($this->once())
            ->method('execute')
            ->willReturn(false);

        $result = $this->userRepository->save($user);

        $this->assertFalse($result);
    }

    public function test_exists_by_email_returns_true(): void
    {
        $email = 'existing@example.com';

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($this->statementMock);

        $this->statementMock->expects($this->once())
            ->method('execute')
            ->with(['email' => $email]);

        $this->statementMock->expects($this->once())
            ->method('fetchColumn')
            ->willReturn(1);

        $result = $this->userRepository->existsByEmail($email);

        $this->assertTrue($result);
    }

    public function test_exists_by_email_returns_false(): void
    {
        $email = 'nonexistent@example.com';

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($this->statementMock);

        $this->statementMock->expects($this->once())
            ->method('execute')
            ->with(['email' => $email]);

        $this->statementMock->expects($this->once())
            ->method('fetchColumn')
            ->willReturn(0);

        $result = $this->userRepository->existsByEmail($email);

        $this->assertFalse($result);
    }

    public function test_find_by_email_returns_user(): void
    {
        $email = 'user@example.com';
        
        $userData = [
            'id' => 'uuid-here',
            'name' => 'John Doe',
            'email' => $email,
            'password' => 'hashed-password',
            'phone' => '11999999999',
            'birth_date' => '1990-01-01',
            'street' => 'Rua A',
            'number' => '123',
            'neighborhood' => 'Centro',
            'city' => 'São Paulo',
            'state' => 'SP',
            'zip_code' => '01000-000',
            'role' => 'customer',
            'accept_terms' => true,
            'accept_privacy' => true,
            'accept_communications' => false,
            'deleted_at' => null,
            'created_at' => '2023-01-01 00:00:00',
            'updated_at' => '2023-01-01 00:00:00'
        ];

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($this->statementMock);

        $this->statementMock->expects($this->once())
            ->method('execute')
            ->with(['email' => $email]);

        $this->statementMock->expects($this->once())
            ->method('fetch')
            ->willReturn($userData);

        $result = $this->userRepository->findByEmail($email);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($email, $result->getEmail());
        $this->assertEquals('John Doe', $result->getName());
    }

    public function test_find_by_email_returns_null_when_not_found(): void
    {
        $email = 'nonexistent@example.com';

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($this->statementMock);

        $this->statementMock->expects($this->once())
            ->method('execute')
            ->with(['email' => $email]);

        $this->statementMock->expects($this->once())
            ->method('fetch')
            ->willReturn(false);

        $result = $this->userRepository->findByEmail($email);

        $this->assertNull($result);
    }

    public function test_delete_user_successfully(): void
    {
        $userId = 'user-uuid';

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($this->statementMock);

        $this->statementMock->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $result = $this->userRepository->delete($userId);

        $this->assertTrue($result);
    }

    public function test_delete_user_fails(): void
    {
        $userId = 'user-uuid';

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($this->statementMock);

        $this->statementMock->expects($this->once())
            ->method('execute')
            ->willReturn(false);

        $result = $this->userRepository->delete($userId);

        $this->assertFalse($result);
    }

    public function test_find_all_users(): void
    {
        $this->pdoMock->expects($this->once())
            ->method('query')
            ->with('SELECT * FROM users WHERE deleted_at IS NULL ORDER BY created_at DESC')
            ->willReturn($this->statementMock);

        $userData = [
            'id' => 'user-123',
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'hashed_password',
            'phone' => '11999999999',
            'birth_date' => '1990-01-01',
            'street' => 'Rua A',
            'number' => '123',
            'neighborhood' => 'Centro',
            'city' => 'São Paulo',
            'state' => 'SP',
            'zip_code' => '01000-000',
            'role' => 'customer',
            'accept_terms' => 1,
            'accept_privacy' => 1,
            'accept_communications' => 0,
            'created_at' => '2023-01-01 12:00:00',
            'updated_at' => '2023-01-01 12:00:00',
            'deleted_at' => null,
        ];

        $this->statementMock->method('fetch')
            ->willReturnOnConsecutiveCalls($userData, false);

        $users = $this->userRepository->findAll();

        $this->assertIsArray($users);
        $this->assertCount(1, $users);
        $this->assertInstanceOf(User::class, $users[0]);
        $this->assertEquals('John Doe', $users[0]->getName());
        $this->assertEquals('john@example.com', $users[0]->getEmail());
    }

    public function test_find_all_returns_empty_array_when_no_users(): void
    {
        $this->pdoMock->expects($this->once())
            ->method('query')
            ->with('SELECT * FROM users WHERE deleted_at IS NULL ORDER BY created_at DESC')
            ->willReturn($this->statementMock);

        $this->statementMock->method('fetch')
            ->willReturn(false);

        $users = $this->userRepository->findAll();

        $this->assertIsArray($users);
        $this->assertEmpty($users);
    }

    public function test_update_user_successfully(): void
    {
        $address = new Address(
            'Rua B',
            '456',
            'Vila Nova',
            'Rio de Janeiro',
            'RJ',
            '20000-000'
        );

        $user = new User(
            'Jane Doe',
            'jane@example.com',
            'newpassword123',
            '11888888888',
            new DateTime('1985-05-15'),
            $address,
            'admin',
            true,
            true,
            true
        );

        // Set user ID using reflection
        $reflection = new \ReflectionClass($user);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($user, 'user-456');

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($this->statementMock);

        $this->statementMock->expects($this->once())
            ->method('execute')
            ->with($this->callback(function ($params) {
                return isset($params['id']) && 
                       $params['id'] === 'user-456' &&
                       $params['name'] === 'Jane Doe' &&
                       $params['email'] === 'jane@example.com' &&
                       $params['role'] === 'admin';
            }))
            ->willReturn(true);

        $result = $this->userRepository->update($user);

        $this->assertTrue($result);
    }

    public function test_update_user_failure(): void
    {
        $address = new Address(
            'Rua C',
            '789',
            'Centro',
            'Belo Horizonte',
            'MG',
            '30000-000'
        );

        $user = new User(
            'Bob Smith',
            'bob@example.com',
            'password456',
            '11777777777',
            new DateTime('1992-03-20'),
            $address,
            'customer',
            true,
            false,
            true
        );

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($this->statementMock);

        $this->statementMock->expects($this->once())
            ->method('execute')
            ->willReturn(false);

        $result = $this->userRepository->update($user);

        $this->assertFalse($result);
    }

    public function test_constructor_sets_connection(): void
    {
        $repository = new UserRepository($this->pdoMock);
        
        // Use reflection to verify the connection is set
        $reflection = new \ReflectionClass($repository);
        $connectionProperty = $reflection->getProperty('connection');
        $connectionProperty->setAccessible(true);
        
        $this->assertSame($this->pdoMock, $connectionProperty->getValue($repository));
    }
}
