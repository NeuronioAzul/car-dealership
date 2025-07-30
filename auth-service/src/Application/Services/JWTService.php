<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Domain\Entities\User;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Infrastructure\Config\JWTConfig;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTService
{
    private JWTConfig $config;
    private ?TokenBlacklistService $blacklistService;
    private ?UserRepositoryInterface $userRepository;

    public function __construct(
        JWTConfig $config,
        ?TokenBlacklistService $blacklistService = null, 
        ?UserRepositoryInterface $userRepository = null
    ) {
        $this->config = $config;
        $this->blacklistService = $blacklistService;
        $this->userRepository = $userRepository;
    }

    public function generateToken(User $user): string
    {
        $payload = [
            'iss' => 'car-dealership-issuer',
            'sub' => $user->getId(),
            'email' => $user->getEmail(),
            'role' => $user->getRole(),
            'iat' => time(),
            'exp' => time() + $this->config->expiration,
        ];

        return JWT::encode($payload, $this->config->secret, $this->config->algorithm);
    }

    public function generateRefreshToken(User $user): string
    {
        $payload = [
            'iss' => 'car-dealership-issuer',
            'sub' => $user->getId(),
            'type' => 'refresh',
            'iat' => time(),
            'exp' => time() + $this->config->refreshExpiration,
        ];

        return JWT::encode($payload, $this->config->secret, $this->config->algorithm);
    }

    public function validateToken(string $token): array
    {
        try {
            // Verificar se o token está na blacklist antes de validar
            if ($this->blacklistService && $this->blacklistService->isTokenRevoked($token)) {
                throw new \Exception('Token foi revogado', 401);
            }

            $decoded = JWT::decode($token, new Key($this->config->secret, $this->config->algorithm));

            return (array) $decoded;
        } catch (\Exception $e) {
            throw new \Exception('Token inválido: ' . $e->getMessage(), 401);
        }
    }

    public function refreshToken(string $refreshToken): string
    {
        try {
            $decoded = JWT::decode($refreshToken, new Key($this->config->secret, $this->config->algorithm));

            if (!isset($decoded->type) || $decoded->type !== 'refresh') {
                throw new \Exception('Token de refresh inválido', 401);
            }

            // Buscar informações atualizadas do usuário se o repositório estiver disponível
            $payload = [
                'iss' => 'car-dealership-issuer',
                'sub' => $decoded->sub,
                'iat' => time(),
                'exp' => time() + $this->config->expiration,
            ];

            // Se temos acesso ao repositório de usuários, buscar informações atualizadas
            if ($this->userRepository) {
                try {
                    $user = $this->userRepository->findById($decoded->sub);

                    if ($user) {
                        $payload['email'] = $user->getEmail();
                        $payload['role'] = $user->getRole();
                    }
                } catch (\Exception $e) {
                    // Se falhar ao buscar o usuário, gerar token apenas com sub
                    // O token ainda será válido mas sem email/role
                }
            }

            return JWT::encode($payload, $this->config->secret, $this->config->algorithm);
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
