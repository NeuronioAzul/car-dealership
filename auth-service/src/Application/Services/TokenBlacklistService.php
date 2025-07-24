<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Infrastructure\Database\TokenBlacklistRepository;

class TokenBlacklistService
{
    private TokenBlacklistRepository $repository;

    public function __construct(TokenBlacklistRepository $repository)
    {
        $this->repository = $repository;
    }

    public function revokeToken(string $token): void
    {
        // Decodificar o token para obter o tempo de expiração
        $tokenParts = explode('.', $token);

        if (count($tokenParts) !== 3) {
            throw new \Exception('Token inválido', 400);
        }

        $payload = json_decode(base64_decode($tokenParts[1]), true);

        if (!$payload || !isset($payload['exp'])) {
            throw new \Exception('Token inválido', 400);
        }

        // Criar hash do token para armazenar (por segurança, não armazenamos o token completo)
        $tokenHash = hash('sha256', $token);

        // Adicionar à blacklist com o tempo de expiração do token
        $this->repository->addToBlacklist($tokenHash, $payload['exp']);
    }

    public function isTokenRevoked(string $token): bool
    {
        $tokenHash = hash('sha256', $token);

        return $this->repository->isTokenBlacklisted($tokenHash);
    }

    public function cleanExpiredTokens(): int
    {
        return $this->repository->cleanExpiredTokens();
    }
}
