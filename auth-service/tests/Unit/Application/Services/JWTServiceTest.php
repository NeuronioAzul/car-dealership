<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Services;

use Tests\TestCase;
use App\Application\Services\JWTService;
use App\Application\Services\TokenBlacklistService;
use App\Domain\Entities\User;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Infrastructure\Config\JWTConfig;
use PHPUnit\Framework\MockObject\MockObject;

class JWTServiceTest extends TestCase
{
    private JWTService $jwtService;
    private JWTConfig $config;
    private TokenBlacklistService|MockObject $blacklistService;
    private UserRepositoryInterface|MockObject $userRepository;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Configurar variáveis de ambiente para o teste
        $_ENV['JWT_SECRET'] = 'test-secret-key-for-testing-purposes';
        $_ENV['JWT_ALGORITHM'] = 'HS256';
        $_ENV['JWT_EXPIRATION'] = '3600';
        $_ENV['JWT_REFRESH_EXPIRATION'] = '86400';

        $this->config = new JWTConfig();

        $this->blacklistService = $this->createMock(TokenBlacklistService::class);
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        
        $this->jwtService = new JWTService(
            $this->config,
            $this->blacklistService,
            $this->userRepository
        );

        $this->user = new User(
            'João Silva',
            'joao@test.com',
            'password123',
            '11999999999',
            new \DateTime('1990-01-01'),
            'customer',
            true,
            true,
            true
        );
    }

    public function testGenerateToken(): void
    {
        $token = $this->jwtService->generateToken($this->user);

        $this->assertIsString($token);
        $this->assertNotEmpty($token);
        
        // Verificar se é um JWT válido (3 partes separadas por ponto)
        $parts = explode('.', $token);
        $this->assertCount(3, $parts);
    }

    public function testGenerateRefreshToken(): void
    {
        $refreshToken = $this->jwtService->generateRefreshToken($this->user);

        $this->assertIsString($refreshToken);
        $this->assertNotEmpty($refreshToken);
        
        // Verificar se é um JWT válido
        $parts = explode('.', $refreshToken);
        $this->assertCount(3, $parts);
    }

    public function testValidateToken(): void
    {
        $this->blacklistService->expects($this->once())
            ->method('isTokenRevoked')
            ->willReturn(false);

        $token = $this->jwtService->generateToken($this->user);
        $decoded = $this->jwtService->validateToken($token);

        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('sub', $decoded);
        $this->assertArrayHasKey('email', $decoded);
        $this->assertArrayHasKey('role', $decoded);
        $this->assertEquals($this->user->getId(), $decoded['sub']);
        $this->assertEquals($this->user->getEmail(), $decoded['email']);
        $this->assertEquals($this->user->getRole(), $decoded['role']);
    }

    public function testValidateTokenWithRevokedToken(): void
    {
        $this->blacklistService->expects($this->once())
            ->method('isTokenRevoked')
            ->willReturn(true);

        $token = $this->jwtService->generateToken($this->user);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Token foi revogado');
        $this->expectExceptionCode(401);

        $this->jwtService->validateToken($token);
    }

    public function testValidateTokenWithInvalidToken(): void
    {
        $this->blacklistService->expects($this->once())
            ->method('isTokenRevoked')
            ->willReturn(false);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/Token inválido/');
        $this->expectExceptionCode(401);

        $this->jwtService->validateToken('invalid-token');
    }

    public function testRefreshToken(): void
    {
        $this->userRepository->expects($this->once())
            ->method('findById')
            ->with($this->user->getId())
            ->willReturn($this->user);

        $refreshToken = $this->jwtService->generateRefreshToken($this->user);
        $newToken = $this->jwtService->refreshToken($refreshToken);

        $this->assertIsString($newToken);
        $this->assertNotEmpty($newToken);
        $this->assertNotEquals($refreshToken, $newToken);
    }

    public function testRefreshTokenWithInvalidType(): void
    {
        $regularToken = $this->jwtService->generateToken($this->user);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Token de refresh inválido');
        $this->expectExceptionCode(401);

        $this->jwtService->refreshToken($regularToken);
    }

    public function testRefreshTokenWithInvalidToken(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/Token de refresh inválido/');
        $this->expectExceptionCode(401);

        $this->jwtService->refreshToken('invalid-refresh-token');
    }

    public function testRefreshTokenWithoutUserRepository(): void
    {
        // Criar JWTService sem UserRepository
        $jwtServiceWithoutRepo = new JWTService($this->config, $this->blacklistService);
        
        $refreshToken = $jwtServiceWithoutRepo->generateRefreshToken($this->user);
        $newToken = $jwtServiceWithoutRepo->refreshToken($refreshToken);

        $this->assertIsString($newToken);
        $this->assertNotEmpty($newToken);
    }

    public function testRefreshTokenWithUserNotFound(): void
    {
        $this->userRepository->expects($this->once())
            ->method('findById')
            ->with($this->user->getId())
            ->willThrowException(new \Exception('User not found'));

        $refreshToken = $this->jwtService->generateRefreshToken($this->user);
        $newToken = $this->jwtService->refreshToken($refreshToken);

        // Deve funcionar mesmo quando o usuário não é encontrado
        $this->assertIsString($newToken);
        $this->assertNotEmpty($newToken);
    }

    public function testExtractUserIdFromToken(): void
    {
        $this->blacklistService->expects($this->once())
            ->method('isTokenRevoked')
            ->willReturn(false);

        $token = $this->jwtService->generateToken($this->user);
        $userId = $this->jwtService->extractUserIdFromToken($token);

        $this->assertEquals($this->user->getId(), $userId);
    }

    public function testExtractUserRoleFromToken(): void
    {
        $this->blacklistService->expects($this->once())
            ->method('isTokenRevoked')
            ->willReturn(false);

        $token = $this->jwtService->generateToken($this->user);
        $role = $this->jwtService->extractUserRoleFromToken($token);

        $this->assertEquals($this->user->getRole(), $role);
    }

    public function testExtractUserRoleFromTokenWithoutRole(): void
    {
        $this->blacklistService->expects($this->once())
            ->method('isTokenRevoked')
            ->willReturn(false);

        // Criar token manualmente sem role
        $payload = [
            'iss' => 'car-dealership-issuer',
            'sub' => $this->user->getId(),
            'email' => $this->user->getEmail(),
            'iat' => time(),
            'exp' => time() + 3600,
        ];

        $token = \Firebase\JWT\JWT::encode($payload, $this->config->secret, $this->config->algorithm);
        $role = $this->jwtService->extractUserRoleFromToken($token);

        $this->assertEquals('customer', $role);
    }

    public function testRevokeToken(): void
    {
        $token = $this->jwtService->generateToken($this->user);

        $this->blacklistService->expects($this->once())
            ->method('revokeToken')
            ->with($token);

        $this->jwtService->revokeToken($token);
    }

    public function testRevokeTokenWithoutBlacklistService(): void
    {
        $jwtServiceWithoutBlacklist = new JWTService($this->config);
        $token = $jwtServiceWithoutBlacklist->generateToken($this->user);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Serviço de blacklist não está disponível');
        $this->expectExceptionCode(500);

        $jwtServiceWithoutBlacklist->revokeToken($token);
    }

    public function testIsTokenRevoked(): void
    {
        $token = $this->jwtService->generateToken($this->user);

        $this->blacklistService->expects($this->once())
            ->method('isTokenRevoked')
            ->with($token)
            ->willReturn(true);

        $isRevoked = $this->jwtService->isTokenRevoked($token);

        $this->assertTrue($isRevoked);
    }

    public function testIsTokenRevokedWithoutBlacklistService(): void
    {
        $jwtServiceWithoutBlacklist = new JWTService($this->config);
        $token = $jwtServiceWithoutBlacklist->generateToken($this->user);

        $isRevoked = $jwtServiceWithoutBlacklist->isTokenRevoked($token);

        $this->assertFalse($isRevoked);
    }
}
