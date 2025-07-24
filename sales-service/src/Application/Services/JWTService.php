<?php

declare(strict_types=1);

namespace App\Application\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTService
{
    private string $secret;
    private string $algorithm;

    public function __construct()
    {
        $this->secret = $_ENV['JWT_SECRET'];
        $this->algorithm = 'HS256';
    }

    public function validateToken(string $token): array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, $this->algorithm));

            return (array) $decoded;
        } catch (\Exception $e) {
            throw new \Exception('Token inválido: ' . $e->getMessage(), 401);
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
}
