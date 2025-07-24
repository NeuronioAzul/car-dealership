<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Domain\Entities\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTService
{
    private string $secret;
    private string $algorithm;
    private int $expiration;
    private ?TokenBlacklistService $blacklistService;

    public function __construct(?TokenBlacklistService $blacklistService = null)
    {
        $this->secret = $_ENV['JWT_SECRET'];
        $this->algorithm = $_ENV['JWT_ALGORITHM'] ?? 'HS256';
        $this->expiration = (int) $_ENV['JWT_EXPIRATION'];
        $this->blacklistService = $blacklistService;
    }

    public function generateToken(User $user): string
    {
        $payload = [
            'iss' => 'car-dealership-issuer',
            'sub' => $user->getId(),
            'email' => $user->getEmail(),
            'role' => $user->getRole(),
            'iat' => time(),
            'exp' => time() + $this->expiration,
        ];

        return JWT::encode($payload, $this->secret, $this->algorithm);
    }

    public function generateRefreshToken(User $user): string
    {
        $payload = [
            'iss' => 'car-dealership-auth',
            'sub' => $user->getId(),
            'type' => 'refresh',
            'iat' => time(),
            'exp' => time() + (7 * 24 * 60 * 60), // 7 dias
        ];

        return JWT::encode($payload, $this->secret, $this->algorithm);
    }

    public function validateToken(string $token): array
    {
        try {
            // Verificar se o token está na blacklist antes de validar
            if ($this->blacklistService && $this->blacklistService->isTokenRevoked($token)) {
                throw new \Exception('Token foi revogado', 401);
            }

            $decoded = JWT::decode($token, new Key($this->secret, $this->algorithm));

            return (array) $decoded;
        } catch (\Exception $e) {
            throw new \Exception('Token inválido: ' . $e->getMessage(), 401);
        }
    }

    public function refreshToken(string $refreshToken): string
    {
        try {
            $decoded = JWT::decode($refreshToken, new Key($this->secret, $this->algorithm));

            if (!isset($decoded->type) || $decoded->type !== 'refresh') {
                throw new \Exception('Token de refresh inválido', 401);
            }

            // Gerar novo token de acesso
            $payload = [
                'iss' => 'car-dealership-auth',
                'sub' => $decoded->sub,
                'iat' => time(),
                'exp' => time() + $this->expiration,
            ];

            return JWT::encode($payload, $this->secret, $this->algorithm);
        } catch (\Exception $e) {
            throw new \Exception('Token de refresh inválido: ' . $e->getMessage(), 401);
        }
    }

    public function extractUserIdFromToken(string $token): string
    {
        $decoded = $this->validateToken($token);

        return $decoded['sub'];
    }

    public function extractUserRoleFromToken(string $token): string
    {
        $decoded = $this->validateToken($token);

        return $decoded['role'] ?? 'customer';
    }

    public function revokeToken(string $token): void
    {
        if (!$this->blacklistService) {
            throw new \Exception('Serviço de blacklist não está disponível', 500);
        }

        $this->blacklistService->revokeToken($token);
    }

    public function isTokenRevoked(string $token): bool
    {
        if (!$this->blacklistService) {
            return false;
        }

        return $this->blacklistService->isTokenRevoked($token);
    }
}
