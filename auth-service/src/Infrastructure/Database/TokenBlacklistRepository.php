<?php

namespace App\Infrastructure\Database;

use PDO;

class TokenBlacklistRepository
{
    private PDO $database;

    public function __construct(PDO $database)
    {
        $this->database = $database;
    }

    public function addToBlacklist(string $tokenHash, int $expiresAt): void
    {
        $sql = "INSERT INTO token_blacklist (token_hash, expires_at, created_at) VALUES (:token_hash, :expires_at, NOW())";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([
            'token_hash' => $tokenHash,
            'expires_at' => date('Y-m-d H:i:s', $expiresAt)
        ]);
    }

    public function isTokenBlacklisted(string $tokenHash): bool
    {
        $sql = "SELECT COUNT(*) FROM token_blacklist WHERE token_hash = :token_hash AND expires_at > NOW()";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute(['token_hash' => $tokenHash]);
        
        return $stmt->fetchColumn() > 0;
    }

    public function cleanExpiredTokens(): int
    {
        $sql = "DELETE FROM token_blacklist WHERE expires_at <= NOW()";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute();
        
        return $stmt->rowCount();
    }

    public function revokeAllUserTokens(string $userId): void
    {
        // Esta implementação seria mais complexa, requerendo armazenar mais informações
        // Por simplicidade, vamos focar apenas no token atual por enquanto
    }
}
