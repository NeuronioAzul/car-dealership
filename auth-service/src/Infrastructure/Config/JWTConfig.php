<?php

declare(strict_types=1);

namespace App\Infrastructure\Config;

/**
 * Configuração tipada para JWT
 * 
 * Resolve o problema de acessar $_ENV diretamente
 */
class JWTConfig
{
    public readonly string $secret;
    public readonly string $algorithm;
    public readonly int $expiration;
    public readonly int $refreshExpiration;

    public function __construct()
    {
        $this->secret = $_ENV['JWT_SECRET'] ?? throw new \InvalidArgumentException('JWT_SECRET not found');
        $this->algorithm = $_ENV['JWT_ALGORITHM'] ?? 'HS256';
        $this->expiration = (int) ($_ENV['JWT_EXPIRATION'] ?? 3600);
        $this->refreshExpiration = (int) ($_ENV['JWT_REFRESH_EXPIRATION'] ?? 604800); // 7 days
    }
}
