<?php

namespace Tests\Unit\Auth;

use PHPUnit\Framework\TestCase;

// Incluir classe do JWT Service
require_once __DIR__ . '/../../../auth-service/src/Application/Services/JWTService.php';

use App\Application\Services\JWTService;

class JWTServiceTest extends TestCase
{
    private JWTService $jwtService;
    private array $testPayload;

    protected function setUp(): void
    {
        $this->jwtService = new JWTService();
        $this->testPayload = [
            'user_id' => 'test-user-id',
            'email' => 'test@email.com',
            'role' => 'customer'
        ];
    }

    public function testTokenGeneration(): void
    {
        $token = $this->jwtService->generateToken($this->testPayload);
        
        $this->assertIsString($token);
        $this->assertNotEmpty($token);
        
        // Verificar se o token tem 3 partes separadas por ponto
        $parts = explode('.', $token);
        $this->assertCount(3, $parts);
    }

    public function testTokenValidation(): void
    {
        $token = $this->jwtService->generateToken($this->testPayload);
        
        $isValid = $this->jwtService->validateToken($token);
        $this->assertTrue($isValid);
    }

    public function testTokenDecoding(): void
    {
        $token = $this->jwtService->generateToken($this->testPayload);
        
        $decodedPayload = $this->jwtService->decodeToken($token);
        
        $this->assertIsArray($decodedPayload);
        $this->assertEquals($this->testPayload['user_id'], $decodedPayload['user_id']);
        $this->assertEquals($this->testPayload['email'], $decodedPayload['email']);
        $this->assertEquals($this->testPayload['role'], $decodedPayload['role']);
    }

    public function testInvalidTokenValidation(): void
    {
        $invalidToken = 'invalid.token.here';
        
        $isValid = $this->jwtService->validateToken($invalidToken);
        $this->assertFalse($isValid);
    }

    public function testExpiredTokenValidation(): void
    {
        // Gerar token com expiração muito curta
        $shortLivedToken = $this->jwtService->generateToken($this->testPayload, 1); // 1 segundo
        
        // Aguardar expiração
        sleep(2);
        
        $isValid = $this->jwtService->validateToken($shortLivedToken);
        $this->assertFalse($isValid);
    }

    public function testRefreshTokenGeneration(): void
    {
        $refreshToken = $this->jwtService->generateRefreshToken($this->testPayload);
        
        $this->assertIsString($refreshToken);
        $this->assertNotEmpty($refreshToken);
        
        // Refresh token deve ser diferente do access token
        $accessToken = $this->jwtService->generateToken($this->testPayload);
        $this->assertNotEquals($accessToken, $refreshToken);
    }

    public function testTokenRefresh(): void
    {
        $originalToken = $this->jwtService->generateToken($this->testPayload);
        $refreshToken = $this->jwtService->generateRefreshToken($this->testPayload);
        
        $newToken = $this->jwtService->refreshToken($refreshToken);
        
        $this->assertIsString($newToken);
        $this->assertNotEquals($originalToken, $newToken);
        
        // Verificar se o novo token é válido
        $this->assertTrue($this->jwtService->validateToken($newToken));
    }

    public function testTokenWithCustomExpiration(): void
    {
        $customExpiration = 7200; // 2 horas
        $token = $this->jwtService->generateToken($this->testPayload, $customExpiration);
        
        $decodedPayload = $this->jwtService->decodeToken($token);
        
        $this->assertArrayHasKey('exp', $decodedPayload);
        $this->assertGreaterThan(time(), $decodedPayload['exp']);
    }

    public function testTokenWithAdditionalClaims(): void
    {
        $additionalClaims = [
            'permissions' => ['read', 'write'],
            'department' => 'sales'
        ];
        
        $payloadWithClaims = array_merge($this->testPayload, $additionalClaims);
        $token = $this->jwtService->generateToken($payloadWithClaims);
        
        $decodedPayload = $this->jwtService->decodeToken($token);
        
        $this->assertEquals($additionalClaims['permissions'], $decodedPayload['permissions']);
        $this->assertEquals($additionalClaims['department'], $decodedPayload['department']);
    }

    public function testGetUserFromToken(): void
    {
        $token = $this->jwtService->generateToken($this->testPayload);
        
        $userData = $this->jwtService->getUserFromToken($token);
        
        $this->assertIsArray($userData);
        $this->assertEquals($this->testPayload['user_id'], $userData['user_id']);
        $this->assertEquals($this->testPayload['email'], $userData['email']);
        $this->assertEquals($this->testPayload['role'], $userData['role']);
    }
}

