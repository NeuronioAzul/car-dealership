<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Application\DTOs\CustomerDTO;

interface CustomerRepositoryInterface
{
    public function save(CustomerDTO $customer): bool;

    public function findById(string $id): ?CustomerDTO;

    public function findByUserId(string $id): ?CustomerDTO;

    public function findByEmail(string $email): ?CustomerDTO;

    public function findAll(): array;

    public function update(CustomerDTO $customer): bool;

    public function delete(string $id): bool;

    public function existsByEmail(string $email): bool;
}
