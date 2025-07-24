<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Entities\SagaTransaction;

interface SagaTransactionRepositoryInterface
{
    public function save(SagaTransaction $transaction): bool;

    public function findById(string $id): ?SagaTransaction;

    public function findByCustomerId(string $customerId): array;

    public function findByStatus(string $status): array;

    public function findPendingTransactions(): array;

    public function update(SagaTransaction $transaction): bool;

    public function delete(string $id): bool;
}
