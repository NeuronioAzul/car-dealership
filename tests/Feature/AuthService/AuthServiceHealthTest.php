<?php

namespace Tests\Feature\AuthService;

use Tests\TestCase;

/**
 * Testes básicos para verificar se o auth-service está funcionando
 */
class AuthServiceHealthTest extends TestCase
{
    /**
     * Verifica se o serviço de autenticação está respondendo
     */
    public function testAuthServiceIsRunning(): void
    {
        if (!$this->isServiceRunning($this->authServiceUrl)) {
            $this->markTestSkipped('Auth service não está disponível');
        }

        $response = $this->makeRequest("{$this->authServiceUrl}/health");
        
        $this->assertEquals(200, $response['code'], 'Auth service deve estar rodando');
        $this->assertTrue($response['body']['success']);
        $this->assertEquals('auth-service', $response['body']['service']);
        $this->assertEquals('healthy', $response['body']['status']);
    }

    /**
     * Testa se é possível fazer login com credenciais válidas
     */
    public function testCanLoginWithValidCredentials(): void
    {
        if (!$this->isServiceRunning($this->authServiceUrl)) {
            $this->markTestSkipped('Auth service não está disponível');
        }

        $response = $this->makeRequest(
            "{$this->authServiceUrl}/login",
            'POST',
            ['email' => 'admin@concessionaria.com', 'password' => 'admin123']
        );

        $this->assertEquals(200, $response['code'], 'Login deve ser bem-sucedido');
        $this->assertTrue($response['body']['success']);
        $this->assertArrayHasKey('access_token', $response['body']['data']);
        $this->assertNotEmpty($response['body']['data']['access_token']);
        $this->assertArrayHasKey('refresh_token', $response['body']['data']);
        $this->assertNotEmpty($response['body']['data']['refresh_token']);
    }

    /**
     * Testa login com credenciais inválidas
     */
    public function testCannotLoginWithInvalidCredentials(): void
    {
        if (!$this->isServiceRunning($this->authServiceUrl)) {
            $this->markTestSkipped('Auth service não está disponível');
        }

        $response = $this->makeRequest(
            "{$this->authServiceUrl}/login",
            'POST',
            ['email' => 'invalid@example.com', 'password' => 'wrongpassword']
        );

        $this->assertEquals(401, $response['code'], 'Login com credenciais inválidas deve falhar');
        $this->assertTrue($response['body']['error']);
    }

    /**
     * Testa validação de token
     */
    public function testTokenValidation(): void
    {
        if (!$this->isServiceRunning($this->authServiceUrl)) {
            $this->markTestSkipped('Auth service não está disponível');
        }

        // Fazer login para obter token
        $loginResult = $this->loginAndGetToken();
        $token = $loginResult['access_token'];

        // Validar token
        $response = $this->makeRequest(
            "{$this->authServiceUrl}/validate",
            'POST',
            null,
            $this->getAuthHeaders($token)
        );

        $this->assertEquals(200, $response['code'], 'Validação de token deve ser bem-sucedida');
        $this->assertTrue($response['body']['success']);
        $this->assertTrue($response['body']['data']['valid']);
        $this->assertArrayHasKey('user_id', $response['body']['data']);
        $this->assertArrayHasKey('email', $response['body']['data']);
        $this->assertArrayHasKey('role', $response['body']['data']);
    }

    /**
     * Testa refresh token
     */
    public function testRefreshToken(): void
    {
        if (!$this->isServiceRunning($this->authServiceUrl)) {
            $this->markTestSkipped('Auth service não está disponível');
        }

        // Fazer login para obter tokens
        $loginResult = $this->loginAndGetToken();
        $refreshToken = $loginResult['refresh_token'];

        // Usar refresh token
        $response = $this->makeRequest(
            "{$this->authServiceUrl}/refresh",
            'POST',
            ['refresh_token' => $refreshToken]
        );

        $this->assertEquals(200, $response['code'], 'Refresh token deve funcionar');
        $this->assertTrue($response['body']['success']);
        $this->assertArrayHasKey('access_token', $response['body']['data']);
        $this->assertNotEmpty($response['body']['data']['access_token']);
        
        // Verificar se o novo token é diferente do original
        $this->assertNotEquals($loginResult['access_token'], $response['body']['data']['access_token']);
    }
}
