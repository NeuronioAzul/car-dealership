<?php

namespace App\Presentation\Controllers;

use App\Application\UseCases\StartVehiclePurchaseUseCase;
use App\Application\UseCases\GetTransactionStatusUseCase;
use App\Infrastructure\Database\DatabaseConfig;
use App\Infrastructure\Database\SagaTransactionRepository;
use App\Application\Services\MicroserviceClient;
use App\Application\Sagas\VehiclePurchaseSaga;
use App\Infrastructure\Messaging\EventPublisher;
use App\Presentation\Middleware\AuthMiddleware;

class SagaController
{
    private StartVehiclePurchaseUseCase $startPurchaseUseCase;
    private GetTransactionStatusUseCase $getStatusUseCase;
    private VehiclePurchaseSaga $vehiclePurchaseSaga;
    private SagaTransactionRepository $transactionRepository;
    private AuthMiddleware $authMiddleware;

    public function __construct()
    {
        $database = DatabaseConfig::getConnection();
        $this->transactionRepository = new SagaTransactionRepository($database);
        $microserviceClient = new MicroserviceClient();
        $eventPublisher = new EventPublisher();
        $this->authMiddleware = new AuthMiddleware();

        $this->vehiclePurchaseSaga = new VehiclePurchaseSaga($this->transactionRepository, $microserviceClient, $eventPublisher);
        $this->startPurchaseUseCase = new StartVehiclePurchaseUseCase($this->transactionRepository, $this->vehiclePurchaseSaga);
        $this->getStatusUseCase = new GetTransactionStatusUseCase($this->transactionRepository);
    }

    public function startVehiclePurchase(): void
    {
        try {
            $user = $this->authMiddleware->requireCustomer();
            $input = json_decode(file_get_contents('php://input'), true);
            
            $requiredFields = ['vehicle_id', 'customer_data'];
            foreach ($requiredFields as $field) {
                if (!isset($input[$field])) {
                    throw new \Exception("Campo obrigatório: {$field}", 400);
                }
            }

            $authToken = $this->authMiddleware->getTokenFromHeader();

            $result = $this->startPurchaseUseCase->execute(
                $user['user_id'],
                $input['vehicle_id'],
                $input['customer_data'],
                $authToken
            );
            
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'data' => $result
            ]);
            
        } catch (\Exception $e) {
            $code = $e->getCode() ?: 500;
            http_response_code($code);
            echo json_encode([
                'error' => true,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getTransactionStatus(): void
    {
        try {
            $user = $this->authMiddleware->requireCustomer();
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $pathParts = explode('/', trim($path, '/'));
            $transactionId = end($pathParts);

            if (!$transactionId) {
                throw new \Exception('ID da transação é obrigatório', 400);
            }

            $result = $this->getStatusUseCase->execute($transactionId, $user['user_id']);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $result
            ]);
            
        } catch (\Exception $e) {
            $code = $e->getCode() ?: 500;
            http_response_code($code);
            echo json_encode([
                'error' => true,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function processTransactions(): void
    {
        try {
            // Buscar transações pendentes
            $pendingTransactions = $this->transactionRepository->findPendingTransactions();
            
            $processed = 0;
            foreach ($pendingTransactions as $transaction) {
                if ($transaction->isCompensating()) {
                    $this->vehiclePurchaseSaga->compensateTransaction($transaction);
                } else {
                    $this->vehiclePurchaseSaga->processNextStep($transaction);
                }
                $processed++;
            }
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => "Processadas {$processed} transações",
                'processed_count' => $processed
            ]);
            
        } catch (\Exception $e) {
            $code = $e->getCode() ?: 500;
            http_response_code($code);
            echo json_encode([
                'error' => true,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function health(): void
    {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'service' => 'saga-orchestrator',
            'status' => 'healthy',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
}

