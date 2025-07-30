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
        try {
            // Validar se o token é válido antes de revogar
            $this->jwtService->validateToken($token);
            
            // Revogar o token
            $this->jwtService->revokeToken($token);
        } catch (\Exception $e) {
            // Se o token já estiver inválido/revogado, lançar exceção específica
            if (str_contains($e->getMessage(), 'revogado') || str_contains($e->getMessage(), 'inválido')) {
                throw new \Exception('Token já foi invalidado ou é inválido', 401);
            }
            throw $e;
        }
    }
}
