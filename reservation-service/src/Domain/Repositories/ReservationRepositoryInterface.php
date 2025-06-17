<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\Reservation;

interface ReservationRepositoryInterface
{
    public function save(Reservation $reservation): bool;
    public function findById(string $id): ?Reservation;
    public function findByCustomerId(string $customerId): array;
    public function findActiveByCustomerId(string $customerId): array;
    public function findActiveByVehicleId(string $vehicleId): ?Reservation;
    public function findByPaymentCode(string $paymentCode): ?Reservation;
    public function findExpired(): array;
    public function update(Reservation $reservation): bool;
    public function delete(string $id): bool;
}

