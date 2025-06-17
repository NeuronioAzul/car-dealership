<?php

namespace App\Application\UseCases;

use App\Domain\Entities\SagaTransaction;
use App\Domain\Repositories\SagaTransactionRepositoryInterface;
use App\Application\Sagas\VehiclePurchaseSaga;

class StartVehiclePurchaseUseCase
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

    public function execute(string $customerId, string $vehicleId, array $customerData, string $authToken): array
    {
        // Verificar se já existe transação ativa para este cliente e veículo
        $existingTransactions = $this->transactionRepository->findByCustomerId($customerId);
        foreach ($existingTransactions as $transaction) {
            if ($transaction->getVehicleId() === $vehicleId && 
                in_array($transaction->getStatus(), ['started', 'in_progress'])) {
                throw new \Exception('Já existe uma transação ativa para este veículo', 409);
            }
        }

        // Iniciar nova transação SAGA
        $transaction = $this->vehiclePurchaseSaga->startTransaction($customerId, $vehicleId, $customerData, $authToken);

        return [
            'transaction_id' => $transaction->getId(),
            'status' => $transaction->getStatus(),
            'current_step' => $transaction->getCurrentStep(),
            'message' => 'Transação de compra iniciada com sucesso'
        ];
    }
}

