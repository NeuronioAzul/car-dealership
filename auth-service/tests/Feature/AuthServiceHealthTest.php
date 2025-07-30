<?php

namespace Tests\Feature;

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
        $response = $this->makeRequest(
            "{$this->authServiceUrl}/login",
            'POST',
            ['email' => 'admin@concessionaria.com', 'password' => 'admin123']
        );

        $this->assertEquals(200, $response['code'], 'Login deve ser bem-sucedido');
        $this->assertTrue($response['body']['success']);
        $this->assertArrayHasKey('access_token', $response['body']['data']);
        $this->assertNotEmpty($response['body']['data']['access_token']);
    }

    /**
     * Testa login com credenciais inválidas
     */
    public function testCannotLoginWithInvalidCredentials(): void
    {
        $response = $this->makeRequest(
            "{$this->authServiceUrl}/login",
            'POST',
            ['email' => 'invalid@example.com', 'password' => 'wrongpassword']
        );

        $this->assertNotEquals(200, $response['code'], 'Login com credenciais inválidas deve falhar');
        $this->assertTrue($response['body']['error']);
    }
}
