<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\Vehicle;

interface VehicleRepositoryInterface
{
    public function save(Vehicle $vehicle): bool;
    public function findById(string $id): ?Vehicle;
    public function findAll(): array;
    public function findAvailable(): array;
    public function search(array $criteria): array;
    public function update(Vehicle $vehicle): bool;
    public function delete(string $id): bool;
    public function updateStatus(string $id, string $status): bool;
}

