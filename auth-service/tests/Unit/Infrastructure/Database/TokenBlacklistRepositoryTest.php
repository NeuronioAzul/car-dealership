<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Database;

use App\Infrastructure\Database\TokenBlacklistRepository;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class TokenBlacklistRepositoryTest extends TestCase
{
    private TokenBlacklistRepository $repository;
    /** @var MockObject&PDO */
    private MockObject $pdoMock;
    /** @var MockObject&PDOStatement */
    private MockObject $statementMock;

    protected function setUp(): void
    {
        $this->pdoMock = $this->createMock(PDO::class);
        $this->statementMock = $this->createMock(PDOStatement::class);
        $this->repository = new TokenBlacklistRepository($this->pdoMock);
    }

    public function test_add_to_blacklist(): void
    {
        $tokenHash = 'abc123hash';
        $expiresAt = time() + 3600;

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with('INSERT INTO token_blacklist (token_hash, expires_at, created_at) VALUES (:token_hash, :expires_at, NOW())')
            ->willReturn($this->statementMock);

        $this->statementMock->expects($this->once())
            ->method('execute')
            ->with([
                'token_hash' => $tokenHash,
                'expires_at' => date('Y-m-d H:i:s', $expiresAt),
            ]);

        $this->repository->addToBlacklist($tokenHash, $expiresAt);
    }

    public function test_is_token_blacklisted_returns_true(): void
    {
        $tokenHash = 'blacklisted-token-hash';

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($this->statementMock);

        $this->statementMock->expects($this->once())
            ->method('execute')
            ->with(['token_hash' => $tokenHash]);

        $this->statementMock->expects($this->once())
            ->method('fetchColumn')
            ->willReturn(1);

        $result = $this->repository->isTokenBlacklisted($tokenHash);

        $this->assertTrue($result);
    }

    public function test_is_token_blacklisted_returns_false(): void
    {
        $tokenHash = 'valid-token-hash';

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($this->statementMock);

        $this->statementMock->expects($this->once())
            ->method('execute')
            ->with(['token_hash' => $tokenHash]);

        $this->statementMock->expects($this->once())
            ->method('fetchColumn')
            ->willReturn(0);

        $result = $this->repository->isTokenBlacklisted($tokenHash);

        $this->assertFalse($result);
    }

    public function test_clean_expired_tokens(): void
    {
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with('DELETE FROM token_blacklist WHERE expires_at <= NOW()')
            ->willReturn($this->statementMock);

        $this->statementMock->expects($this->once())
            ->method('execute');

        $this->statementMock->expects($this->once())
            ->method('rowCount')
            ->willReturn(5);

        $result = $this->repository->cleanExpiredTokens();

        $this->assertEquals(5, $result);
    }

    public function test_clean_expired_tokens_with_no_tokens_removed(): void
    {
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with('DELETE FROM token_blacklist WHERE expires_at <= NOW()')
            ->willReturn($this->statementMock);

        $this->statementMock->expects($this->once())
            ->method('execute');

        $this->statementMock->expects($this->once())
            ->method('rowCount')
            ->willReturn(0);

        $result = $this->repository->cleanExpiredTokens();

        $this->assertEquals(0, $result);
    }
}
