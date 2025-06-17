<?php

namespace App\Application\Sagas;

use App\Domain\Entities\SagaTransaction;
use App\Domain\Repositories\SagaTransactionRepositoryInterface;
use App\Application\Services\MicroserviceClient;
use App\Infrastructure\Messaging\EventPublisher;

class VehiclePurchaseSaga
{
    private SagaTransactionRepositoryInterface $transactionRepository;
    private MicroserviceClient $microserviceClient;
    private EventPublisher $eventPublisher;

    public function __construct(
        SagaTransactionRepositoryInterface $transactionRepository,
        MicroserviceClient $microserviceClient,
        EventPublisher $eventPublisher
    ) {
        $this->transactionRepository = $transactionRepository;
        $this->microserviceClient = $microserviceClient;
        $this->eventPublisher = $eventPublisher;
    }

    public function startTransaction(string $customerId, string $vehicleId, array $customerData, string $authToken): SagaTransaction
    {
        // Criar nova transação SAGA
        $transaction = new SagaTransaction($customerId, $vehicleId, 'purchase_vehicle');
        $transaction->addToContext('customer_data', $customerData);
        $transaction->addToContext('auth_token', $authToken);
        
        // Buscar dados do veículo
        try {
            $vehicleResponse = $this->microserviceClient->getVehicleDetails($vehicleId);
            $vehicleData = $vehicleResponse['data']['data'];
            $transaction->addToContext('vehicle_data', $vehicleData);
            $transaction->addToContext('vehicle_price', $vehicleData['price']);
        } catch (\Exception $e) {
            $transaction->failStep('get_vehicle_details', 'Erro ao obter dados do veículo: ' . $e->getMessage());
            $this->transactionRepository->save($transaction);
            throw $e;
        }

        // Salvar transação
        $this->transactionRepository->save($transaction);

        // Iniciar processamento
        $transaction->startProgress();
        $this->transactionRepository->update($transaction);

        // Publicar evento de início
        $this->eventPublisher->publish('saga.transaction_started', [
            'transaction_id' => $transaction->getId(),
            'customer_id' => $customerId,
            'vehicle_id' => $vehicleId,
            'type' => 'purchase_vehicle',
            'timestamp' => date('Y-m-d H:i:s')
        ]);

        return $transaction;
    }

