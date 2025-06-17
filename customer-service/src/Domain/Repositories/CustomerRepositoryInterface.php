<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\Customer;

interface CustomerRepositoryInterface
{
    public function save(Customer $customer): bool;
    public function findById(string $id): ?Customer;
    public function findByEmail(string $email): ?Customer;
    public function findAll(): array;
    public function update(Customer $customer): bool;
    public function delete(string $id): bool;
    public function existsByEmail(string $email): bool;
}

