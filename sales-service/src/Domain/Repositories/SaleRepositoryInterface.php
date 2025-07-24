<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Entities\Sale;

interface SaleRepositoryInterface
{
    public function save(Sale $sale): bool;

    public function findById(string $id): ?Sale;

    public function findByCustomerId(string $customerId): array;

    public function findByVehicleId(string $vehicleId): ?Sale;

    public function findByReservationId(string $reservationId): ?Sale;

    public function findByPaymentId(string $paymentId): ?Sale;

    public function findAll(): array;

    public function update(Sale $sale): bool;

    public function delete(string $id): bool;
}
