<?php

namespace App\Presentation\Controllers;

use App\Application\UseCases\GetCustomerProfileUseCase;
use App\Application\UseCases\UpdateCustomerProfileUseCase;
use App\Application\UseCases\CreateCustomerProfileUseCase;
use App\Infrastructure\Database\DatabaseConfig;
use App\Infrastructure\Database\CustomerRepository;
use App\Infrastructure\Messaging\EventPublisher;
use App\Presentation\Middleware\AuthMiddleware;
use App\Application\DTOs\CustomerDTO;
use App\Domain\ValueObjects\CustomerAddress;
use App\Application\Requests\RequestCustomer;

class CustomerController
{
    private GetCustomerProfileUseCase $getProfileUseCase;
    private UpdateCustomerProfileUseCase $updateProfileUseCase;
    private CreateCustomerProfileUseCase $createCustomerProfileUseCase;
    private AuthMiddleware $authMiddleware;

    public function __construct()
    {
        $database = DatabaseConfig::getConnection();
        $customerRepository = new CustomerRepository($database);
        $eventPublisher = new EventPublisher();
        $this->authMiddleware = new AuthMiddleware();

        $this->getProfileUseCase = new GetCustomerProfileUseCase($customerRepository);
        $this->updateProfileUseCase = new UpdateCustomerProfileUseCase($customerRepository, $eventPublisher);
        $this->createCustomerProfileUseCase = new CreateCustomerProfileUseCase($customerRepository);
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

            $customer = $customerRepository->findByEmail($input['email']);

            if ($customer) {
                throw new \Exception('Cliente já cadastrado com este e-mail', 400);
            }

            // Valida os dados de entrada
            $request = new RequestCustomer($input);

            if (!$request->isValid()) {
                http_response_code(422);
                echo json_encode([
                    'error' => true,
                    'message' => 'Erro de validação.',
                    'errors' => $request->errors()
                ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                return;
            }

            $customer = new CustomerDTO(
                userId: $user['user_id'], // Associa o cliente ao usuário autenticado
                fullName: $input['full_name'],
                email: $user['email'],
                cpf: $input['cpf'] ?? null,
                rg: $input['rg'] ?? null,
                birthDate: isset($user['birth_date']) ? new \DateTime($user['birth_date']) : null,
                gender: $input['gender'] ?? null,
                maritalStatus: $input['marital_status'] ?? null,
                phone: $user['phone'] ?? null,
                mobile: $input['cellphone'] ?? null,
                whatsapp: $input['profession'] ?? null,
                address: new CustomerAddress(
                    street: $input['address']['street'],
                    number: $input['address']['number'],
                    complement: $input['address']['complement'] ?? '',
                    neighborhood: $input['address']['neighborhood'],
                    city: $input['address']['city'],
                    state: $input['address']['state'],
                    zipCode: $input['address']['zip_code']
                ),
                occupation: $input['occupation'] ?? null,
                company: $input['company'] ?? null,
                monthlyIncome: $input['monthly_income'] ?? 0.0,
                preferredContact: $input['preferred_contact'] ?? 'email',
                newsletterSubscription: $input['newsletter_subscription'] ?? false,
                smsNotifications: $input['sms_notifications'] ?? false,
                totalPurchases: 0, // Inicialmente zero
                totalSpent: 0.0, // Inicialmente zero
                lastPurchaseDate: null, // Inicialmente nulo
                customerScore: 0, // Inicialmente zero
                customerTier: 'bronze' // Inicialmente bronze
            );

            $customer = $this->createCustomerProfileUseCase->execute($customer);

            var_dump($user['email'], $customer);
            die();

            http_response_code(201);
            echo json_encode([
                'success' => true,
                'data' => $customer,
                'message' => 'Cliente criado com sucesso'
            ]);
        } catch (\Exception $e) {
            $code = (int)$e->getCode() ?: 500;
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
