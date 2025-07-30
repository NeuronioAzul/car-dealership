<?php

declare(strict_types=1);

namespace App\Presentation\Middleware;

use App\Application\Services\JWTService;
use App\Application\Services\TokenBlacklistService;
use App\Infrastructure\Config\JWTConfig;
use App\Infrastructure\Database\DatabaseConfig;
use App\Infrastructure\Database\TokenBlacklistRepository;
use App\Infrastructure\Database\UserRepository;

class AuthMiddleware
{
    private JWTService $jwtService;

    public function __construct()
    {
        // Inicialização legacy para compatibilidade
        $database = DatabaseConfig::getConnection();
        $userRepository = new UserRepository($database);
        $blacklistRepository = new TokenBlacklistRepository($database);
        $blacklistService = new TokenBlacklistService($blacklistRepository);
        $jwtConfig = new JWTConfig();
        
        $this->jwtService = new JWTService($jwtConfig, $blacklistService, $userRepository);
    }

    public function authenticate(): array
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            throw new \Exception('Token de autenticação não fornecido', 401);
        }

        $token = substr($authHeader, 7);

        try {
            // Validar token através do auth-service para verificar blacklist
            $validationResult = $this->validateTokenWithAuthService($authHeader);

            if (!$validationResult['valid']) {
                throw new \Exception('Token inválido ou revogado', 401);
            }

            return $validationResult;
        } catch (\Exception $e) {
            // Tratar diferentes tipos de erro de JWT de forma amigável
            $message = $e->getMessage();

            if (str_contains($message, 'revogado') || str_contains($message, 'invalidado')) {
                throw new \Exception('Token foi invalidado. Faça login novamente para continuar.', 401);
            }

            if (str_contains($message, 'Expired token') || str_contains($message, 'expirado')) {
                throw new \Exception('Token expirado. Faça login novamente para continuar.', 401);
            }

            if (str_contains($message, 'Invalid token') || str_contains($message, 'Token inválido')) {
                throw new \Exception('Token inválido. Faça login novamente para continuar.', 401);
            }

            if (str_contains($message, 'Signature verification failed')) {
                throw new \Exception('Token inválido. Faça login novamente para continuar.', 401);
            }

            // Para qualquer outro erro de JWT
            throw new \Exception('Token inválido. Faça login novamente para continuar.', 401);
        }
    }

    private function validateTokenWithAuthService(string $authHeader): array
    {
        $authServiceUrl = $_ENV['AUTH_SERVICE_URL'] ?? 'http://auth-service:80';
        $url = $authServiceUrl . '/api/v1/auth/validate';

        $options = [
            'http' => [
                'method' => 'POST',
                'header' => [
                    'Content-Type: application/json',
                    'Authorization: ' . $authHeader,
                ],
                'content' => json_encode([]),
                'timeout' => 5,
            ],
        ];

        $context = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            throw new \Exception('Erro ao validar token com serviço de autenticação', 500);
        }

        $responseData = json_decode($response, true);

        if (!$responseData || !isset($responseData['success']) || !$responseData['success']) {
            $message = $responseData['message'] ?? 'Token inválido';

            throw new \Exception($message, 401);
        }

        return [
            'valid' => true,
            'user_id' => $responseData['data']['user_id'],
            'email' => $responseData['data']['email'],
            'role' => $responseData['data']['role'],
        ];
    }

    public function requireCustomer(): array
    {
        try {
            $user = $this->authenticate();
        } catch (\Exception $e) {
            // Re-lançar exceções de autenticação com suas mensagens já tratadas
            throw $e;
        }

        if ($user['role'] !== 'customer') {
            throw new \Exception('Acesso negado. Apenas clientes podem acessar este recurso.', 403);
        }

        return $user;
    }

    public function requireAdmin(): array
    {
        try {
            $user = $this->authenticate();
        } catch (\Exception $e) {
            // Re-lançar exceções de autenticação com suas mensagens já tratadas
            throw $e;
        }

        if ($user['role'] !== 'admin') {
            throw new \Exception('Acesso negado. Apenas administradores podem acessar este recurso.', 403);
        }

        return $user;
    }

    public function isAuthenticated(): bool
    {
        try {
            $this->authenticate();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function isAdmin(): bool
    {
        try {
            $user = $this->authenticate();

            return $user['role'] === 'admin';
        } catch (\Exception $e) {
            return false;
        }
    }
}
