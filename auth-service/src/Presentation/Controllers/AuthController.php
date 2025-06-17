<?php

namespace App\Presentation\Controllers;

use App\Application\UseCases\LoginUseCase;
use App\Application\UseCases\RegisterUseCase;
use App\Application\Services\JWTService;
use App\Infrastructure\Database\DatabaseConfig;
use App\Infrastructure\Database\UserRepository;
use App\Infrastructure\Messaging\EventPublisher;
use App\Application\Requests\RequestUser;

class AuthController
{
    private LoginUseCase $loginUseCase;
    private RegisterUseCase $registerUseCase;
    private JWTService $jwtService;

    public function __construct()
    {
        $database = DatabaseConfig::getConnection();
        $userRepository = new UserRepository($database);
        $eventPublisher = new EventPublisher();
        $this->jwtService = new JWTService();

        $this->loginUseCase = new LoginUseCase($userRepository, $this->jwtService, $eventPublisher);
        $this->registerUseCase = new RegisterUseCase($userRepository, $eventPublisher);
    }

    public function login(): void
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);

            if (!isset($input['email']) || !isset($input['password'])) {
                throw new \Exception('Email e senha são obrigatórios', 400);
            }

            $result = $this->loginUseCase->execute($input['email'], $input['password']);

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

    public function register(): void
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $request = new RequestUser($input);

            if (!$input) {
                throw new \Exception('Dados inválidos', 400);
            }

            if (!$request->isValid()) {
                http_response_code(422);
                echo json_encode([
                    'error' => true,
                    'message' => 'Erro de validação.',
                    'errors' => $request->errors()
                ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                return;
            }

            $result = $this->registerUseCase->execute($input);

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

    public function refresh(): void
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);

            if (!isset($input['refresh_token'])) {
                throw new \Exception('Refresh token é obrigatório', 400);
            }

            $newToken = $this->jwtService->refreshToken($input['refresh_token']);

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => [
                    'access_token' => $newToken,
                    'token_type' => 'Bearer',
                    'expires_in' => $_ENV['JWT_EXPIRATION']
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

    public function logout(): void
    {
        // Para logout, apenas retornamos sucesso
        // Em uma implementação real, poderíamos invalidar o token
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Logout realizado com sucesso'
        ]);
    }

    public function validate(): void
    {
        try {
            $headers = getallheaders();
            $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

            if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
                throw new \Exception('Token não fornecido', 401);
            }

            $token = substr($authHeader, 7);
            $decoded = $this->jwtService->validateToken($token);

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => [
                    'valid' => true,
                    'user_id' => $decoded['sub'],
                    'email' => $decoded['email'] ?? null,
                    'role' => $decoded['role'] ?? 'customer'
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
            'service' => 'auth-service',
            'status' => 'healthy',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
}
