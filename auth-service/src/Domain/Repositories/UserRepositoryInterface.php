<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Entities\User;

interface UserRepositoryInterface
{
    public function save(User $user): bool;

    public function findById(string $id): ?User;

    public function findByEmail(string $email): ?User;

    public function findAll(): array;

    public function update(User $user): bool;

    public function delete(string $id): bool;

    public function existsByEmail(string $email): bool;
}
