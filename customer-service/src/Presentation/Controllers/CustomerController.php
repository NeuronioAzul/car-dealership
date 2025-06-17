<?php

namespace App\Presentation\Controllers;

use App\Application\UseCases\GetCustomerProfileUseCase;
use App\Application\UseCases\UpdateCustomerProfileUseCase;
use App\Infrastructure\Database\DatabaseConfig;
use App\Infrastructure\Database\CustomerRepository;
use App\Infrastructure\Messaging\EventPublisher;
use App\Presentation\Middleware\AuthMiddleware;

class CustomerController
{
    private GetCustomerProfileUseCase $getProfileUseCase;
    private UpdateCustomerProfileUseCase $updateProfileUseCase;
    private AuthMiddleware $authMiddleware;

    public function __construct()
    {
        $database = DatabaseConfig::getConnection();
        $customerRepository = new CustomerRepository($database);
        $eventPublisher = new EventPublisher();
        $this->authMiddleware = new AuthMiddleware();

        $this->getProfileUseCase = new GetCustomerProfileUseCase($customerRepository);
        $this->updateProfileUseCase = new UpdateCustomerProfileUseCase($customerRepository, $eventPublisher);
    }

    public function createCustomer(): void
    {
        try {
            // Verifica se o usuário já está autenticado
            $user = $this->authMiddleware->requireCustomer();
            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input) {
                throw new \Exception('Dados inválidos', 400);
            }

            $database = DatabaseConfig::getConnection();
            $customerRepository = new CustomerRepository($database);

            $customer = 

            http_response_code(201);
            echo json_encode([
                'success' => true,
                'data' => $customer,
                'message' => 'Cliente criado com sucesso'
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
        

    public function getProfile(): void
    {
        try {
            $user = $this->authMiddleware->requireCustomer();


            $profile = $this->getProfileUseCase->execute($user['user_id']);

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $profile
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

    public function updateProfile(): void
    {
        try {
            $user = $this->authMiddleware->requireCustomer();
            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input) {
                throw new \Exception('Dados inválidos', 400);
            }

            $profile = $this->updateProfileUseCase->execute($user['user_id'], $input);

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $profile,
                'message' => 'Perfil atualizado com sucesso'
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

    public function deleteProfile(): void
    {
        try {
            $user = $this->authMiddleware->requireCustomer();

            $database = DatabaseConfig::getConnection();
            $customerRepository = new CustomerRepository($database);

            if (!$customerRepository->delete($user['user_id'])) {
                throw new \Exception('Erro ao excluir perfil', 500);
            }

            // Publicar evento de exclusão
            $eventPublisher = new EventPublisher();
            $eventPublisher->publish('customer.profile_deleted', [
                'customer_id' => $user['user_id'],
                'timestamp' => date('Y-m-d H:i:s')
            ]);

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Perfil excluído com sucesso'
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
            'service' => 'customer-service',
            'status' => 'healthy',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
}
