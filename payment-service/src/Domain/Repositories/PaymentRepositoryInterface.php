<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\Payment;

interface PaymentRepositoryInterface
{
    public function save(Payment $payment): bool;
    public function findById(string $id): ?Payment;
    public function findByPaymentCode(string $paymentCode): ?Payment;
    public function findByCustomerId(string $customerId): array;
    public function findByReservationId(string $reservationId): ?Payment;
    public function findByTransactionId(string $transactionId): ?Payment;
    public function findPending(): array;
    public function update(Payment $payment): bool;
    public function delete(string $id): bool;
}

