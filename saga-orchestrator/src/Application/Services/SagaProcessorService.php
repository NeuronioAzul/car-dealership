<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Sagas\VehiclePurchaseSaga;
use App\Domain\Repositories\SagaTransactionRepositoryInterface;

class SagaProcessorService
{
    private SagaTransactionRepositoryInterface $transactionRepository;
    private VehiclePurchaseSaga $vehiclePurchaseSaga;

    public function __construct(
        SagaTransactionRepositoryInterface $transactionRepository,
        VehiclePurchaseSaga $vehiclePurchaseSaga
    ) {
        $this->transactionRepository = $transactionRepository;
        $this->vehiclePurchaseSaga = $vehiclePurchaseSaga;
    }

    public function processAllPendingTransactions(): array
    {
        $pendingTransactions = $this->transactionRepository->findPendingTransactions();
        $results = [];

        foreach ($pendingTransactions as $transaction) {
            try {
                $result = $this->processSingleTransaction($transaction);
                $results[] = [
                    'transaction_id' => $transaction->getId(),
                    'status' => 'processed',
                    'result' => $result,
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'transaction_id' => $transaction->getId(),
                    'status' => 'error',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    public function processSingleTransaction($transaction): array
    {
        $initialStatus = $transaction->getStatus();
        $initialStep = $transaction->getCurrentStep();

        if ($transaction->isCompensating()) {
            $this->vehiclePurchaseSaga->compensateTransaction($transaction);

            // Recarregar transação para obter estado atualizado
            $updatedTransaction = $this->transactionRepository->findById($transaction->getId());

            return [
                'action' => 'compensation',
                'initial_status' => $initialStatus,
                'final_status' => $updatedTransaction->getStatus(),
                'compensation_step' => $updatedTransaction->getNextCompensationStep(),
            ];
        } else {
            $this->vehiclePurchaseSaga->processNextStep($transaction);

            // Recarregar transação para obter estado atualizado
            $updatedTransaction = $this->transactionRepository->findById($transaction->getId());

            return [
                'action' => 'step_execution',
                'initial_status' => $initialStatus,
                'final_status' => $updatedTransaction->getStatus(),
                'initial_step' => $initialStep,
                'current_step' => $updatedTransaction->getCurrentStep(),
                'completed_steps' => $updatedTransaction->getCompletedSteps(),
            ];
        }
    }

    public function retryFailedTransaction(string $transactionId): array
    {
        $transaction = $this->transactionRepository->findById($transactionId);

        if (!$transaction) {
            throw new \Exception('Transação não encontrada', 404);
        }

        if (!$transaction->isFailed()) {
            throw new \Exception('Apenas transações falhadas podem ser reprocessadas', 400);
        }

        // Resetar status para permitir reprocessamento
        $transaction->setStatus('in_progress');
        $transaction->setFailureReason(null);
        $this->transactionRepository->update($transaction);

        return $this->processSingleTransaction($transaction);
    }

    public function getTransactionStatistics(): array
    {
        $allTransactions = $this->transactionRepository->findByStatus('completed') +
                          $this->transactionRepository->findByStatus('failed') +
                          $this->transactionRepository->findByStatus('compensated') +
                          $this->transactionRepository->findPendingTransactions();

        $stats = [
            'total' => count($allTransactions),
            'completed' => 0,
            'failed' => 0,
            'compensated' => 0,
            'pending' => 0,
            'success_rate' => 0,
        ];

        foreach ($allTransactions as $transaction) {
            switch ($transaction->getStatus()) {
                case 'completed':
                    $stats['completed']++;
                    break;
                case 'failed':
                    $stats['failed']++;
                    break;
                case 'compensated':
                    $stats['compensated']++;
                    break;
                default:
                    $stats['pending']++;
                    break;
            }
        }

        if ($stats['total'] > 0) {
            $stats['success_rate'] = ($stats['completed'] / $stats['total']) * 100;
        }

        return $stats;
    }
}
