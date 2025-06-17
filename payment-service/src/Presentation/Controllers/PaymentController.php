<?php

namespace App\Presentation\Controllers;

use App\Application\UseCases\ProcessPaymentUseCase;
use App\Application\UseCases\CreatePaymentUseCase;
use App\Application\UseCases\GetPaymentStatusUseCase;
use App\Infrastructure\Database\DatabaseConfig;
use App\Infrastructure\Database\PaymentRepository;
use App\Application\Services\FakePaymentGatewayService;
use App\Infrastructure\Messaging\EventPublisher;
use App\Presentation\Middleware\AuthMiddleware;

class PaymentController
{
    private ProcessPaymentUseCase $processPaymentUseCase;
    private CreatePaymentUseCase $createPaymentUseCase;
    private GetPaymentStatusUseCase $getPaymentStatusUseCase;
    private AuthMiddleware $authMiddleware;

    public function __construct()
    {
        $database = DatabaseConfig::getConnection();
        $paymentRepository = new PaymentRepository($database);
        $paymentGateway = new FakePaymentGatewayService();
        $eventPublisher = new EventPublisher();
        $this->authMiddleware = new AuthMiddleware();

        $this->processPaymentUseCase = new ProcessPaymentUseCase($paymentRepository, $paymentGateway, $eventPublisher);
        $this->createPaymentUseCase = new CreatePaymentUseCase($paymentRepository);
        $this->getPaymentStatusUseCase = new GetPaymentStatusUseCase($paymentRepository);
    }

    public function processPayment(): void
    {
        try {
            $user = $this->authMiddleware->requireCustomer();
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['payment_code'])) {
                throw new \Exception('Código de pagamento é obrigatório', 400);
            }

            $result = $this->processPaymentUseCase->execute(
                $input['payment_code'],
                $user['user_id'],
                $input
            );
            
            $httpCode = $result['success'] ? 200 : 400;
            http_response_code($httpCode);
            echo json_encode([
                'success' => $result['success'],
                'data' => $result['payment'],
                'message' => $result['message']
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

    public function createPayment(): void
    {
        try {
            $user = $this->authMiddleware->requireCustomer();
            $input = json_decode(file_get_contents('php://input'), true);
            
            $requiredFields = ['reservation_id', 'vehicle_id', 'payment_code', 'amount'];
            foreach ($requiredFields as $field) {
                if (!isset($input[$field])) {
                    throw new \Exception("Campo obrigatório: {$field}", 400);
                }
            }

            $result = $this->createPaymentUseCase->execute(
                $user['user_id'],
                $input['reservation_id'],
                $input['vehicle_id'],
                $input['payment_code'],
                (float) $input['amount'],
                $input['method'] ?? 'credit_card'
            );
            
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'data' => $result,
                'message' => 'Pagamento criado com sucesso'
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

    public function getPaymentStatus(): void
    {
        try {
            $user = $this->authMiddleware->requireCustomer();
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $pathParts = explode('/', trim($path, '/'));
            $paymentCode = end($pathParts);

            if (!$paymentCode) {
                throw new \Exception('Código de pagamento é obrigatório', 400);
            }

            $result = $this->getPaymentStatusUseCase->execute($paymentCode, $user['user_id']);
            
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

    public function listCustomerPayments(): void
    {
        try {
            $user = $this->authMiddleware->requireCustomer();
            
            $database = DatabaseConfig::getConnection();
            $paymentRepository = new PaymentRepository($database);
            $payments = $paymentRepository->findByCustomerId($user['user_id']);
            
            $result = array_map(function($payment) {
                return $payment->toArray();
            }, $payments);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => [
                    'payments' => $result,
                    'total' => count($result)
                ]
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
            'service' => 'payment-service',
            'status' => 'healthy',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
}

