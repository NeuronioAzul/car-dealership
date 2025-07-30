<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\Requests\RequestUser;
use App\Application\Services\JWTService;
use App\Application\Services\TokenBlacklistService;
use App\Application\UseCases\LoginUseCase;
use App\Application\UseCases\LogoutUseCase;
use App\Application\UseCases\RegisterUseCase;
use App\Application\Validation\RequestValidator;
use App\Infrastructure\Config\JWTConfig;
use App\Infrastructure\Database\DatabaseConfig;
use App\Infrastructure\Database\TokenBlacklistRepository;
use App\Infrastructure\Database\UserRepository;
use App\Infrastructure\DI\Container;
use App\Infrastructure\Messaging\EventPublisher;
use App\Presentation\Exceptions\BadRequestException;
use App\Presentation\Exceptions\UnauthorizedException;
use App\Presentation\Exceptions\InternalServerErrorException;
use App\Presentation\Exceptions\UnprocessableEntityException;

class AuthController
{
    private LoginUseCase $loginUseCase;
    private RegisterUseCase $registerUseCase;
    private LogoutUseCase $logoutUseCase;
    private JWTService $jwtService;
    private TokenBlacklistService $blacklistService;

    public function __construct(?Container $container = null)
    {
        if ($container !== null) {
            // Nova implementação com DI Container
            $this->loginUseCase = $container->get(LoginUseCase::class);
            $this->registerUseCase = $container->get(RegisterUseCase::class);
            $this->logoutUseCase = $container->get(LogoutUseCase::class);
            $this->jwtService = $container->get(JWTService::class);
            $this->blacklistService = $container->get(TokenBlacklistService::class);
        } else {
            // Implementação legacy para compatibilidade com testes existentes
            $this->initializeLegacyDependencies();
        }
    }

    /**
     * Inicialização legacy para compatibilidade
     * @deprecated Use Container-based initialization instead
     */
    private function initializeLegacyDependencies(): void
    {
        $database = DatabaseConfig::getConnection();
        $userRepository = new UserRepository($database);
        $eventPublisher = new EventPublisher();
        $requestValidator = new RequestValidator();
        $jwtConfig = new JWTConfig();

        // Inicializar serviços de token
        $blacklistRepository = new TokenBlacklistRepository($database);
        $this->blacklistService = new TokenBlacklistService($blacklistRepository);
        $this->jwtService = new JWTService($jwtConfig, $this->blacklistService, $userRepository);

        $this->loginUseCase = new LoginUseCase($userRepository, $this->jwtService, $eventPublisher);
        $this->registerUseCase = new RegisterUseCase($userRepository, $eventPublisher, $requestValidator);
        $this->logoutUseCase = new LogoutUseCase($this->jwtService);
    }

    public function login(): void
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);

            if (!isset($input['email']) || !isset($input['password'])) {
                throw new BadRequestException('Email e senha são obrigatórios');
            }

            $result = $this->loginUseCase->execute($input['email'], $input['password']);

            header('Content-Type: application/json');
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $result,
            ]);
        } catch (BadRequestException $e) {
            http_response_code($e->getStatusCode());
            header('Content-Type: application/json');
            echo json_encode($e->toArray());
        } catch (\Exception $e) {
            $exception = new InternalServerErrorException($e->getMessage());
            http_response_code($exception->getStatusCode());
            header('Content-Type: application/json');
            echo json_encode($exception->toArray());
        }
    }

    public function register(): void
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $request = new RequestUser($input);

            if (!$input) {
                throw new BadRequestException('Dados inválidos');
            }

            if (!$request->isValid()) {
                $exception = new UnprocessableEntityException('Erro de validação');
                http_response_code($exception->getStatusCode());
                echo json_encode([
                    'error' => true,
                    'message' => $exception->getMessage(),
                    'errors' => $request->errors(),
                ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

                return;
            }

            $result = $this->registerUseCase->execute($input);

            header('Content-Type: application/json');
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'data' => $result,
            ]);
        } catch (BadRequestException | UnprocessableEntityException $e) {
            http_response_code($e->getStatusCode());
            header('Content-Type: application/json');
            echo json_encode($e->toArray());
        } catch (\Exception $e) {
            $exception = new InternalServerErrorException($e->getMessage());
            http_response_code($exception->getStatusCode());
            header('Content-Type: application/json');
            echo json_encode($exception->toArray());
        }
    }

    public function refresh(): void
    {
        try {
            // Ler o refresh token do body da requisição
            $input = json_decode(file_get_contents('php://input'), true);

            if (!isset($input['refresh_token']) || empty($input['refresh_token'])) {
                throw new BadRequestException('Token de refresh não fornecido');
            }

            $refreshToken = $input['refresh_token'];

            $newToken = $this->jwtService->refreshToken($refreshToken);

            header('Content-Type: application/json');
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => [
                    'access_token' => $newToken,
                    'token_type' => 'Bearer',
                    'expires_in' => $_ENV['JWT_EXPIRATION'],
                ],
            ]);
        } catch (BadRequestException $e) {
            http_response_code($e->getStatusCode());
            header('Content-Type: application/json');
            echo json_encode($e->toArray());
        } catch (\Exception $e) {
            $exception = new InternalServerErrorException($e->getMessage());
            http_response_code($exception->getStatusCode());
            header('Content-Type: application/json');
            echo json_encode($exception->toArray());
        }
    }

    public function logout(): void
    {
        try {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
            
            if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
                throw new BadRequestException('Token não fornecido para logout');
            }

            $token = substr($authHeader, 7);
            $this->logoutUseCase->execute($token);

            header('Content-Type: application/json');
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Logout realizado com sucesso. Token invalidado.',
            ]);
        } catch (BadRequestException $e) {
            http_response_code($e->getStatusCode());
            header('Content-Type: application/json');
            echo json_encode($e->toArray());
        } catch (UnauthorizedException $e) {
            http_response_code($e->getStatusCode());
            header('Content-Type: application/json');
            echo json_encode($e->toArray());
        } catch (\Exception $e) {
            $exception = new InternalServerErrorException($e->getMessage());
            http_response_code($exception->getStatusCode());
            header('Content-Type: application/json');
            echo json_encode($exception->toArray());
        }
    }

    public function validate(): void
    {
        try {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;

            if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
                throw new UnauthorizedException('Token não fornecido');
            }

            $token = substr($authHeader, 7);
            $decoded = $this->jwtService->validateToken($token);

            header('Content-Type: application/json');
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
        } catch (UnauthorizedException $e) {
            http_response_code($e->getStatusCode());
            header('Content-Type: application/json');
            echo json_encode([
                'error' => true,
                'message' => $e->getMessage(),
                'valid' => false,
            ]);
        } catch (\Exception $e) {
            $exception = new UnauthorizedException('Token inválido. Faça login novamente.');
            http_response_code($exception->getStatusCode());
            header('Content-Type: application/json');
            echo json_encode([
                'error' => true,
                'message' => $exception->getMessage(),
                'valid' => false,
            ]);
        }
    }

    public function health(): void
    {
        header('Content-Type: application/json');
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'service' => 'auth-service',
            'status' => 'healthy',
            'timestamp' => date('Y-m-d H:i:s'),
        ]);
    }
}
