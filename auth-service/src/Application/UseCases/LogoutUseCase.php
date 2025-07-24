<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Application\Services\JWTService;

class LogoutUseCase
{
    private JWTService $jwtService;

    public function __construct(JWTService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    public function execute(string $token): void
    {
        // Validar se o token é válido antes de revogar
        $this->jwtService->validateToken($token);

        // Revogar o token
        $this->jwtService->revokeToken($token);
    }
}
