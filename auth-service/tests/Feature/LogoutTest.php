<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Testes de funcionalidade para o sistema de logout com invalidação de token
 */
class LogoutTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->checkAuthServiceAvailability();
    }

    /**
     * Testa o fluxo completo de logout com invalidação de token
     */
    public function testCompleteLogoutFlow(): void
    {
        // 1. Fazer login e obter token
        $token = $this->loginAndGetToken();
        
        $this->assertNotEmpty($token, 'Token deve ser retornado no login');
        
        // 2. Validar que o token está funcionando
        $validateResponse = $this->makeRequest(
            "{$this->authServiceUrl}/validate",
            'POST',
            null,
            ["Authorization: Bearer $token"]
        );
        
        $this->assertEquals(200, $validateResponse['code'], 'Token deve ser válido após login');
        $this->assertTrue($validateResponse['body']['data']['valid']);
        $this->assertEquals('admin@example.com', $validateResponse['body']['data']['email']);
        
        // 3. Fazer logout
        $logoutResponse = $this->makeRequest(
            "{$this->authServiceUrl}/logout",
            'POST',
            null,
            ["Authorization: Bearer $token"]
        );
        
        $this->assertEquals(200, $logoutResponse['code'], 'Logout deve ser bem-sucedido');
        $this->assertTrue($logoutResponse['body']['success']);
        $this->assertStringContainsString('invalidado', $logoutResponse['body']['message']);
        
        // 4. Tentar validar token após logout (deve falhar)
        $validateAfterLogout = $this->makeRequest(
            "{$this->authServiceUrl}/validate",
            'POST',
            null,
            ["Authorization: Bearer $token"]
        );
        
        $this->assertEquals(401, $validateAfterLogout['code'], 'Token deve ser inválido após logout');
        $this->assertTrue($validateAfterLogout['body']['error']);
        $this->assertFalse($validateAfterLogout['body']['valid']);
        $this->assertStringContainsString('invalidado', $validateAfterLogout['body']['message']);
    }

    /**
     * Testa logout com token já invalidado
     */
    public function testLogoutWithAlreadyInvalidatedToken(): void
    {
        // 1. Fazer login e logout
        $token = $this->loginAndGetToken();
        
        $logoutResponse = $this->makeRequest(
            "{$this->authServiceUrl}/logout",
            'POST',
            null,
            ["Authorization: Bearer $token"]
        );
        
        $this->assertEquals(200, $logoutResponse['code'], 'Primeiro logout deve ser bem-sucedido');
        
        // 2. Tentar fazer logout novamente com o mesmo token
        $secondLogoutResponse = $this->makeRequest(
            "{$this->authServiceUrl}/logout",
            'POST',
            null,
            ["Authorization: Bearer $token"]
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
        
        $this->assertEquals(400, $logoutResponse['code'], 'Logout sem token deve retornar erro 400');
        $this->assertTrue($logoutResponse['body']['error']);
        $this->assertStringContainsString('Token não fornecido', $logoutResponse['body']['message']);
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
            ["Authorization: Bearer $invalidToken"]
        );
        
        $this->assertEquals(401, $logoutResponse['code'], 'Logout com token inválido deve retornar erro 401');
        $this->assertTrue($logoutResponse['body']['error']);
        $this->assertStringContainsString('inválido', $logoutResponse['body']['message']);
    }

    /**
     * Testa logout com token malformado
     */
    public function testLogoutWithMalformedToken(): void
    {
        $malformedToken = 'not-a-jwt-token';
        
        $logoutResponse = $this->makeRequest(
            "{$this->authServiceUrl}/logout",
            'POST',
            null,
            ["Authorization: Bearer $malformedToken"]
        );
        
        $this->assertEquals(401, $logoutResponse['code'], 'Logout com token malformado deve retornar erro 401');
        $this->assertTrue($logoutResponse['body']['error']);
    }

    /**
     * Testa se token invalidado não pode ser usado em outras operações
     */
    public function testInvalidatedTokenCannotBeUsedElsewhere(): void
    {
        // 1. Fazer login
        $token = $this->loginAndGetToken();
        
        // 2. Fazer logout
        $this->makeRequest(
            "{$this->authServiceUrl}/logout",
            'POST',
            null,
            ["Authorization: Bearer $token"]
        );
        
        // 3. Tentar usar o token em uma operação diferente (validação)
        $validateResponse = $this->makeRequest(
            "{$this->authServiceUrl}/validate",
            'POST',
            null,
            ["Authorization: Bearer $token"]
        );
        
        $this->assertEquals(401, $validateResponse['code'], 'Token invalidado não deve funcionar em outras operações');
        $this->assertFalse($validateResponse['body']['valid']);
    }

    /**
     * Testa múltiplos tokens de um mesmo usuário
     */
    public function testMultipleTokensFromSameUser(): void
    {
        // 1. Fazer login duas vezes para obter dois tokens diferentes
        $token1 = $this->loginAndGetToken();
        $token2 = $this->loginAndGetToken();
        
        $this->assertNotEquals($token1, $token2, 'Tokens devem ser diferentes');
        
        // 2. Validar que ambos os tokens funcionam
        $validate1 = $this->makeRequest(
            "{$this->authServiceUrl}/validate",
            'POST',
            null,
            ["Authorization: Bearer $token1"]
        );
        
        $validate2 = $this->makeRequest(
            "{$this->authServiceUrl}/validate",
            'POST',
            null,
            ["Authorization: Bearer $token2"]
        );
        
        $this->assertEquals(200, $validate1['code'], 'Primeiro token deve ser válido');
        $this->assertEquals(200, $validate2['code'], 'Segundo token deve ser válido');
        
        // 3. Fazer logout apenas com o primeiro token
        $logoutResponse = $this->makeRequest(
            "{$this->authServiceUrl}/logout",
            'POST',
            null,
            ["Authorization: Bearer $token1"]
        );
        
        $this->assertEquals(200, $logoutResponse['code'], 'Logout do primeiro token deve ser bem-sucedido');
        
        // 4. Verificar que apenas o primeiro token foi invalidado
        $validate1After = $this->makeRequest(
            "{$this->authServiceUrl}/validate",
            'POST',
            null,
            ["Authorization: Bearer $token1"]
        );
        
        $validate2After = $this->makeRequest(
            "{$this->authServiceUrl}/validate",
            'POST',
            null,
            ["Authorization: Bearer $token2"]
        );
        
        $this->assertEquals(401, $validate1After['code'], 'Primeiro token deve estar invalidado');
        $this->assertEquals(200, $validate2After['code'], 'Segundo token deve continuar válido');
    }
}