    public function processNextStep(SagaTransaction $transaction): void
    {
        $currentStep = $transaction->getCurrentStep();
        
        if (!$currentStep) {
            return;
        }

        try {
            switch ($currentStep) {
                case 'create_reservation':
                    $this->executeCreateReservation($transaction);
                    break;
                    
                case 'generate_payment_code':
                    $this->executeGeneratePaymentCode($transaction);
                    break;
                    
                case 'process_payment':
                    $this->executeProcessPayment($transaction);
                    break;
                    
                case 'create_sale':
                    $this->executeCreateSale($transaction);
                    break;
                    
                case 'update_vehicle_status':
                    $this->executeUpdateVehicleStatus($transaction);
                    break;
                    
                default:
                    throw new \Exception("Passo desconhecido: {$currentStep}");
            }
            
            $this->transactionRepository->update($transaction);
            
        } catch (\Exception $e) {
            $transaction->failStep($currentStep, $e->getMessage());
            $this->transactionRepository->update($transaction);
            
            // Publicar evento de falha
            $this->eventPublisher->publish('saga.step_failed', [
                'transaction_id' => $transaction->getId(),
                'step' => $currentStep,
                'error' => $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
    }

    private function executeCreateReservation(SagaTransaction $transaction): void
    {
        $customerId = $transaction->getCustomerId();
        $vehicleId = $transaction->getVehicleId();
        $authToken = $transaction->getFromContext('auth_token');

        $response = $this->microserviceClient->createReservation($customerId, $vehicleId, $authToken);
        $reservationData = $response['data']['data'];

        $transaction->completeStep('create_reservation', [
            'reservation_id' => $reservationData['id'],
            'expires_at' => $reservationData['expires_at']
        ]);
    }

    private function executeGeneratePaymentCode(SagaTransaction $transaction): void
    {
        $reservationId = $transaction->getFromContext('create_reservation_data')['reservation_id'];
        $authToken = $transaction->getFromContext('auth_token');

        $response = $this->microserviceClient->generatePaymentCode($reservationId, $authToken);
        $paymentCodeData = $response['data']['data'];

        $transaction->completeStep('generate_payment_code', [
            'payment_code' => $paymentCodeData['payment_code']
        ]);
    }

    private function executeProcessPayment(SagaTransaction $transaction): void
    {
        $customerId = $transaction->getCustomerId();
        $vehicleId = $transaction->getVehicleId();
        $reservationId = $transaction->getFromContext('create_reservation_data')['reservation_id'];
        $paymentCode = $transaction->getFromContext('generate_payment_code_data')['payment_code'];
        $amount = $transaction->getFromContext('vehicle_price');
        $authToken = $transaction->getFromContext('auth_token');

        // Criar pagamento
        $createResponse = $this->microserviceClient->createPayment(
            $customerId, $reservationId, $vehicleId, $paymentCode, $amount, $authToken
        );

        // Processar pagamento
        $processResponse = $this->microserviceClient->processPayment($paymentCode, 'credit_card', $authToken);
        $paymentResult = $processResponse['data'];

        if (!$paymentResult['success']) {
            throw new \Exception('Pagamento recusado: ' . $paymentResult['message']);
        }

        $transaction->completeStep('process_payment', [
            'payment_id' => $paymentResult['data']['payment']['id'],
            'transaction_id' => $paymentResult['data']['payment']['transaction_id'],
            'amount_paid' => $paymentResult['data']['payment']['amount']
        ]);
    }

    private function executeCreateSale(SagaTransaction $transaction): void
    {
        $customerId = $transaction->getCustomerId();
        $vehicleId = $transaction->getVehicleId();
        $reservationId = $transaction->getFromContext('create_reservation_data')['reservation_id'];
        $paymentId = $transaction->getFromContext('process_payment_data')['payment_id'];
        $salePrice = $transaction->getFromContext('process_payment_data')['amount_paid'];
        $customerData = $transaction->getFromContext('customer_data');
        $vehicleData = $transaction->getFromContext('vehicle_data');
        $authToken = $transaction->getFromContext('auth_token');

        $response = $this->microserviceClient->createSale(
            $customerId, $vehicleId, $reservationId, $paymentId, $salePrice, 
            $customerData, $vehicleData, $authToken
        );
        $saleData = $response['data']['data'];

        $transaction->completeStep('create_sale', [
            'sale_id' => $saleData['sale']['id'],
            'contract_pdf' => $saleData['documents']['contract'],
            'invoice_pdf' => $saleData['documents']['invoice']
        ]);
    }

    private function executeUpdateVehicleStatus(SagaTransaction $transaction): void
    {
        $vehicleId = $transaction->getVehicleId();

        $response = $this->microserviceClient->updateVehicleStatus($vehicleId, 'sold');

        $transaction->completeStep('update_vehicle_status', [
            'vehicle_status' => 'sold',
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // Publicar evento de transação completada
        $this->eventPublisher->publish('saga.transaction_completed', [
            'transaction_id' => $transaction->getId(),
            'customer_id' => $transaction->getCustomerId(),
            'vehicle_id' => $transaction->getVehicleId(),
            'sale_id' => $transaction->getFromContext('create_sale_data')['sale_id'],
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    public function compensateTransaction(SagaTransaction $transaction): void
    {
        $compensationStep = $transaction->getNextCompensationStep();
        
        if (!$compensationStep) {
            $transaction->completeCompensation();
            $this->transactionRepository->update($transaction);
            return;
        }

        try {
            switch ($compensationStep) {
                case 'update_vehicle_status':
                    $this->compensateUpdateVehicleStatus($transaction);
                    break;
                    
                case 'create_sale':
                    $this->compensateCreateSale($transaction);
                    break;
                    
                case 'process_payment':
                    $this->compensateProcessPayment($transaction);
                    break;
                    
                case 'create_reservation':
                    $this->compensateCreateReservation($transaction);
                    break;
            }
            
            $transaction->completeStep($compensationStep . '_compensated');
            $this->transactionRepository->update($transaction);
            
        } catch (\Exception $e) {
            // Log erro de compensação mas continue tentando
            error_log("Erro na compensação {$compensationStep}: " . $e->getMessage());
        }
    }

    private function compensateUpdateVehicleStatus(SagaTransaction $transaction): void
    {
        $vehicleId = $transaction->getVehicleId();
        $this->microserviceClient->updateVehicleStatus($vehicleId, 'available');
    }

    private function compensateCreateSale(SagaTransaction $transaction): void
    {
        $saleId = $transaction->getFromContext('create_sale_data')['sale_id'];
        $authToken = $transaction->getFromContext('auth_token');
        $this->microserviceClient->cancelSale($saleId, $authToken);
    }

    private function compensateProcessPayment(SagaTransaction $transaction): void
    {
        $paymentId = $transaction->getFromContext('process_payment_data')['payment_id'];
        $authToken = $transaction->getFromContext('auth_token');
        $this->microserviceClient->refundPayment($paymentId, $authToken);
    }

    private function compensateCreateReservation(SagaTransaction $transaction): void
    {
        $reservationId = $transaction->getFromContext('create_reservation_data')['reservation_id'];
        $authToken = $transaction->getFromContext('auth_token');
        $this->microserviceClient->cancelReservation($reservationId, $authToken);
    }
}

