<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Database;

use PHPUnit\Framework\TestCase;
use App\Infrastructure\Database\TokenBlacklistRepository;
use PDO;
use PDOStatement;
use Mockery;

class TokenBlacklistRepositoryCoverageTest extends TestCase
{
    private TokenBlacklistRepository $repository;
    private PDO $mockPdo;
    private PDOStatement $mockStatement;

    protected function setUp(): void
    {
        $this->mockPdo = Mockery::mock(PDO::class);
        $this->mockStatement = Mockery::mock(PDOStatement::class);
        $this->repository = new TokenBlacklistRepository($this->mockPdo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function test_add_to_blacklist_with_different_timestamps(): void
    {
        $this->mockPdo->shouldReceive('prepare')
            ->once()
            ->andReturn($this->mockStatement);

        $this->mockStatement->shouldReceive('execute')
            ->once()
            ->with([
                'token_hash' => 'token123',
                'expires_at' => '2021-01-01 00:00:00'
            ]);

        $this->repository->addToBlacklist('token123', 1609459200);
        $this->assertTrue(true); // Assert that no exception was thrown
    }

    public function test_add_to_blacklist_with_current_timestamp(): void
    {
        $currentTime = time();
        $expectedDate = date('Y-m-d H:i:s', $currentTime);
        
        $this->mockPdo->shouldReceive('prepare')
            ->once()
            ->andReturn($this->mockStatement);

        $this->mockStatement->shouldReceive('execute')
            ->once()
            ->with([
                'token_hash' => 'current_token',
                'expires_at' => $expectedDate
            ]);

        $this->repository->addToBlacklist('current_token', $currentTime);
        $this->assertTrue(true);
    }

    public function test_is_token_blacklisted_returns_true(): void
    {
        $this->mockPdo->shouldReceive('prepare')
            ->once()
            ->andReturn($this->mockStatement);

        $this->mockStatement->shouldReceive('execute')
            ->once()
            ->with(['token_hash' => 'blacklisted_token']);

        $this->mockStatement->shouldReceive('fetchColumn')
            ->once()
            ->andReturn(1);

        $result = $this->repository->isTokenBlacklisted('blacklisted_token');
        $this->assertTrue($result);
    }

    public function test_is_token_blacklisted_returns_false(): void
    {
        $this->mockPdo->shouldReceive('prepare')
            ->once()
            ->andReturn($this->mockStatement);

        $this->mockStatement->shouldReceive('execute')
            ->once()
            ->with(['token_hash' => 'clean_token']);

        $this->mockStatement->shouldReceive('fetchColumn')
            ->once()
            ->andReturn(0);

        $result = $this->repository->isTokenBlacklisted('clean_token');
        $this->assertFalse($result);
    }

    public function test_clean_expired_tokens_returns_count(): void
    {
        $this->mockPdo->shouldReceive('prepare')
            ->once()
            ->andReturn($this->mockStatement);

        $this->mockStatement->shouldReceive('execute')
            ->once();

        $this->mockStatement->shouldReceive('rowCount')
            ->once()
            ->andReturn(5);

        $result = $this->repository->cleanExpiredTokens();
        $this->assertEquals(5, $result);
    }

    public function test_clean_expired_tokens_returns_zero(): void
    {
        $this->mockPdo->shouldReceive('prepare')
            ->once()
            ->andReturn($this->mockStatement);

        $this->mockStatement->shouldReceive('execute')
            ->once();

        $this->mockStatement->shouldReceive('rowCount')
            ->once()
            ->andReturn(0);

        $result = $this->repository->cleanExpiredTokens();
        $this->assertEquals(0, $result);
    }

    public function test_repository_constructor_sets_database(): void
    {
        $repository = new TokenBlacklistRepository($this->mockPdo);
        
        $reflection = new \ReflectionClass($repository);
        $databaseProperty = $reflection->getProperty('database');
        $databaseProperty->setAccessible(true);
        $database = $databaseProperty->getValue($repository);
        
        $this->assertSame($this->mockPdo, $database);
    }

    public function test_add_to_blacklist_with_special_characters(): void
    {
        $specialToken = 'token!@#$%^&*()_+-=[]{}|;:,.<>?';
        $currentTime = time();
        $expectedDate = date('Y-m-d H:i:s', $currentTime);
        
        $this->mockPdo->shouldReceive('prepare')
            ->once()
            ->andReturn($this->mockStatement);

        $this->mockStatement->shouldReceive('execute')
            ->once()
            ->with([
                'token_hash' => $specialToken,
                'expires_at' => $expectedDate
            ]);

        $this->repository->addToBlacklist($specialToken, $currentTime);
        $this->assertTrue(true);
    }

    public function test_is_token_blacklisted_with_various_tokens(): void
    {
        $tokens = ['token1', 'token2', 'very-long-token-123'];
        
        foreach ($tokens as $index => $token) {
            $this->mockPdo->shouldReceive('prepare')
                ->once()
                ->andReturn($this->mockStatement);

            $this->mockStatement->shouldReceive('execute')
                ->once()
                ->with(['token_hash' => $token]);

            $this->mockStatement->shouldReceive('fetchColumn')
                ->once()
                ->andReturn($index % 2); // Alternate between 0 and 1

            $result = $this->repository->isTokenBlacklisted($token);
            $this->assertEquals($index % 2 === 1, $result);
        }
    }

    public function test_multiple_clean_operations(): void
    {
        $expectedCounts = [3, 7, 0, 12];
        
        foreach ($expectedCounts as $count) {
            $this->mockPdo->shouldReceive('prepare')
                ->once()
                ->andReturn($this->mockStatement);

            $this->mockStatement->shouldReceive('execute')
                ->once();

            $this->mockStatement->shouldReceive('rowCount')
                ->once()
                ->andReturn($count);

            $result = $this->repository->cleanExpiredTokens();
            $this->assertEquals($count, $result);
        }
    }
}
