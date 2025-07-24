<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\Requests\RequestUser;
use App\Application\Services\JWTService;
use App\Application\Services\TokenBlacklistService;
use App\Application\UseCases\LoginUseCase;
use App\Application\UseCases\LogoutUseCase;
use App\Application\UseCases\RegisterUseCase;
use App\Infrastructure\Database\DatabaseConfig;
use App\Infrastructure\Database\TokenBlacklistRepository;
use App\Infrastructure\Database\UserRepository;
use App\Infrastructure\Messaging\EventPublisher;

class AuthController
{
    private LoginUseCase $loginUseCase;
    private RegisterUseCase $registerUseCase;
    private LogoutUseCase $logoutUseCase;
    private JWTService $jwtService;
    private TokenBlacklistService $blacklistService;

    public function __construct()
    {
        $database = DatabaseConfig::getConnection();
        $userRepository = new UserRepository($database);
        $eventPublisher = new EventPublisher();

        // Inicializar serviços de token
        $blacklistRepository = new TokenBlacklistRepository($database);
        $this->blacklistService = new TokenBlacklistService($blacklistRepository);
        $this->jwtService = new JWTService($this->blacklistService);

        $this->loginUseCase = new LoginUseCase($userRepository, $this->jwtService, $eventPublisher);
        $this->registerUseCase = new RegisterUseCase($userRepository, $eventPublisher);
        $this->logoutUseCase = new LogoutUseCase($this->jwtService);
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
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            $code = $e->getCode() ?: 500;
            http_response_code($code);
            echo json_encode([
                'error' => true,
                'message' => $e->getMessage(),
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
                    'errors' => $request->errors(),
                ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

                return;
            }

            $result = $this->registerUseCase->execute($input);

            http_response_code(201);
            echo json_encode([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            $code = $e->getCode() ?: 500;
            http_response_code($code);
            echo json_encode([
                'error' => true,
                'message' => $e->getMessage(),
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
                    'expires_in' => $_ENV['JWT_EXPIRATION'],
                ],
            ]);
        } catch (\Exception $e) {
            $code = $e->getCode() ?: 500;
            http_response_code($code);
            echo json_encode([
                'error' => true,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function logout(): void
    {
        try {
            $headers = getallheaders();
            $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

            if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
                throw new \Exception('Token não fornecido para logout', 400);
            }

            $token = substr($authHeader, 7);

            // Usar o LogoutUseCase
            $this->logoutUseCase->execute($token);

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Logout realizado com sucesso. Token invalidado.',
            ]);
        } catch (\Exception $e) {
            $code = $e->getCode() ?: 500;

            // Mensagens mais amigáveis para diferentes tipos de erro
            $message = $e->getMessage();

            if (str_contains($message, 'revogado')) {
                $message = 'Token já foi invalidado anteriormente.';
            } elseif (str_contains($message, 'Expired token')) {
                $message = 'Token já expirou.';
            } elseif (str_contains($message, 'Token inválido')) {
                $message = 'Token inválido para logout.';
            }

            http_response_code($code);
            echo json_encode([
                'error' => true,
                'message' => $message,
            ]);
        }
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
                    'role' => $decoded['role'] ?? 'customer',
                    'expires_at' => $decoded['exp'] ?? null,
                ],
            ]);
        } catch (\Exception $e) {
            $code = $e->getCode() ?: 401;

            // Mensagens mais amigáveis para diferentes tipos de erro
            $message = $e->getMessage();

            if (str_contains($message, 'revogado')) {
                $message = 'Token foi invalidado. Faça login novamente.';
            } elseif (str_contains($message, 'Expired token')) {
                $message = 'Token expirado. Faça login novamente.';
            } elseif (str_contains($message, 'Token inválido')) {
                $message = 'Token inválido. Faça login novamente.';
            }

            http_response_code($code);
            echo json_encode([
                'error' => true,
                'message' => $message,
                'valid' => false,
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
            'timestamp' => date('Y-m-d H:i:s'),
        ]);
    }
}
