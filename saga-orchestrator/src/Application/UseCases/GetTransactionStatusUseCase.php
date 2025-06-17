<?php

namespace App\Application\UseCases;

use App\Domain\Repositories\SagaTransactionRepositoryInterface;

class GetTransactionStatusUseCase
{
    private SagaTransactionRepositoryInterface $transactionRepository;

    public function __construct(SagaTransactionRepositoryInterface $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    public function execute(string $transactionId, string $customerId): array
    {
        $transaction = $this->transactionRepository->findById($transactionId);
        
        if (!$transaction) {
            throw new \Exception('Transação não encontrada', 404);
        }

        if ($transaction->getCustomerId() !== $customerId) {
            throw new \Exception('Acesso negado. Esta transação não pertence ao cliente', 403);
        }

        return $transaction->toArray();
    }
}

