<?php

declare(strict_types=1);

namespace App\Infrastructure\Database;

use App\Domain\Entities\User;
use App\Domain\Repositories\UserRepositoryInterface;
use DateTime;
use PDO;

class UserRepository implements UserRepositoryInterface
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function save(User $user): bool
    {
        $sql = '
            INSERT INTO users (
                id, name, email, password, phone, birth_date,
                role, accept_terms, accept_privacy, accept_communications,
                created_at, updated_at
            ) VALUES (
                :id, :name, :email, :password, :phone, :birth_date,
                :role, :accept_terms, :accept_privacy, :accept_communications,
                :created_at, :updated_at
            )
        ';

        $stmt = $this->connection->prepare($sql);

        return $stmt->execute([
            'id' => $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'password' => $user->getPassword(),
            'phone' => $user->getPhone(),
            'birth_date' => $user->getBirthDate()->format('Y-m-d'),
            'role' => $user->getRole(),
            'accept_terms' => $user->getAcceptTerms(),
            'accept_privacy' => $user->getAcceptPrivacy(),
            'accept_communications' => $user->getAcceptCommunications(),
            'created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $user->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);
    }

    public function findById(string $id): ?User
    {
        $sql = 'SELECT * FROM users WHERE id = :id AND deleted_at IS NULL';
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(['id' => $id]);

        $data = $stmt->fetch();

        return $data ? $this->mapToUser($data) : null;
    }

    public function findByEmail(string $email): ?User
    {
        $sql = 'SELECT * FROM users WHERE email = :email AND deleted_at IS NULL';
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(['email' => $email]);

        $data = $stmt->fetch();

        return $data ? $this->mapToUser($data) : null;
    }

    public function findAll(): array
    {
        $sql = 'SELECT * FROM users WHERE deleted_at IS NULL ORDER BY created_at DESC';
        $stmt = $this->connection->query($sql);

        $users = [];
        while ($data = $stmt->fetch()) {
            $users[] = $this->mapToUser($data);
        }

        return $users;
    }

    public function update(User $user): bool
    {
        $sql = '
            UPDATE users SET
                name = :name,
                email = :email,
                password = :password,
                phone = :phone,
                birth_date = :birth_date,
                zip_code = :zip_code,
                role = :role,
                accept_terms = :accept_terms,
                accept_privacy = :accept_privacy,
                accept_communications = :accept_communications,
                updated_at = :updated_at
            WHERE id = :id
        ';

        $stmt = $this->connection->prepare($sql);

        return $stmt->execute([
            'id' => $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'password' => $user->getPassword(),
            'phone' => $user->getPhone(),
            'birth_date' => $user->getBirthDate()->format('Y-m-d'),
            'role' => $user->getRole(),
            'accept_terms' => $user->getAcceptTerms(),
            'accept_privacy' => $user->getAcceptPrivacy(),
            'accept_communications' => $user->getAcceptCommunications(),
            'updated_at' => $user->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);
    }

    public function delete(string $id): bool
    {
        $sql = 'UPDATE users SET deleted_at = NOW(), updated_at = NOW() WHERE id = :id AND deleted_at IS NULL';
        $stmt = $this->connection->prepare($sql);

        return $stmt->execute(['id' => $id]);
    }

    public function existsByEmail(string $email): bool
    {
        $sql = 'SELECT COUNT(*) FROM users WHERE email = :email AND deleted_at IS NULL';
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(['email' => $email]);

        return $stmt->fetchColumn() > 0;
    }

    private function mapToUser(array $data): User
    {
        $user = new User(
            $data['name'],
            $data['email'],
            '', // Password será definido diretamente
            $data['phone'],
            new DateTime($data['birth_date']),
            $data['role'],
            (bool) $data['accept_terms'],
            (bool) $data['accept_privacy'],
            (bool) $data['accept_communications'],
        );

        // Usar reflection para definir propriedades privadas
        $reflection = new \ReflectionClass($user);

        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($user, $data['id']);

        $passwordProperty = $reflection->getProperty('password');
        $passwordProperty->setAccessible(true);
        $passwordProperty->setValue($user, $data['password']);

        $createdAtProperty = $reflection->getProperty('createdAt');
        $createdAtProperty->setAccessible(true);
        $createdAtProperty->setValue($user, new DateTime($data['created_at']));

        $updatedAtProperty = $reflection->getProperty('updatedAt');
        $updatedAtProperty->setAccessible(true);
        $updatedAtProperty->setValue($user, new DateTime($data['updated_at']));

        if ($data['deleted_at']) {
            $deletedAtProperty = $reflection->getProperty('deletedAt');
            $deletedAtProperty->setAccessible(true);
            $deletedAtProperty->setValue($user, new DateTime($data['deleted_at']));
        }

        return $user;
    }
}
