<?php

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
        $decoded = $this->jwtService->validateToken($token);
        
        return [
            'user_id' => $decoded['sub'],
            'email' => $decoded['email'] ?? null,
            'role' => $decoded['role'] ?? 'customer'
        ];
    }

    public function requireCustomer(): array
    {
        $user = $this->authenticate();
        
        if ($user['role'] !== 'customer') {
            throw new \Exception('Acesso negado. Apenas clientes podem acessar este recurso', 403);
        }
        
        return $user;
    }

    public function requireAdmin(): array
    {
        $user = $this->authenticate();
        
        if ($user['role'] !== 'admin') {
            throw new \Exception('Acesso negado. Apenas administradores podem acessar este recurso', 403);
        }
        
        return $user;
    }
}

