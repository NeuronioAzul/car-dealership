<?php

declare(strict_types=1);

namespace App\Presentation\Middleware;

use App\Application\Services\JWTService;

class AuthMiddleware
{
    private JWTService $jwtService;

    public function __construct()
    {
        $this->jwtService = new JWTService();
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
            $decoded = $this->jwtService->validateToken($token);
        } catch (\Exception $e) {
            // Tratar diferentes tipos de erro de JWT de forma amigável
            $message = $e->getMessage();

            if (str_contains($message, 'Expired token')) {
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

        return [
            'user_id' => $decoded['sub'],
            'email' => $decoded['email'] ?? null,
            'role' => $decoded['role'] ?? 'customer',
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
