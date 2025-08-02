<?php

namespace App\Tests\Unit\Infrastructure\Database;

use App\Domain\Entities\User;
use App\Infrastructure\Database\UserRepository;
use DateTime;
use PDO;
use PDOStatement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UserRepositoryTest extends TestCase
{
    private PDO|MockObject $connection;
    private PDOStatement|MockObject $stmt;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(PDO::class);
        $this->stmt = $this->createMock(PDOStatement::class);
        $this->userRepository = new UserRepository($this->connection);
    }

    private function createUser(): User
    {
        return new User(
            'John Doe',
            'john.doe@example.com',
            'password123',
            '11999999999',
            new DateTime('1990-01-01'),
            true,
            true,
            true
        );
    }

    public function test_save_user_successfully(): void
    {
        $user = $this->createUser();
        $this->connection->expects($this->once())->method('prepare')->willReturn($this->stmt);
        $this->stmt->expects($this->once())->method('execute')->with($this->isType('array'))->willReturn(true);
        $result = $this->userRepository->save($user);
        $this->assertTrue($result);
    }

    public function test_save_user_fails(): void
    {
        $user = $this->createUser();
        $this->connection->expects($this->once())->method('prepare')->willReturn($this->stmt);
        $this->stmt->expects($this->once())->method('execute')->willReturn(false);
        $result = $this->userRepository->save($user);
        $this->assertFalse($result);
    }

    public function test_exists_by_email_returns_true(): void
    {
        $this->connection->expects($this->once())->method('prepare')->willReturn($this->stmt);
        $this->stmt->expects($this->once())->method('execute')->with(['email' => 'test@example.com'])->willReturn(true);
        $this->stmt->expects($this->once())->method('fetchColumn')->willReturn(1);
        $this->assertTrue($this->userRepository->existsByEmail('test@example.com'));
    }

    public function test_exists_by_email_returns_false(): void
    {
        $this->connection->expects($this->once())->method('prepare')->willReturn($this->stmt);
        $this->stmt->expects($this->once())->method('execute')->with(['email' => 'test@example.com'])->willReturn(true);
        $this->stmt->expects($this->once())->method('fetchColumn')->willReturn(0);
        $this->assertFalse($this->userRepository->existsByEmail('test@example.com'));
    }

    public function test_find_by_email_returns_user(): void
    {
        $email = 'john@example.com';
        $userData = [
            'id' => 'some-uuid',
            'name' => 'John Doe',
            'email' => $email,
            'password' => 'hashed_password',
            'phone' => '11999999999',
            'birth_date' => '1990-01-01',
            'street' => 'Rua Teste',
            'number' => '123',
            'neighborhood' => 'Bairro Teste',
            'city' => 'Cidade Teste',
            'state' => 'TS',
            'zip_code' => '12345-678',
            'role' => 'customer',
            'accept_terms' => 1,
            'accept_privacy' => 1,
            'accept_communications' => 1,
            'created_at' => '2023-01-01 10:00:00',
            'updated_at' => '2023-01-01 10:00:00',
            'deleted_at' => null,
        ];

        $this->connection->expects($this->once())->method('prepare')->willReturn($this->stmt);
        $this->stmt->expects($this->once())->method('execute')->with(['email' => $email]);
        $this->stmt->expects($this->once())->method('fetch')->willReturn($userData);

        $user = $this->userRepository->findByEmail($email);
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($email, $user->getEmail());
    }

    public function test_find_by_email_returns_null_when_not_found(): void
    {
        $email = 'notfound@example.com';
        $this->connection->expects($this->once())->method('prepare')->willReturn($this->stmt);
        $this->stmt->expects($this->once())->method('execute')->with(['email' => $email]);
        $this->stmt->expects($this->once())->method('fetch')->willReturn(false);

        $user = $this->userRepository->findByEmail($email);
        $this->assertNull($user);
    }

    public function test_delete_user_successfully(): void
    {
        $userId = 'some-uuid';
        $this->connection->expects($this->once())->method('prepare')->willReturn($this->stmt);
        $this->stmt->expects($this->once())->method('execute')->with(['id' => $userId])->willReturn(true);
        $this->assertTrue($this->userRepository->delete($userId));
    }

    public function test_delete_user_fails(): void
    {
        $userId = 'some-uuid';
        $this->connection->expects($this->once())->method('prepare')->willReturn($this->stmt);
        $this->stmt->expects($this->once())->method('execute')->with(['id' => $userId])->willReturn(false);
        $this->assertFalse($this->userRepository->delete($userId));
    }

    public function test_find_all_users(): void
    {
        $userData = [
            'id' => 'some-uuid', 'name' => 'John Doe', 'email' => 'john@example.com', 'password' => 'hashed',
            'phone' => '123', 'birth_date' => '1990-01-01', 'street' => 'a', 'number' => '1',
            'neighborhood' => 'b', 'city' => 'c', 'state' => 'd', 'zip_code' => '123', 'role' => 'customer',
            'accept_terms' => 1, 'accept_privacy' => 1, 'accept_communications' => 1,
            'created_at' => '2023-01-01 10:00:00', 'updated_at' => '2023-01-01 10:00:00', 'deleted_at' => null
        ];
        $this->connection->expects($this->once())->method('query')->willReturn($this->stmt);
        $this->stmt->expects($this->exactly(2))->method('fetch')
            ->willReturnOnConsecutiveCalls($userData, false);

        $users = $this->userRepository->findAll();
        $this->assertCount(1, $users);
        $this->assertInstanceOf(User::class, $users[0]);
    }

    public function test_find_all_returns_empty_array_when_no_users(): void
    {
        $this->connection->expects($this->once())->method('query')->willReturn($this->stmt);
        $this->stmt->expects($this->once())->method('fetch')->willReturn(false);
        $users = $this->userRepository->findAll();
        $this->assertCount(0, $users);
    }

    public function test_update_user_successfully(): void
    {
        $user = $this->createUser();
        $this->connection->expects($this->once())->method('prepare')->willReturn($this->stmt);
        $this->stmt->expects($this->once())->method('execute')->with($this->callback(function ($params) use ($user) {
            return $params['id'] === $user->getId() && $params['name'] === 'John Doe';
        }))->willReturn(true);
        $this->assertTrue($this->userRepository->update($user));
    }

    public function test_update_user_failure(): void
    {
        $user = $this->createUser();
        $this->connection->expects($this->once())->method('prepare')->willReturn($this->stmt);
        $this->stmt->expects($this->once())->method('execute')->willReturn(false);
        $this->assertFalse($this->userRepository->update($user));
    }

    public function test_constructor_sets_connection(): void
    {
        $this->assertInstanceOf(UserRepository::class, $this->userRepository);
    }
}
