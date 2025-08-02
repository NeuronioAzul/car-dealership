<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use DateTime;
use Ramsey\Uuid\Uuid;

class User
{
    private string $id;
    private string $name;
    private string $email;
    private string $password;
    private string $phone;
    private DateTime $birthDate;
    private string $role; // 'customer' or 'admin'
    private bool $acceptTerms;
    private bool $acceptPrivacy;
    private bool $acceptCommunications;
    private DateTime $createdAt;
    private DateTime $updatedAt;
    private ?DateTime $deletedAt;

    public function __construct(
        string $name,
        string $email,
        string $password,
        string $phone,
        DateTime $birthDate,
        string $role = 'customer',
        bool $acceptTerms = false,
        bool $acceptPrivacy = false,
        bool $acceptCommunications = false
    ) {
        $this->id = Uuid::uuid6()->toString();
        $this->name = $name;
        $this->email = $email;
        $this->password = password_hash($password, PASSWORD_ARGON2ID);
        $this->phone = $phone;
        $this->birthDate = $birthDate;
        $this->role = $role;
        $this->acceptTerms = $acceptTerms;
        $this->acceptPrivacy = $acceptPrivacy;
        $this->acceptCommunications = $acceptCommunications;
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
        $this->deletedAt = null;
    }

    // Getters
    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getBirthDate(): DateTime
    {
        return $this->birthDate;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getAcceptTerms(): bool
    {
        return $this->acceptTerms;
    }

    public function getAcceptPrivacy(): bool
    {
        return $this->acceptPrivacy;
    }

    public function getAcceptCommunications(): bool
    {
        return $this->acceptCommunications;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    public function getDeletedAt(): ?DateTime
    {
        return $this->deletedAt;
    }

    // Setters
    public function setName(string $name): void
    {
        $this->name = $name;
        $this->updatedAt = new DateTime();
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
        $this->updatedAt = new DateTime();
    }

    public function setPassword(string $password): void
    {
        $this->password = password_hash($password, PASSWORD_ARGON2ID);
        $this->updatedAt = new DateTime();
    }

    public function setPhone(string $phone): void
    {
        $this->phone = $phone;
        $this->updatedAt = new DateTime();
    }

    public function setBirthDate(DateTime $birthDate): void
    {
        $this->birthDate = $birthDate;
        $this->updatedAt = new DateTime();
    }

    public function setRole(string $role): void
    {
        $this->role = $role;
        $this->updatedAt = new DateTime();
    }

    public function setAcceptTerms(bool $acceptTerms): void
    {
        $this->acceptTerms = $acceptTerms;
        $this->updatedAt = new DateTime();
    }

    public function setAcceptPrivacy(bool $acceptPrivacy): void
    {
        $this->acceptPrivacy = $acceptPrivacy;
        $this->updatedAt = new DateTime();
    }

    public function setAcceptCommunications(bool $acceptCommunications): void
    {
        $this->acceptCommunications = $acceptCommunications;
        $this->updatedAt = new DateTime();
    }

    public function delete(): void
    {
        $this->deletedAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'birth_date' => $this->birthDate->format('Y-m-d'),
            'role' => $this->role,
            'accept_terms' => $this->acceptTerms,
            'accept_privacy' => $this->acceptPrivacy,
            'accept_communications' => $this->acceptCommunications,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deletedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
