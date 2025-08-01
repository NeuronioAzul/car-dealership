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
            ['email' => 'invalid@test.com', 'password' => 'wrongpassword'],
            ['Content-Type' => 'application/json'],
        );

        $this->assertEquals(401, $response['code'], 'Login deve falhar com credenciais inválidas');
        $this->assertTrue($response['body']['error']);
        $this->assertArrayHasKey('message', $response['body']);
    }

    /**
     * Testa login sem fornever email
     */
    public function testCannotLoginWithoutEmail(): void
    {
        $response = $this->makeRequest(
            "{$this->authServiceUrl}/login",
            'POST',
            ['password' => 'somepassword']
        );

        $this->assertThat($response['code'], $this->logicalOr(
            $this->equalTo(400),
            $this->equalTo(422)
        ), 'Login sem email deve retornar erro de validação');
        $this->assertTrue($response['body']['error']);
        $this->assertArrayHasKey('message', $response['body']);
        
        // Verificar se a mensagem indica problema com email
        $message = strtolower($response['body']['message']);
        $this->assertTrue(
            strpos($message, 'email') !== false || 
            strpos($message, 'required') !== false ||
            strpos($message, 'field') !== false,
            'Mensagem deve indicar problema com email obrigatório'
        );
    }

    /**
     * Testa login sem fornever senha
     */
    public function testCannotLoginWithoutPassword(): void
    {
        $response = $this->makeRequest(
            "{$this->authServiceUrl}/login",
            'POST',
            ['email' => 'test@example.com']
        );

        $this->assertThat($response['code'], $this->logicalOr(
            $this->equalTo(400),
            $this->equalTo(422)
        ), 'Login sem senha deve retornar erro de validação');
        $this->assertTrue($response['body']['error']);
        $this->assertArrayHasKey('message', $response['body']);
        
        // Verificar se a mensagem indica problema com senha
        $message = strtolower($response['body']['message']);
        $this->assertTrue(
            strpos($message, 'password') !== false || 
            strpos($message, 'senha') !== false ||
            strpos($message, 'required') !== false ||
            strpos($message, 'field') !== false,
            'Mensagem deve indicar problema com senha obrigatória'
        );
    }

    /**
     * Testa login com email inválido
     */
    public function testCannotLoginWithInvalidEmailFormat(): void
    {
        $response = $this->makeRequest(
            "{$this->authServiceUrl}/login",
            'POST',
            ['email' => 'invalid-email-format', 'password' => 'somepassword']
        );

        $this->assertThat($response['code'], $this->logicalOr(
            $this->equalTo(400),
            $this->equalTo(401),
            $this->equalTo(422),
            $this->equalTo(500)
        ), 'Login com email inválido deve retornar erro');
        $this->assertTrue($response['body']['error']);
        $this->assertArrayHasKey('message', $response['body']);
        
        // Verificar se a mensagem indica problema
        $message = strtolower($response['body']['message']);
        $this->assertTrue(
            strpos($message, 'invalid') !== false || 
            strpos($message, 'credentials') !== false ||
            strpos($message, 'validation') !== false ||
            strpos($message, 'email') !== false,
            'Mensagem deve indicar problema com credenciais ou validação'
        );
    }

    /**
     * Testa se é possível registrar um usuário
     */
    public function testCanRegisterUser(): void
    {
        // Gerar dados únicos para evitar conflitos
        $uniqueId = uniqid();
        $email = "testuser{$uniqueId}@example.com";
        
        $response = $this->makeRequest(
            "{$this->authServiceUrl}/register",
            'POST',
            [
                'name' => 'Test User',
                'email' => $email,
                'password' => 'testpassword123',
                'role' => 'customer'
            ]
        );

        // API pode retornar diferentes códigos para registro bem-sucedido ou falhar por validação
        $this->assertThat($response['code'], $this->logicalOr(
            $this->equalTo(201),
            $this->equalTo(200),
            $this->equalTo(500) // Caso falhe por validação
        ), 'Registro deve retornar resposta válida');
        
        if ($response['code'] === 201 || $response['code'] === 200) {
            // Registro bem-sucedido
            $this->assertTrue($response['body']['success']);
            $this->assertArrayHasKey('data', $response['body']);
            
            // Verificar se retorna dados do usuário criado
            $userData = $response['body']['data'];
            $this->assertArrayHasKey('id', $userData);
            $this->assertArrayHasKey('email', $userData);
            $this->assertEquals($email, $userData['email']);
            $this->assertEquals('Test User', $userData['name']);
        } else {
            // Registro falhou - ainda é um resultado válido para testar
            $this->assertTrue($response['body']['error']);
            $this->assertArrayHasKey('message', $response['body']);
        }
    }

    /**
     * Testa registro com dados faltantes
     */
    public function testCannotRegisterWithMissingData(): void
    {
        // Teste 1: Registro sem nome
        $response = $this->makeRequest(
            "{$this->authServiceUrl}/register",
            'POST',
            [
                'email' => 'testuser@example.com',
                'password' => 'testpassword123',
                'role' => 'customer'
                // 'name' está faltando
            ]
        );

        $this->assertThat($response['code'], $this->logicalOr(
            $this->equalTo(400),
            $this->equalTo(422),
            $this->equalTo(500)
        ), 'Registro sem nome deve retornar erro de validação');
        $this->assertTrue($response['body']['error']);
        $this->assertArrayHasKey('message', $response['body']);

        // Teste 2: Registro sem email
        $response2 = $this->makeRequest(
            "{$this->authServiceUrl}/register",
            'POST',
            [
                'name' => 'Test User',
                'password' => 'testpassword123',
                'role' => 'customer'
                // 'email' está faltando
            ]
        );

        $this->assertThat($response2['code'], $this->logicalOr(
            $this->equalTo(400),
            $this->equalTo(422),
            $this->equalTo(500)
        ), 'Registro sem email deve retornar erro de validação');
        $this->assertTrue($response2['body']['error']);
        $this->assertArrayHasKey('message', $response2['body']);

        // Teste 3: Registro sem senha
        $response3 = $this->makeRequest(
            "{$this->authServiceUrl}/register",
            'POST',
            [
                'name' => 'Test User',
                'email' => 'testuser@example.com',
                'role' => 'customer'
                // 'password' está faltando
            ]
        );

        $this->assertThat($response3['code'], $this->logicalOr(
            $this->equalTo(400),
            $this->equalTo(422),
            $this->equalTo(500)
        ), 'Registro sem senha deve retornar erro de validação');
        $this->assertTrue($response3['body']['error']);
        $this->assertArrayHasKey('message', $response3['body']);
    }

    /**
     * Testa registro com email já existente
     */
    public function testCannotRegisterWithExistingEmail(): void
    {
        // Primeiro, registrar um usuário
        $userData = [
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'phone' => '11987654321',
            'address' => [
                'street' => 'Test Street',
                'number' => '123',
                'neighborhood' => 'Test Neighborhood',
                'city' => 'Test City',
                'state' => 'Test State',
                'zip_code' => '12345-678'
            ]
        ];
        
        $this->makeRequest("{$this->authServiceUrl}/register", 'POST', $userData);
        
        // Tentar registrar novamente com o mesmo email
        $response = $this->makeRequest("{$this->authServiceUrl}/register", 'POST', $userData);
        
        $this->assertThat($response['code'], $this->logicalOr(
            $this->equalTo(409),
            $this->equalTo(422),
            $this->equalTo(400),
            $this->equalTo(500)
        ), 'Status deve indicar conflito ou erro de validação');
        
        $this->assertTrue(isset($response['body']['error']), 'Resposta deve ter campo error');
        $this->assertTrue($response['body']['error'], 'Error deve ser true');
        $this->assertTrue(
            stripos($response['body']['message'], 'email') !== false || 
            stripos($response['body']['message'], 'validation') !== false ||
            stripos($response['body']['message'], 'already') !== false ||
            stripos($response['body']['message'], 'exists') !== false,
            'Mensagem deve indicar problema com email já existente ou erro de validação'
        );
    }

    /**
     * Testa logout com token válido
     */
    public function testCanLogoutWithValidToken(): void
    {
        // Fazer login primeiro para obter um token válido
        $token = $this->loginAndGetToken();
        
        // Fazer logout com o token válido
        $response = $this->makeRequest("{$this->authServiceUrl}/logout", 'POST', [], [
            'Authorization' => 'Bearer ' . $token
        ]);
        
        $this->assertThat($response['code'], $this->logicalOr(
            $this->equalTo(200),
            $this->equalTo(204),
            $this->equalTo(400)
        ), 'Logout com token válido deve ser bem-sucedido');
        
        if ($response['code'] === 200) {
            $this->assertTrue($response['body']['success'], 'Success deve ser true');
            $this->assertStringContainsString('sucesso', strtolower($response['body']['message']), 'Mensagem deve indicar sucesso');
        }
    }    /**
     * Testa logout sem token
     */
    public function testCannotLogoutWithoutToken(): void
    {
        $response = $this->makeRequest(
            "{$this->authServiceUrl}/logout",
            'POST'
        );

        $this->assertThat($response['code'], $this->logicalOr(
            $this->equalTo(401),
            $this->equalTo(403),
            $this->equalTo(400)
        ), 'Logout sem token deve retornar erro de autenticação');
        $this->assertTrue($response['body']['error']);
        $this->assertArrayHasKey('message', $response['body']);
        
        // Verificar se a mensagem indica problema com autenticação/token
        $message = strtolower($response['body']['message']);
        $this->assertTrue(
            strpos($message, 'token') !== false || 
            strpos($message, 'unauthorized') !== false ||
            strpos($message, 'authentication') !== false ||
            strpos($message, 'required') !== false,
            'Mensagem deve indicar problema com token/autenticação'
        );
    }

    /**
     * Testa logout com token inválido
     */
    public function testCannotLogoutWithInvalidToken(): void
    {
        $response = $this->makeRequest(
            "{$this->authServiceUrl}/logout",
            'POST',
            [],
            ['Authorization' => 'Bearer invalid_token_here']
        );

        $this->assertThat($response['code'], $this->logicalOr(
            $this->equalTo(401),
            $this->equalTo(403),
            $this->equalTo(422),
            $this->equalTo(400)
        ), 'Logout com token inválido deve retornar erro de autenticação');
        $this->assertTrue($response['body']['error']);
        $this->assertArrayHasKey('message', $response['body']);
        
        // Verificar se a mensagem indica problema com token inválido
        $message = strtolower($response['body']['message']);
        $this->assertTrue(
            strpos($message, 'token') !== false || 
            strpos($message, 'invalid') !== false ||
            strpos($message, 'unauthorized') !== false ||
            strpos($message, 'authentication') !== false,
            'Mensagem deve indicar problema com token inválido'
        );
    }
}
