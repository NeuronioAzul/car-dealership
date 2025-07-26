<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Application\DTOs\VehicleDTO;

interface VehicleRepositoryInterface
{
    public function save(VehicleDTO $vehicle): bool;

    public function findById(string $id): ?VehicleDTO;

    public function findAll(): array;

    public function findAvailable(): array;

    public function search(array $criteria): array;

    public function update(VehicleDTO $vehicle): bool;

    public function partialUpdate(string $id, array $fieldsToUpdate): bool;

    public function delete(string $id): bool;

    public function updateStatus(string $id, string $status): bool;

    public function findByChassisNumber(string $chassisNumber): ?VehicleDTO;

    public function findByLicensePlate(string $licensePlate): ?VehicleDTO;

    public function findByRenavam(string $renavam): ?VehicleDTO;
}
