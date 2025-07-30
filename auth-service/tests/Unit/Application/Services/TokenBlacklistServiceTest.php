<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Services;

use Tests\TestCase;
use App\Application\Services\TokenBlacklistService;
use App\Infrastructure\Database\TokenBlacklistRepository;
use PHPUnit\Framework\MockObject\MockObject;

class TokenBlacklistServiceTest extends TestCase
{
    private TokenBlacklistService $blacklistService;
    /** @var MockObject&TokenBlacklistRepository */
    private MockObject $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(TokenBlacklistRepository::class);
        $this->blacklistService = new TokenBlacklistService($this->repository);
    }

    public function testRevokeToken(): void
    {
        // Criar um JWT válido para o teste
        $payload = [
            'iss' => 'test',
            'sub' => 'user123',
            'exp' => time() + 3600,
            'iat' => time()
        ];

        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payloadJson = json_encode($payload);
        
        $token = base64_encode($header) . '.' . 
                base64_encode($payloadJson) . '.' . 
                base64_encode('signature');

        $expectedHash = hash('sha256', $token);

        $this->repository->expects($this->once())
            ->method('addToBlacklist')
            ->with($expectedHash, $payload['exp']);

        $this->blacklistService->revokeToken($token);
    }

    public function testRevokeTokenWithInvalidFormat(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Token inválido');
        $this->expectExceptionCode(400);

        $this->blacklistService->revokeToken('invalid.token');
    }

    public function testRevokeTokenWithInvalidPayload(): void
    {
        $invalidToken = base64_encode('header') . '.' . 
                       base64_encode('invalid-json') . '.' . 
                       base64_encode('signature');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Token inválido');
        $this->expectExceptionCode(400);

        $this->blacklistService->revokeToken($invalidToken);
    }

    public function testRevokeTokenWithoutExpiration(): void
    {
        $payload = [
            'iss' => 'test',
            'sub' => 'user123',
            'iat' => time()
            // exp ausente
        ];

        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payloadJson = json_encode($payload);
        
        $token = base64_encode($header) . '.' . 
                base64_encode($payloadJson) . '.' . 
                base64_encode('signature');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Token inválido');
        $this->expectExceptionCode(400);

        $this->blacklistService->revokeToken($token);
    }

    public function testIsTokenRevoked(): void
    {
        $token = 'valid.jwt.token';
        $expectedHash = hash('sha256', $token);

        $this->repository->expects($this->once())
            ->method('isTokenBlacklisted')
            ->with($expectedHash)
            ->willReturn(true);

        $result = $this->blacklistService->isTokenRevoked($token);

        $this->assertTrue($result);
    }

    public function testIsTokenNotRevoked(): void
    {
        $token = 'valid.jwt.token';
        $expectedHash = hash('sha256', $token);

        $this->repository->expects($this->once())
            ->method('isTokenBlacklisted')
            ->with($expectedHash)
            ->willReturn(false);

        $result = $this->blacklistService->isTokenRevoked($token);

        $this->assertFalse($result);
    }

    public function testCleanExpiredTokens(): void
    {
        $expectedCount = 5;

        $this->repository->expects($this->once())
            ->method('cleanExpiredTokens')
            ->willReturn($expectedCount);

        $result = $this->blacklistService->cleanExpiredTokens();

        $this->assertEquals($expectedCount, $result);
    }
}
