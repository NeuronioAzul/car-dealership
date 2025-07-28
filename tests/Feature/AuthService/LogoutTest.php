<?php

namespace Tests\Feature\AuthService;

use Tests\TestCase;

/**
 * Testes de funcionalidade para o sistema de logout com invalidação de token
 */
class LogoutTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        if (!$this->isServiceRunning($this->authServiceUrl)) {
            $this->markTestSkipped('Auth service não está disponível');
        }
    }

    /**
     * Testa o fluxo completo de logout com invalidação de token
     */
    public function testCompleteLogoutFlow(): void
    {
        // 1. Fazer login e obter token
        $loginResult = $this->loginAndGetToken();
        $token = $loginResult['access_token'];
        
        $this->assertNotEmpty($token, 'Token deve ser retornado no login');
        
        // 2. Validar que o token está funcionando
        $validateResponse = $this->makeRequest(
            "{$this->authServiceUrl}/validate",
            'POST',
            null,
            $this->getAuthHeaders($token)
        );
        
        $this->assertEquals(200, $validateResponse['code'], 'Token deve ser válido após login');
        $this->assertTrue($validateResponse['body']['success']);
        
        // 3. Fazer logout
        $logoutResponse = $this->makeRequest(
            "{$this->authServiceUrl}/logout",
            'POST',
            null,
            $this->getAuthHeaders($token)
        );
        
        $this->assertEquals(200, $logoutResponse['code'], 'Logout deve ser bem-sucedido');
        $this->assertTrue($logoutResponse['body']['success']);
        $this->assertStringContainsString('invalidado', $logoutResponse['body']['message']);
        
        // 4. Tentar validar token após logout (deve falhar)
        $validateAfterLogout = $this->makeRequest(
            "{$this->authServiceUrl}/validate",
            'POST',
            null,
            $this->getAuthHeaders($token)
        );
        
        $this->assertEquals(401, $validateAfterLogout['code'], 'Token deve ser inválido após logout');
        $this->assertTrue($validateAfterLogout['body']['error']);
    }

    /**
     * Testa logout com token já invalidado
     */
    public function testLogoutWithAlreadyInvalidatedToken(): void
    {
        // 1. Fazer login e logout
        $loginResult = $this->loginAndGetToken();
        $token = $loginResult['access_token'];
        
        $logoutResponse = $this->makeRequest(
            "{$this->authServiceUrl}/logout",
            'POST',
            null,
            $this->getAuthHeaders($token)
        );
        
        $this->assertEquals(200, $logoutResponse['code'], 'Primeiro logout deve ser bem-sucedido');
        
        // 2. Tentar fazer logout novamente com o mesmo token
        $secondLogoutResponse = $this->makeRequest(
            "{$this->authServiceUrl}/logout",
            'POST',
            null,
            $this->getAuthHeaders($token)
        );
        
        $this->assertEquals(401, $secondLogoutResponse['code'], 'Segundo logout deve falhar');
        $this->assertTrue($secondLogoutResponse['body']['error']);
        $this->assertStringContainsString('invalidado', $secondLogoutResponse['body']['message']);
    }

    /**
     * Testa logout sem token
     */
    public function testLogoutWithoutToken(): void
    {
        $logoutResponse = $this->makeRequest(
            "{$this->authServiceUrl}/logout",
            'POST'
        );
        
        $this->assertEquals(400, $logoutResponse['code'], 'Logout sem token deve retornar 400');
        $this->assertTrue($logoutResponse['body']['error']);
    }

    /**
     * Testa logout com token inválido
     */
    public function testLogoutWithInvalidToken(): void
    {
        $invalidToken = 'invalid.token.here';
        
        $logoutResponse = $this->makeRequest(
            "{$this->authServiceUrl}/logout",
            'POST',
            null,
            $this->getAuthHeaders($invalidToken)
        );
        
        $this->assertEquals(401, $logoutResponse['code'], 'Logout com token inválido deve retornar 401');
        $this->assertTrue($logoutResponse['body']['error']);
    }

    /**
     * Testa refresh token após logout
     */
    public function testRefreshTokenAfterLogout(): void
    {
        // 1. Fazer login
        $loginResult = $this->loginAndGetToken();
        $accessToken = $loginResult['access_token'];
        $refreshToken = $loginResult['refresh_token'];
        
        // 2. Fazer logout
        $logoutResponse = $this->makeRequest(
            "{$this->authServiceUrl}/logout",
            'POST',
            null,
            $this->getAuthHeaders($accessToken)
        );
        
        $this->assertEquals(200, $logoutResponse['code'], 'Logout deve ser bem-sucedido');
        
        // 3. Tentar usar refresh token (deve ainda funcionar)
        $refreshResponse = $this->makeRequest(
            "{$this->authServiceUrl}/refresh",
            'POST',
            ['refresh_token' => $refreshToken]
        );
        
        // O refresh token deve ainda funcionar pois apenas o access token foi invalidado
        $this->assertEquals(200, $refreshResponse['code'], 'Refresh token deve ainda funcionar após logout');
        $this->assertTrue($refreshResponse['body']['success']);
        $this->assertArrayHasKey('access_token', $refreshResponse['body']['data']);
    }
}
