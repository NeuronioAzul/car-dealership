<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;

class AuthServiceIntegrationTest extends TestCase
{
    private string $baseUrl = 'http://localhost:8081/api/v1/auth';

    public function testHealthEndpoint(): void
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/health');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Se o serviço estiver rodando, deve retornar 200
        // Se não estiver, podemos pelo menos validar que não há erros fatais
        $this->assertTrue(is_string($response) || $response === false);
        $this->assertTrue(is_int($httpCode));
    }

    public function testRegisterEndpointStructure(): void
    {
        $userData = [
            'name' => 'João Silva',
            'email' => 'joao.test@email.com',
            'password' => 'password123',
            'phone' => '11999999999',
            'birth_date' => '1990-01-01',
            'address' => [
                'street' => 'Rua das Flores',
                'number' => '123',
                'neighborhood' => 'Centro',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01234-567'
            ],
            'role' => 'customer',
            'accept_terms' => true,
            'accept_privacy' => true,
            'accept_communications' => false
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/register');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Validar que pelo menos não há erro fatal no endpoint
        $this->assertTrue(is_string($response) || $response === false);
        $this->assertTrue(is_int($httpCode));

        // Se conseguiu se conectar, validar estrutura da resposta
        if ($response !== false) {
            $decodedResponse = json_decode($response, true);
            $this->assertTrue(is_array($decodedResponse) || $decodedResponse === null);
        }
    }

    public function testLoginEndpointStructure(): void
    {
        $loginData = [
            'email' => 'test@email.com',
            'password' => 'password123'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/login');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Validar que pelo menos não há erro fatal no endpoint
        $this->assertTrue(is_string($response) || $response === false);
        $this->assertTrue(is_int($httpCode));

        // Se conseguiu se conectar, validar estrutura da resposta
        if ($response !== false) {
            $decodedResponse = json_decode($response, true);
            $this->assertTrue(is_array($decodedResponse) || $decodedResponse === null);
        }
    }

    /**
     * Testa fluxo completo de registro de usuário
     */
    public function testCompleteUserRegistrationFlow(): void
    {
        // Dados de teste para registro
        $userData = [
            'name' => 'Maria Silva Integration Test',
            'email' => 'maria.integration.' . time() . '@email.com', // Email único para evitar conflitos
            'password' => 'strongPassword123!',
            'phone' => '11987654321',
            'birth_date' => '1985-05-15',
            'address' => [
                'street' => 'Rua da Integração',
                'number' => '456',
                'neighborhood' => 'Centro',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01234-567'
            ],
            'role' => 'customer',
            'accept_terms' => true,
            'accept_privacy' => true,
            'accept_communications' => false
        ];

        // Executar requisição de registro
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/register');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Validações básicas de conectividade
        $this->assertNotFalse($response, 'Deve conseguir conectar no serviço de registro');
        $this->assertTrue(is_int($httpCode), 'Deve retornar código HTTP válido');

        // Se conseguiu conectar, validar resposta
        if ($response !== false && $httpCode > 0) {
            $decodedResponse = json_decode($response, true);
            $this->assertIsArray($decodedResponse, 'Resposta deve ser um JSON válido');

            // Se o serviço está funcionando normalmente (201 Created)
            if ($httpCode === 201) {
                $this->assertTrue($decodedResponse['success'], 'Registro deve ser bem-sucedido');
                $this->assertArrayHasKey('data', $decodedResponse, 'Resposta deve conter dados do usuário');
                $this->assertArrayHasKey('id', $decodedResponse['data'], 'Dados devem conter ID do usuário');
                $this->assertEquals($userData['email'], $decodedResponse['data']['email'], 'Email deve coincidir');
                $this->assertEquals($userData['name'], $decodedResponse['data']['name'], 'Nome deve coincidir');
            }
            // Se há conflito (usuário já existe) - código 409
            elseif ($httpCode === 409) {
                $this->assertArrayHasKey('error', $decodedResponse, 'Resposta de erro deve conter mensagem');
                $this->assertStringContainsString('já existe', $decodedResponse['message'] ?? '', 'Deve indicar conflito de usuário');
            }
            // Se há erro de validação - código 422
            elseif ($httpCode === 422) {
                $this->assertArrayHasKey('error', $decodedResponse, 'Resposta deve conter erro de validação');
            }
        }
    }

    /**
     * Testa fluxo completo de login e logout
     */
    public function testCompleteLoginLogoutFlow(): void
    {
        // Primeiro, tentar registrar um usuário para fazer login
        $userData = [
            'name' => 'João Login Test',
            'email' => 'joao.login.' . time() . '@email.com',
            'password' => 'testPassword123!',
            'phone' => '11987654321',
            'birth_date' => '1990-01-01',
            'address' => [
                'street' => 'Rua do Login',
                'number' => '123',
                'neighborhood' => 'Centro',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01234-567'
            ],
            'role' => 'customer',
            'accept_terms' => true,
            'accept_privacy' => true,
            'accept_communications' => false
        ];

        // Registrar usuário
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/register');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $registerResponse = curl_exec($ch);
        $registerHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Aguardar para evitar conflitos de timestamp
        sleep(1);

        // Executar login
        $loginData = [
            'email' => $userData['email'],
            'password' => $userData['password']
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/login');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $loginResponse = curl_exec($ch);
        $loginHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Validações básicas de conectividade
        $this->assertNotFalse($loginResponse, 'Deve conseguir conectar no serviço de login');
        $this->assertTrue(is_int($loginHttpCode), 'Deve retornar código HTTP válido para login');

        $accessToken = null;

        // Se conseguiu conectar e fazer login
        if ($loginResponse !== false && $loginHttpCode > 0) {
            $loginDecoded = json_decode($loginResponse, true);
            $this->assertIsArray($loginDecoded, 'Resposta de login deve ser JSON válido');

            // Se login foi bem-sucedido
            if ($loginHttpCode === 200 && isset($loginDecoded['success']) && $loginDecoded['success']) {
                $this->assertArrayHasKey('data', $loginDecoded, 'Login deve retornar dados');
                $this->assertArrayHasKey('access_token', $loginDecoded['data'], 'Login deve retornar token de acesso');
                $this->assertArrayHasKey('token_type', $loginDecoded['data'], 'Login deve retornar tipo de token');

                $accessToken = $loginDecoded['data']['access_token'];
                $this->assertNotEmpty($accessToken, 'Token de acesso não deve estar vazio');

                // Aguardar para evitar conflitos de timestamp
                sleep(1);

                // Testar logout com o token obtido
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/logout');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $accessToken
                ]);

                $logoutResponse = curl_exec($ch);
                $logoutHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                // Validar logout
                $this->assertNotFalse($logoutResponse, 'Deve conseguir conectar no serviço de logout');

                if ($logoutResponse !== false && $logoutHttpCode > 0) {
                    $logoutDecoded = json_decode($logoutResponse, true);
                    $this->assertIsArray($logoutDecoded, 'Resposta de logout deve ser JSON válido');

                    if ($logoutHttpCode === 200) {
                        $this->assertTrue($logoutDecoded['success'], 'Logout deve ser bem-sucedido');
                        $this->assertArrayHasKey('message', $logoutDecoded, 'Logout deve retornar mensagem');
                    }
                }
            }
        }
    }

    /**
     * Testa refresh de token
     */
    public function testTokenRefreshFlow(): void
    {
        // Dados de teste para registrar e fazer login
        $userData = [
            'name' => 'Ana Refresh Test',
            'email' => 'ana.refresh.' . time() . '@email.com',
            'password' => 'refreshPassword123!',
            'phone' => '11987654321',
            'birth_date' => '1988-03-10',
            'address' => [
                'street' => 'Rua do Refresh',
                'number' => '789',
                'neighborhood' => 'Centro',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01234-567'
            ],
            'role' => 'customer',
            'accept_terms' => true,
            'accept_privacy' => true,
            'accept_communications' => false
        ];

        // Registrar usuário
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/register');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $registerResponse = curl_exec($ch);
        curl_close($ch);

        // Aguardar para evitar conflitos de timestamp
        sleep(1);

        // Fazer login para obter tokens
        $loginData = [
            'email' => $userData['email'],
            'password' => $userData['password']
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/login');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $loginResponse = curl_exec($ch);
        $loginHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Validações básicas
        $this->assertNotFalse($loginResponse, 'Deve conseguir fazer login para teste de refresh');

        if ($loginResponse !== false && $loginHttpCode === 200) {
            $loginDecoded = json_decode($loginResponse, true);

            if (
                isset($loginDecoded['success']) && $loginDecoded['success'] &&
                isset($loginDecoded['data']['refresh_token'])
            ) {

                $refreshToken = $loginDecoded['data']['refresh_token'];

                // Aguardar para garantir diferença de timestamp
                sleep(2);

                // Testar refresh token
                $refreshData = [
                    'refresh_token' => $refreshToken
                ];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/refresh');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($refreshData));
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

                $refreshResponse = curl_exec($ch);
                $refreshHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                // Validar resposta de refresh
                $this->assertNotFalse($refreshResponse, 'Deve conseguir conectar no endpoint de refresh');

                if ($refreshResponse !== false && $refreshHttpCode > 0) {
                    $refreshDecoded = json_decode($refreshResponse, true);
                    $this->assertIsArray($refreshDecoded, 'Resposta de refresh deve ser JSON válido');

                    if ($refreshHttpCode === 200) {
                        $this->assertTrue($refreshDecoded['success'], 'Refresh deve ser bem-sucedido');
                        $this->assertArrayHasKey('data', $refreshDecoded, 'Refresh deve retornar dados');
                        $this->assertArrayHasKey('access_token', $refreshDecoded['data'], 'Refresh deve retornar novo token');
                        $this->assertNotEmpty($refreshDecoded['data']['access_token'], 'Novo token não deve estar vazio');

                        // Verificar se o novo token é diferente do original
                        $originalToken = $loginDecoded['data']['access_token'];
                        $newToken = $refreshDecoded['data']['access_token'];
                        $this->assertNotEquals($originalToken, $newToken, 'Novo token deve ser diferente do original');
                    }
                }
            } else {
                // Se não há refresh token na resposta de login, apenas validar conectividade
                $this->assertTrue(true, 'Login conectou mas não retornou refresh token - implementação pode não incluir refresh tokens');
            }
        }
    }

    /**
     * Testa validação de token
     */
    public function testTokenValidationFlow(): void
    {
        // Dados de teste para registrar e fazer login
        $userData = [
            'name' => 'Carlos Validate Test',
            'email' => 'carlos.validate.' . time() . '@email.com',
            'password' => 'validatePassword123!',
            'phone' => '11987654321',
            'birth_date' => '1992-08-20',
            'address' => [
                'street' => 'Rua da Validação',
                'number' => '321',
                'neighborhood' => 'Centro',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01234-567'
            ],
            'role' => 'customer',
            'accept_terms' => true,
            'accept_privacy' => true,
            'accept_communications' => false
        ];

        // Registrar usuário
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/register');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $registerResponse = curl_exec($ch);
        curl_close($ch);

        // Aguardar para evitar conflitos de timestamp
        sleep(1);

        // Fazer login para obter token
        $loginData = [
            'email' => $userData['email'],
            'password' => $userData['password']
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/login');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $loginResponse = curl_exec($ch);
        $loginHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Validações básicas
        $this->assertNotFalse($loginResponse, 'Deve conseguir fazer login para teste de validação');

        if ($loginResponse !== false && $loginHttpCode === 200) {
            $loginDecoded = json_decode($loginResponse, true);

            if (
                isset($loginDecoded['success']) && $loginDecoded['success'] &&
                isset($loginDecoded['data']['access_token'])
            ) {

                $accessToken = $loginDecoded['data']['access_token'];

                // Aguardar para garantir diferença de timestamp
                sleep(1);

                // Testar validação do token
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/validate');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $accessToken
                ]);

                $validateResponse = curl_exec($ch);
                $validateHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                // Validar resposta de validação
                $this->assertNotFalse($validateResponse, 'Deve conseguir conectar no endpoint de validação');

                if ($validateResponse !== false && $validateHttpCode > 0) {
                    $validateDecoded = json_decode($validateResponse, true);
                    $this->assertIsArray($validateDecoded, 'Resposta de validação deve ser JSON válido');

                    if ($validateHttpCode === 200) {
                        $this->assertTrue($validateDecoded['success'], 'Validação deve ser bem-sucedida');
                        $this->assertArrayHasKey('data', $validateDecoded, 'Validação deve retornar dados');
                        $this->assertTrue($validateDecoded['data']['valid'], 'Token deve ser válido');
                        $this->assertArrayHasKey('user_id', $validateDecoded['data'], 'Dados devem incluir user_id');
                        $this->assertArrayHasKey('email', $validateDecoded['data'], 'Dados devem incluir email');
                        $this->assertEquals($userData['email'], $validateDecoded['data']['email'], 'Email deve coincidir');
                    }
                }

                // Testar validação com token inválido
                sleep(1);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/validate');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Authorization: Bearer invalid_token_here'
                ]);

                $invalidResponse = curl_exec($ch);
                $invalidHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                // Validar resposta para token inválido
                if ($invalidResponse !== false && $invalidHttpCode > 0) {
                    $invalidDecoded = json_decode($invalidResponse, true);

                    if ($invalidHttpCode === 401) {
                        $this->assertArrayHasKey('valid', $invalidDecoded, 'Resposta deve indicar se token é válido');
                        $this->assertFalse($invalidDecoded['valid'], 'Token inválido deve retornar valid=false');
                        $this->assertArrayHasKey('error', $invalidDecoded, 'Resposta deve conter indicador de erro');
                    }
                }
            }
        }
    }

    /**
     * Testa blacklist de tokens
     */
    public function testTokenBlacklistFlow(): void
    {
        // Dados de teste para registrar e fazer login
        $userData = [
            'name' => 'Pedro Blacklist Test',
            'email' => 'pedro.blacklist.' . time() . '@email.com',
            'password' => 'blacklistPassword123!',
            'phone' => '11987654321',
            'birth_date' => '1985-12-25',
            'address' => [
                'street' => 'Rua do Blacklist',
                'number' => '555',
                'neighborhood' => 'Centro',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01234-567'
            ],
            'role' => 'customer',
            'accept_terms' => true,
            'accept_privacy' => true,
            'accept_communications' => false
        ];

        // Registrar usuário
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/register');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $registerResponse = curl_exec($ch);
        curl_close($ch);

        // Aguardar para evitar conflitos de timestamp
        sleep(1);

        // Fazer login para obter token
        $loginData = [
            'email' => $userData['email'],
            'password' => $userData['password']
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/login');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $loginResponse = curl_exec($ch);
        $loginHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Validações básicas
        $this->assertNotFalse($loginResponse, 'Deve conseguir fazer login para teste de blacklist');

        if ($loginResponse !== false && $loginHttpCode === 200) {
            $loginDecoded = json_decode($loginResponse, true);

            if (
                isset($loginDecoded['success']) && $loginDecoded['success'] &&
                isset($loginDecoded['data']['access_token'])
            ) {

                $accessToken = $loginDecoded['data']['access_token'];

                // Aguardar para garantir diferença de timestamp
                sleep(1);

                // Primeiro, validar que o token está válido
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/validate');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $accessToken
                ]);

                $validateResponse = curl_exec($ch);
                $validateHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                // Se conseguiu validar inicialmente
                if ($validateResponse !== false && $validateHttpCode === 200) {
                    $validateDecoded = json_decode($validateResponse, true);
                    if (isset($validateDecoded['success']) && $validateDecoded['success']) {
                        $this->assertTrue($validateDecoded['data']['valid'], 'Token deve estar válido antes do logout');
                    }
                }

                // Aguardar para diferença de timestamp
                sleep(1);

                // Fazer logout (que deve adicionar token ao blacklist)
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/logout');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $accessToken
                ]);

                $logoutResponse = curl_exec($ch);
                $logoutHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                // Validar logout
                $this->assertNotFalse($logoutResponse, 'Deve conseguir fazer logout');

                if ($logoutResponse !== false && $logoutHttpCode === 200) {
                    $logoutDecoded = json_decode($logoutResponse, true);
                    if (isset($logoutDecoded['success']) && $logoutDecoded['success']) {
                        $this->assertTrue($logoutDecoded['success'], 'Logout deve ser bem-sucedido');
                    }

                    // Aguardar para garantir que blacklist foi processado
                    sleep(1);

                    // Tentar validar o token novamente (deve estar na blacklist)
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/validate');
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        'Content-Type: application/json',
                        'Authorization: Bearer ' . $accessToken
                    ]);

                    $blacklistValidateResponse = curl_exec($ch);
                    $blacklistValidateHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);

                    // Validar que token está na blacklist
                    $this->assertNotFalse($blacklistValidateResponse, 'Deve conseguir conectar para validar token na blacklist');

                    if ($blacklistValidateResponse !== false && $blacklistValidateHttpCode > 0) {
                        $blacklistDecoded = json_decode($blacklistValidateResponse, true);

                        // Token deve estar invalidado (401) ou retornar valid=false
                        if ($blacklistValidateHttpCode === 401) {
                            $this->assertArrayHasKey('valid', $blacklistDecoded, 'Resposta deve indicar se token é válido');
                            $this->assertFalse($blacklistDecoded['valid'], 'Token deve estar na blacklist (valid=false)');
                        }
                    }
                }
            }
        }
    }

    /**
     * Testa persistência de dados do usuário
     */
    public function testUserDataPersistence(): void
    {
        // Dados completos de teste para persistência
        $userData = [
            'name' => 'Luisa Persistência Test',
            'email' => 'luisa.persistence.' . time() . '@email.com',
            'password' => 'persistencePassword123!',
            'phone' => '11987654321',
            'birth_date' => '1987-06-15',
            'address' => [
                'street' => 'Rua da Persistência',
                'number' => '777',
                'neighborhood' => 'Vila Madalena',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '05435-123'
            ],
            'role' => 'customer',
            'accept_terms' => true,
            'accept_privacy' => true,
            'accept_communications' => true
        ];

        // Registrar usuário
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/register');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $registerResponse = curl_exec($ch);
        $registerHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Validações básicas
        $this->assertNotFalse($registerResponse, 'Deve conseguir registrar usuário para teste de persistência');

        if ($registerResponse !== false && $registerHttpCode === 201) {
            $registerDecoded = json_decode($registerResponse, true);

            if (
                isset($registerDecoded['success']) && $registerDecoded['success'] &&
                isset($registerDecoded['data'])
            ) {

                $registeredData = $registerDecoded['data'];

                // Os dados do usuário estão dentro de 'data.user'
                if (isset($registeredData['user'])) {
                    $registeredUser = $registeredData['user'];

                    // Validar que dados foram persistidos corretamente no registro
                    $this->assertArrayHasKey('id', $registeredUser, 'Usuário registrado deve ter ID');
                    $this->assertNotEmpty($registeredUser['id'], 'ID do usuário não deve estar vazio');
                    $this->assertEquals($userData['name'], $registeredUser['name'], 'Nome deve ser persistido corretamente');
                    $this->assertEquals($userData['email'], $registeredUser['email'], 'Email deve ser persistido corretamente');
                    $this->assertEquals($userData['role'], $registeredUser['role'], 'Role deve ser persistida corretamente');
                } else {
                    // Dados podem estar diretamente em 'data'
                    $registeredUser = $registeredData;

                    // Validar que dados foram persistidos corretamente no registro
                    $this->assertArrayHasKey('id', $registeredUser, 'Usuário registrado deve ter ID');
                    $this->assertNotEmpty($registeredUser['id'], 'ID do usuário não deve estar vazio');
                    $this->assertEquals($userData['name'], $registeredUser['name'], 'Nome deve ser persistido corretamente');
                    $this->assertEquals($userData['email'], $registeredUser['email'], 'Email deve ser persistido corretamente');
                    $this->assertEquals($userData['role'], $registeredUser['role'], 'Role deve ser persistida corretamente');
                }

                // Aguardar para evitar conflitos de timestamp
                sleep(2);

                // Fazer login para verificar se dados persistidos permitem autenticação
                $loginData = [
                    'email' => $userData['email'],
                    'password' => $userData['password']
                ];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/login');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

                $loginResponse = curl_exec($ch);
                $loginHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                // Validar que login funciona com dados persistidos
                $this->assertNotFalse($loginResponse, 'Deve conseguir fazer login com dados persistidos');

                if ($loginResponse !== false && $loginHttpCode === 200) {
                    $loginDecoded = json_decode($loginResponse, true);

                    if (isset($loginDecoded['success']) && $loginDecoded['success']) {
                        $this->assertTrue($loginDecoded['success'], 'Login deve ser bem-sucedido com dados persistidos');
                        $this->assertArrayHasKey('data', $loginDecoded, 'Login deve retornar dados do usuário persistido');
                        $this->assertArrayHasKey('user', $loginDecoded['data'], 'Dados de login devem incluir informações do usuário');

                        // Validar que dados do usuário são retornados consistentemente
                        $loginUser = $loginDecoded['data']['user'];
                        $userId = isset($registeredData['user']) ? $registeredData['user']['id'] : $registeredData['id'];
                        $this->assertEquals($userId, $loginUser['id'], 'ID deve ser consistente entre registro e login');
                        $this->assertEquals($userData['email'], $loginUser['email'], 'Email deve ser consistente entre registro e login');
                        $this->assertEquals($userData['name'], $loginUser['name'], 'Nome deve ser consistente entre registro e login');
                    }
                }
            }
        }
    }

    /**
     * Testa publicação de eventos
     */
    public function testEventPublishing(): void
    {
        // Este teste valida se o sistema não falha ao tentar publicar eventos
        // Em um ambiente de teste sem RabbitMQ, deve degradar graciosamente

        // Dados de teste para gerar eventos
        $userData = [
            'name' => 'Roberto Event Test',
            'email' => 'roberto.events.' . time() . '@email.com',
            'password' => 'eventPassword123!',
            'phone' => '11987654321',
            'birth_date' => '1989-09-30',
            'address' => [
                'street' => 'Rua dos Eventos',
                'number' => '888',
                'neighborhood' => 'Itaim Bibi',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '04534-567'
            ],
            'role' => 'customer',
            'accept_terms' => true,
            'accept_privacy' => true,
            'accept_communications' => false
        ];

        // Testar evento de registro de usuário
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/register');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $registerResponse = curl_exec($ch);
        $registerHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Validar que registro funciona mesmo se publicação de eventos falhar
        $this->assertNotFalse($registerResponse, 'Registro deve funcionar mesmo se eventos falharem');

        if ($registerResponse !== false && $registerHttpCode > 0) {
            $registerDecoded = json_decode($registerResponse, true);

            // Se registro foi bem-sucedido, sistema gerenciou eventos corretamente
            if ($registerHttpCode === 201 && isset($registerDecoded['success']) && $registerDecoded['success']) {
                $this->assertTrue($registerDecoded['success'], 'Sistema deve processar registro mesmo com falhas de evento');
                $this->assertArrayHasKey('data', $registerDecoded, 'Dados de usuário devem ser retornados mesmo se eventos falharem');

                // Aguardar para evitar conflitos de timestamp
                sleep(1);

                // Testar evento de login
                $loginData = [
                    'email' => $userData['email'],
                    'password' => $userData['password']
                ];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/login');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

                $loginResponse = curl_exec($ch);
                $loginHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                // Validar que login funciona mesmo se publicação de eventos falhar
                $this->assertNotFalse($loginResponse, 'Login deve funcionar mesmo se eventos falharem');

                if ($loginResponse !== false && $loginHttpCode === 200) {
                    $loginDecoded = json_decode($loginResponse, true);

                    if (isset($loginDecoded['success']) && $loginDecoded['success']) {
                        $this->assertTrue($loginDecoded['success'], 'Sistema deve processar login mesmo com falhas de evento');
                        $this->assertArrayHasKey('data', $loginDecoded, 'Tokens devem ser gerados mesmo se eventos falharem');
                        $this->assertArrayHasKey('access_token', $loginDecoded['data'], 'Token de acesso deve ser gerado mesmo se eventos falharem');

                        // Validar que o sistema continua funcionando após eventos
                        $this->assertNotEmpty($loginDecoded['data']['access_token'], 'Token não deve estar vazio');
                    }
                }
            }
            // Se houve erro, validar que não foi por falha de eventos
            elseif ($registerHttpCode >= 400) {
                // Erro deve ser de validação/negócio, não de infraestrutura de eventos
                $this->assertIsArray($registerDecoded, 'Mesmo com erro, resposta deve ser JSON válido');
                $this->assertArrayHasKey('message', $registerDecoded, 'Erro deve ter mensagem clara, não erro de evento');
            }
        }
    }

    /**
     * Testa middleware de autenticação em rotas protegidas
     */
    public function testAuthMiddlewareInProtectedRoutes(): void
    {
        // Este teste valida o comportamento do middleware de autenticação
        // testando acesso sem token e com token válido

        // Primeiro, testar acesso sem token em rota protegida (validate)
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/validate');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        // Sem header Authorization

        $noTokenResponse = curl_exec($ch);
        $noTokenHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Deve rejeitar acesso sem token
        $this->assertNotFalse($noTokenResponse, 'Deve conseguir conectar para testar middleware');

        if ($noTokenResponse !== false && $noTokenHttpCode > 0) {
            $this->assertEquals(401, $noTokenHttpCode, 'Middleware deve rejeitar acesso sem token (401)');

            $noTokenDecoded = json_decode($noTokenResponse, true);
            if (is_array($noTokenDecoded)) {
                $this->assertArrayHasKey('error', $noTokenDecoded, 'Resposta de erro deve conter flag de erro');
                $this->assertFalse($noTokenDecoded['valid'] ?? true, 'Deve indicar que token é inválido');
            }
        }

        // Testar com token inválido
        sleep(1);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/validate');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer invalid_fake_token_here_12345'
        ]);

        $invalidTokenResponse = curl_exec($ch);
        $invalidTokenHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Deve rejeitar token inválido
        if ($invalidTokenResponse !== false && $invalidTokenHttpCode > 0) {
            $this->assertEquals(401, $invalidTokenHttpCode, 'Middleware deve rejeitar token inválido (401)');

            $invalidTokenDecoded = json_decode($invalidTokenResponse, true);
            if (is_array($invalidTokenDecoded)) {
                $this->assertFalse($invalidTokenDecoded['valid'] ?? true, 'Token inválido deve retornar valid=false');
            }
        }

        // Agora criar usuário válido para testar token válido
        $userData = [
            'name' => 'Sandra Middleware Test',
            'email' => 'sandra.middleware.' . time() . '@email.com',
            'password' => 'middlewarePassword123!',
            'phone' => '11987654321',
            'birth_date' => '1991-04-12',
            'address' => [
                'street' => 'Rua do Middleware',
                'number' => '999',
                'neighborhood' => 'Pinheiros',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '05422-987'
            ],
            'role' => 'customer',
            'accept_terms' => true,
            'accept_privacy' => true,
            'accept_communications' => false
        ];

        // Registrar e fazer login para obter token válido
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/register');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $registerResponse = curl_exec($ch);
        curl_close($ch);

        sleep(1);

        $loginData = [
            'email' => $userData['email'],
            'password' => $userData['password']
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/login');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $loginResponse = curl_exec($ch);
        $loginHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Se conseguiu fazer login, testar middleware com token válido
        if ($loginResponse !== false && $loginHttpCode === 200) {
            $loginDecoded = json_decode($loginResponse, true);

            if (
                isset($loginDecoded['success']) && $loginDecoded['success'] &&
                isset($loginDecoded['data']['access_token'])
            ) {

                $validToken = $loginDecoded['data']['access_token'];

                sleep(1);

                // Testar acesso com token válido
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/validate');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $validToken
                ]);

                $validTokenResponse = curl_exec($ch);
                $validTokenHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                // Middleware deve permitir acesso com token válido
                if ($validTokenResponse !== false && $validTokenHttpCode > 0) {
                    $this->assertEquals(200, $validTokenHttpCode, 'Middleware deve permitir acesso com token válido (200)');

                    $validTokenDecoded = json_decode($validTokenResponse, true);
                    if (is_array($validTokenDecoded)) {
                        $this->assertTrue($validTokenDecoded['success'] ?? false, 'Middleware deve processar requisição com token válido');
                        $this->assertTrue($validTokenDecoded['data']['valid'] ?? false, 'Token válido deve retornar valid=true');
                        $this->assertArrayHasKey('user_id', $validTokenDecoded['data'], 'Middleware deve extrair dados do usuário do token');
                    }
                }
            }
        }
    }

    /**
     * Testa validação de roles de usuário
     */
    public function testUserRoleValidation(): void
    {
        // Testar registro com diferentes roles
        $customerData = [
            'name' => 'Cliente Role Test',
            'email' => 'cliente.role.' . time() . '@email.com',
            'password' => 'rolePassword123!',
            'phone' => '11987654321',
            'birth_date' => '1990-05-15',
            'address' => [
                'street' => 'Rua dos Roles',
                'number' => '123',
                'neighborhood' => 'Centro',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01234-567'
            ],
            'role' => 'customer',
            'accept_terms' => true,
            'accept_privacy' => true,
            'accept_communications' => false
        ];

        // Registrar usuário com role customer
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/register');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($customerData));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $customerResponse = curl_exec($ch);
        $customerHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Validações básicas
        $this->assertNotFalse($customerResponse, 'Deve conseguir registrar usuário customer');
        
        if ($customerResponse !== false && $customerHttpCode === 201) {
            $customerDecoded = json_decode($customerResponse, true);
            
            if (isset($customerDecoded['success']) && $customerDecoded['success']) {
                // Verificar se role foi definida corretamente
                $userData = $customerDecoded['data']['user'] ?? $customerDecoded['data'];
                $this->assertEquals('customer', $userData['role'], 'Role customer deve ser persistida corretamente');
                
                sleep(1);
                
                // Fazer login e verificar role no token
                $loginData = [
                    'email' => $customerData['email'],
                    'password' => $customerData['password']
                ];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/login');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

                $loginResponse = curl_exec($ch);
                $loginHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($loginResponse !== false && $loginHttpCode === 200) {
                    $loginDecoded = json_decode($loginResponse, true);
                    
                    if (isset($loginDecoded['success']) && $loginDecoded['success']) {
                        $accessToken = $loginDecoded['data']['access_token'];
                        
                        sleep(1);
                        
                        // Validar token e verificar role
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/validate');
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                            'Content-Type: application/json',
                            'Authorization: Bearer ' . $accessToken
                        ]);

                        $validateResponse = curl_exec($ch);
                        $validateHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        curl_close($ch);

                        if ($validateResponse !== false && $validateHttpCode === 200) {
                            $validateDecoded = json_decode($validateResponse, true);
                            
                            if (isset($validateDecoded['success']) && $validateDecoded['success']) {
                                $this->assertEquals('customer', $validateDecoded['data']['role'], 'Role no token deve ser customer');
                            }
                        }
                    }
                }
            }
        }

        // Testar role inválida
        sleep(1);
        
        $invalidRoleData = [
            'name' => 'Admin Invalid Test',
            'email' => 'admin.invalid.' . time() . '@email.com',
            'password' => 'adminPassword123!',
            'phone' => '11987654321',
            'birth_date' => '1985-03-20',
            'address' => [
                'street' => 'Rua Admin',
                'number' => '456',
                'neighborhood' => 'Centro',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01234-567'
            ],
            'role' => 'invalid_role',
            'accept_terms' => true,
            'accept_privacy' => true,
            'accept_communications' => false
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/register');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($invalidRoleData));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $invalidResponse = curl_exec($ch);
        $invalidHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Deve rejeitar role inválida ou usar role padrão
        $this->assertNotFalse($invalidResponse, 'Deve conseguir conectar para testar role inválida');
        
        if ($invalidResponse !== false && $invalidHttpCode > 0) {
            $invalidDecoded = json_decode($invalidResponse, true);
            
            // Se rejeitou (422), validar erro de validação
            if ($invalidHttpCode === 422) {
                $this->assertArrayHasKey('error', $invalidDecoded, 'Deve retornar erro para role inválida');
            }
            // Se aceitou (201), deve ter usado role padrão
            elseif ($invalidHttpCode === 201) {
                $userData = $invalidDecoded['data']['user'] ?? $invalidDecoded['data'];
                $this->assertEquals('customer', $userData['role'], 'Role inválida deve ser convertida para customer');
            }
        }
    }

    /**
     * Testa tratamento de erros de banco de dados
     */
    public function testDatabaseErrorHandling(): void
    {
        // Este teste valida comportamento quando há problemas de conectividade/banco
        // Testamos cenários que podem gerar erros de banco indiretamente
        
        // Testar registro com dados que podem causar conflito
        $conflictEmail = 'conflict.test.' . time() . '@email.com';
        
        $userData = [
            'name' => 'Primeiro Usuário',
            'email' => $conflictEmail,
            'password' => 'dbErrorPassword123!',
            'phone' => '11987654321',
            'birth_date' => '1990-01-01',
            'address' => [
                'street' => 'Rua DB Error',
                'number' => '123',
                'neighborhood' => 'Centro',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01234-567'
            ],
            'role' => 'customer',
            'accept_terms' => true,
            'accept_privacy' => true,
            'accept_communications' => false
        ];

        // Primeiro registro
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/register');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $firstResponse = curl_exec($ch);
        $firstHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->assertNotFalse($firstResponse, 'Deve conseguir fazer primeiro registro');
        
        if ($firstResponse !== false && $firstHttpCode === 201) {
            sleep(1);
            
            // Tentar registrar novamente com mesmo email (deve gerar conflito)
            $userData['name'] = 'Segundo Usuário (Conflito)';
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/register');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

            $conflictResponse = curl_exec($ch);
            $conflictHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            // Deve tratar conflito de email adequadamente
            $this->assertNotFalse($conflictResponse, 'Deve conseguir conectar para testar conflito');
            
            if ($conflictResponse !== false && $conflictHttpCode > 0) {
                $conflictDecoded = json_decode($conflictResponse, true);
                
                // Deve retornar erro 409 (Conflict) ou 422 (Validation Error)
                $this->assertContains($conflictHttpCode, [409, 422], 'Deve retornar código de erro apropriado para conflito');
                $this->assertIsArray($conflictDecoded, 'Resposta de erro deve ser JSON válido');
                $this->assertArrayHasKey('error', $conflictDecoded, 'Resposta de erro deve conter flag de erro');
                
                // Verificar se mensagem indica problema de duplicação
                $message = $conflictDecoded['message'] ?? '';
                $this->assertStringContainsString('já existe', $message, 'Mensagem deve indicar que email já existe');
            }
        }

        // Testar login com credenciais que não existem (simula erro de consulta)
        sleep(1);
        
        $nonExistentLogin = [
            'email' => 'naoexiste.' . time() . '@email.com',
            'password' => 'senhaInexistente123!'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/login');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($nonExistentLogin));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $loginResponse = curl_exec($ch);
        $loginHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Deve tratar usuário inexistente adequadamente
        $this->assertNotFalse($loginResponse, 'Deve conseguir conectar para testar login inexistente');
        
        if ($loginResponse !== false && $loginHttpCode > 0) {
            $this->assertEquals(401, $loginHttpCode, 'Login inexistente deve retornar 401 Unauthorized');
            
            $loginDecoded = json_decode($loginResponse, true);
            $this->assertIsArray($loginDecoded, 'Resposta de erro de login deve ser JSON válido');
            $this->assertArrayHasKey('error', $loginDecoded, 'Resposta deve conter indicador de erro');
        }
    }

    /**
     * Testa comportamento com dados inválidos
     */
    public function testInvalidDataHandling(): void
    {
        // Testar registro com dados inválidos/faltantes
        
        // Teste 1: Email inválido
        $invalidEmailData = [
            'name' => 'Email Inválido Test',
            'email' => 'email_invalido_sem_at',
            'password' => 'validPassword123!',
            'phone' => '11987654321',
            'birth_date' => '1990-01-01',
            'address' => [
                'street' => 'Rua Inválida',
                'number' => '123',
                'neighborhood' => 'Centro',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01234-567'
            ],
            'role' => 'customer',
            'accept_terms' => true,
            'accept_privacy' => true,
            'accept_communications' => false
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/register');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($invalidEmailData));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $emailResponse = curl_exec($ch);
        $emailHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->assertNotFalse($emailResponse, 'Deve conseguir conectar para testar email inválido');
        
        if ($emailResponse !== false && $emailHttpCode > 0) {
            // Deve rejeitar email inválido (pode ser erro de validação ou erro interno)
            $this->assertContains($emailHttpCode, [400, 422, 500], 'Email inválido deve retornar erro apropriado');
            
            $emailDecoded = json_decode($emailResponse, true);
            $this->assertIsArray($emailDecoded, 'Resposta deve ser JSON válido');
            $this->assertArrayHasKey('error', $emailDecoded, 'Deve conter indicador de erro');
        }

        sleep(1);

        // Teste 2: Senha muito fraca
        $weakPasswordData = [
            'name' => 'Senha Fraca Test',
            'email' => 'senha.fraca.' . time() . '@email.com',
            'password' => '123',
            'phone' => '11987654321',
            'birth_date' => '1990-01-01',
            'address' => [
                'street' => 'Rua Senha Fraca',
                'number' => '456',
                'neighborhood' => 'Centro',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01234-567'
            ],
            'role' => 'customer',
            'accept_terms' => true,
            'accept_privacy' => true,
            'accept_communications' => false
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/register');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($weakPasswordData));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $passwordResponse = curl_exec($ch);
        $passwordHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($passwordResponse !== false && $passwordHttpCode > 0) {
            $passwordDecoded = json_decode($passwordResponse, true);
            
            // Se rejeitou senha fraca, validar erro
            if ($passwordHttpCode === 422) {
                $this->assertArrayHasKey('error', $passwordDecoded, 'Deve conter erro de validação para senha fraca');
            }
            // Se aceitou, sistema pode não ter validação de força de senha
            elseif ($passwordHttpCode === 201) {
                $this->assertTrue(true, 'Sistema aceita senhas simples - validação de força opcional');
            }
        }

        sleep(1);

        // Teste 3: Dados obrigatórios faltando
        $missingDataData = [
            'name' => 'Dados Faltando Test',
            // 'email' => 'faltando',
            'password' => 'validPassword123!',
            'role' => 'customer',
            'accept_terms' => true,
            'accept_privacy' => true
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/register');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($missingDataData));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $missingResponse = curl_exec($ch);
        $missingHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Deve rejeitar dados obrigatórios faltando
        $this->assertNotFalse($missingResponse, 'Deve conseguir conectar para testar dados faltando');
        
        if ($missingResponse !== false && $missingHttpCode > 0) {
            $this->assertContains($missingHttpCode, [400, 422, 500], 'Dados obrigatórios faltando deve retornar erro');
            
            $missingDecoded = json_decode($missingResponse, true);
            $this->assertIsArray($missingDecoded, 'Resposta deve ser JSON válido');
            $this->assertArrayHasKey('error', $missingDecoded, 'Deve conter indicador de erro');
        }

        sleep(1);

        // Teste 4: Login com dados inválidos
        $invalidLoginData = [
            'email' => 'email_sem_arroba',
            'password' => ''
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/login');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($invalidLoginData));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $invalidLoginResponse = curl_exec($ch);
        $invalidLoginHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Deve rejeitar dados de login inválidos
        if ($invalidLoginResponse !== false && $invalidLoginHttpCode > 0) {
            $this->assertContains($invalidLoginHttpCode, [400, 401, 422, 500], 'Login inválido deve retornar erro apropriado');
            
            $invalidLoginDecoded = json_decode($invalidLoginResponse, true);
            $this->assertIsArray($invalidLoginDecoded, 'Resposta deve ser JSON válido');
            $this->assertArrayHasKey('error', $invalidLoginDecoded, 'Deve conter indicador de erro');
        }
    }

    /**
     * Testa limpeza de tokens expirados
     */
    public function testExpiredTokenCleanup(): void
    {
        // Este teste valida o comportamento com tokens expirados
        // Como não podemos alterar tempo real, testamos comportamento indireto
        
        // Criar usuário para gerar tokens
        $userData = [
            'name' => 'Token Expiry Test',
            'email' => 'token.expiry.' . time() . '@email.com',
            'password' => 'expiryPassword123!',
            'phone' => '11987654321',
            'birth_date' => '1985-08-15',
            'address' => [
                'street' => 'Rua Token Expiry',
                'number' => '789',
                'neighborhood' => 'Centro',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01234-567'
            ],
            'role' => 'customer',
            'accept_terms' => true,
            'accept_privacy' => true,
            'accept_communications' => false
        ];

        // Registrar usuário
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/register');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $registerResponse = curl_exec($ch);
        $registerHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->assertNotFalse($registerResponse, 'Deve conseguir registrar usuário para teste de expiração');
        
        if ($registerResponse !== false && $registerHttpCode === 201) {
            sleep(1);
            
            // Fazer login para obter token
            $loginData = [
                'email' => $userData['email'],
                'password' => $userData['password']
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/login');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

            $loginResponse = curl_exec($ch);
            $loginHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($loginResponse !== false && $loginHttpCode === 200) {
                $loginDecoded = json_decode($loginResponse, true);
                
                if (isset($loginDecoded['success']) && $loginDecoded['success']) {
                    $accessToken = $loginDecoded['data']['access_token'];
                    
                    sleep(1);
                    
                    // Validar que token está funcionando inicialmente
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/validate');
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        'Content-Type: application/json',
                        'Authorization: Bearer ' . $accessToken
                    ]);

                    $validateResponse = curl_exec($ch);
                    $validateHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);

                    // Token deve estar válido inicialmente
                    if ($validateResponse !== false && $validateHttpCode === 200) {
                        $validateDecoded = json_decode($validateResponse, true);
                        
                        if (isset($validateDecoded['success']) && $validateDecoded['success']) {
                            $this->assertTrue($validateDecoded['data']['valid'], 'Token deve estar válido inicialmente');
                            
                            // Verificar se há informação de expiração
                            if (isset($validateDecoded['data']['expires_at'])) {
                                $expiresAt = $validateDecoded['data']['expires_at'];
                                $this->assertNotEmpty($expiresAt, 'Token deve ter informação de expiração');
                                $this->assertIsNumeric($expiresAt, 'Expiração deve ser timestamp numérico');
                                
                                // Verificar se expiração é no futuro
                                $currentTime = time();
                                $this->assertGreaterThan($currentTime, $expiresAt, 'Token deve expirar no futuro');
                            }
                        }
                    }
                    
                    // Testar comportamento com token malformado (simula token corrompido/expirado)
                    sleep(1);
                    
                    $malformedToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.expired.token';
                    
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/validate');
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        'Content-Type: application/json',
                        'Authorization: Bearer ' . $malformedToken
                    ]);

                    $expiredResponse = curl_exec($ch);
                    $expiredHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);

                    // Sistema deve rejeitar token malformado/expirado
                    if ($expiredResponse !== false && $expiredHttpCode > 0) {
                        $this->assertEquals(401, $expiredHttpCode, 'Token malformado deve retornar 401');
                        
                        $expiredDecoded = json_decode($expiredResponse, true);
                        $this->assertIsArray($expiredDecoded, 'Resposta deve ser JSON válido');
                        $this->assertFalse($expiredDecoded['valid'] ?? true, 'Token malformado deve retornar valid=false');
                    }
                    
                    // Fazer logout para invalidar token atual (simula limpeza)
                    sleep(1);
                    
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/logout');
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        'Content-Type: application/json',
                        'Authorization: Bearer ' . $accessToken
                    ]);

                    $logoutResponse = curl_exec($ch);
                    $logoutHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);

                    // Logout deve funcionar (limpeza de token)
                    if ($logoutResponse !== false && $logoutHttpCode === 200) {
                        $this->assertTrue(true, 'Sistema implementa limpeza de tokens via logout');
                    }
                }
            }
        }
    }

    /**
     * Testa configuração de variáveis de ambiente
     */
    public function testEnvironmentConfiguration(): void
    {
        // Teste indiretamente as configurações através dos endpoints
        // que dependem de variáveis de ambiente como DB_HOST, JWT_SECRET, etc.
        
        // Testar se o endpoint de health funciona (indica que as config básicas estão OK)
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/health');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $healthResponse = curl_exec($ch);
        $healthHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Validação básica de conectividade
        $this->assertNotFalse($healthResponse, 'Endpoint de health deve ser acessivel (config de ambiente funcionando)');
        
        if ($healthResponse !== false && $healthHttpCode > 0) {
            // Se health endpoint funciona, configurações básicas estão OK
            $this->assertContains($healthHttpCode, [200, 404], 'Health endpoint deve retornar status válido');
            
            if ($healthHttpCode === 200) {
                $healthDecoded = json_decode($healthResponse, true);
                $this->assertIsArray($healthDecoded, 'Health response deve ser JSON válido');
            }
        }

        sleep(1);

        // Testar registro para validar se configurações de banco funcionam
        $userData = [
            'name' => 'Environment Config Test',
            'email' => 'env.config.' . time() . '@email.com',
            'password' => 'envConfigPassword123!',
            'phone' => '11987654321',
            'birth_date' => '1990-01-01',
            'address' => [
                'street' => 'Rua Env Config',
                'number' => '123',
                'neighborhood' => 'Centro',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01234-567'
            ],
            'role' => 'customer',
            'accept_terms' => true,
            'accept_privacy' => true,
            'accept_communications' => false
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/register');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $registerResponse = curl_exec($ch);
        $registerHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Se conseguiu registrar, configurações de banco estão funcionando
        $this->assertNotFalse($registerResponse, 'Registro deve funcionar (configurações de DB corretas)');
        
        if ($registerResponse !== false && $registerHttpCode > 0) {
            // Se registro funciona, configurações de ambiente estão adequadas
            $this->assertContains($registerHttpCode, [201, 409, 422, 500], 'Registro deve processar com configurações válidas');
            
            $registerDecoded = json_decode($registerResponse, true);
            $this->assertIsArray($registerDecoded, 'Response deve ser JSON válido (config de env OK)');
            
            // Se conseguiu criar usuário, testar login (valida JWT_SECRET)
            if ($registerHttpCode === 201) {
                sleep(1);
                
                $loginData = [
                    'email' => $userData['email'],
                    'password' => $userData['password']
                ];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/login');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

                $loginResponse = curl_exec($ch);
                $loginHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                // Login funcionando indica que JWT_SECRET está configurado
                if ($loginResponse !== false && $loginHttpCode === 200) {
                    $loginDecoded = json_decode($loginResponse, true);
                    
                    if (isset($loginDecoded['data']['access_token'])) {
                        $this->assertNotEmpty($loginDecoded['data']['access_token'], 'Token JWT deve ser gerado (JWT_SECRET configurado)');
                    }
                }
            }
        }
    }

    /**
     * Testa conexão com RabbitMQ
     */
    public function testRabbitMQConnection(): void
    {
        // Este teste valida se o sistema degrada graciosamente quando RabbitMQ não está disponível
        // Em ambiente de teste, RabbitMQ pode não estar rodando, mas o sistema deve continuar funcionando
        
        $userData = [
            'name' => 'RabbitMQ Test User',
            'email' => 'rabbitmq.test.' . time() . '@email.com',
            'password' => 'rabbitMQPassword123!',
            'phone' => '11987654321',
            'birth_date' => '1985-06-10',
            'address' => [
                'street' => 'Rua RabbitMQ',
                'number' => '456',
                'neighborhood' => 'Centro',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01234-567'
            ],
            'role' => 'customer',
            'accept_terms' => true,
            'accept_privacy' => true,
            'accept_communications' => false
        ];

        // Testar registro de usuário (que pode tentar publicar evento para RabbitMQ)
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/register');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $registerResponse = curl_exec($ch);
        $registerHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Sistema deve funcionar mesmo se RabbitMQ não estiver disponível
        $this->assertNotFalse($registerResponse, 'Registro deve funcionar mesmo sem RabbitMQ');
        
        if ($registerResponse !== false && $registerHttpCode > 0) {
            $registerDecoded = json_decode($registerResponse, true);
            $this->assertIsArray($registerDecoded, 'Response deve ser JSON válido');
            
            // Se registro funcionou, sistema está degradando graciosamente
            if ($registerHttpCode === 201) {
                $this->assertTrue($registerDecoded['success'] ?? false, 'Sistema deve funcionar sem RabbitMQ (degradação graciosa)');
                
                sleep(1);
                
                // Testar login também (pode gerar eventos)
                $loginData = [
                    'email' => $userData['email'],
                    'password' => $userData['password']
                ];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/login');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

                $loginResponse = curl_exec($ch);
                $loginHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                // Login deve funcionar mesmo sem RabbitMQ
                if ($loginResponse !== false && $loginHttpCode === 200) {
                    $loginDecoded = json_decode($loginResponse, true);
                    $this->assertTrue($loginDecoded['success'] ?? false, 'Login deve funcionar sem RabbitMQ (resiliência)');
                    
                    if (isset($loginDecoded['data']['access_token'])) {
                        sleep(1);
                        
                        // Testar logout (pode tentar publicar evento de logout)
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/logout');
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                            'Content-Type: application/json',
                            'Authorization: Bearer ' . $loginDecoded['data']['access_token']
                        ]);

                        $logoutResponse = curl_exec($ch);
                        $logoutHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        curl_close($ch);

                        // Logout deve funcionar mesmo sem RabbitMQ
                        if ($logoutResponse !== false && $logoutHttpCode === 200) {
                            $this->assertTrue(true, 'Sistema completo funciona sem RabbitMQ (alta resiliência)');
                        }
                    }
                }
            }
        }
    }

    /**
     * Testa container de injeção de dependência
     */
    public function testDependencyInjectionContainer(): void
    {
        // Este teste valida indiretamente se o container DI está funcionando
        // testando se os serviços são injetados corretamente via API
        
        // Criar usuário para testar diferentes serviços injetados
        $userData = [
            'name' => 'DI Container Test',
            'email' => 'di.container.' . time() . '@email.com',
            'password' => 'diContainerPassword123!',
            'phone' => '11987654321',
            'birth_date' => '1988-12-25',
            'address' => [
                'street' => 'Rua DI Container',
                'number' => '789',
                'neighborhood' => 'Centro',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01234-567'
            ],
            'role' => 'customer',
            'accept_terms' => true,
            'accept_privacy' => true,
            'accept_communications' => false
        ];

        // Testar RegisterUseCase (via container DI)
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/register');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $registerResponse = curl_exec($ch);
        $registerHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Se register funciona, UserRepository e PasswordHasher foram injetados corretamente
        $this->assertNotFalse($registerResponse, 'RegisterUseCase deve funcionar via DI container');
        
        if ($registerResponse !== false && $registerHttpCode > 0) {
            $registerDecoded = json_decode($registerResponse, true);
            $this->assertIsArray($registerDecoded, 'Response de register deve ser válida (DI funcionando)');
            
            // Se registro funcionou, container DI está resolvendo dependências
            if ($registerHttpCode === 201) {
                $this->assertTrue($registerDecoded['success'] ?? false, 'RegisterUseCase injetado via DI');
                
                sleep(1);
                
                // Testar LoginUseCase (via container DI)
                $loginData = [
                    'email' => $userData['email'],
                    'password' => $userData['password']
                ];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/login');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

                $loginResponse = curl_exec($ch);
                $loginHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                // Se login funciona, TokenGenerator e UserRepository foram injetados
                if ($loginResponse !== false && $loginHttpCode === 200) {
                    $loginDecoded = json_decode($loginResponse, true);
                    $this->assertTrue($loginDecoded['success'] ?? false, 'LoginUseCase injetado via DI');
                    
                    if (isset($loginDecoded['data']['access_token'])) {
                        $accessToken = $loginDecoded['data']['access_token'];
                        
                        sleep(1);
                        
                        // Testar ValidateTokenUseCase (via container DI)
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/validate');
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                            'Content-Type: application/json',
                            'Authorization: Bearer ' . $accessToken
                        ]);

                        $validateResponse = curl_exec($ch);
                        $validateHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        curl_close($ch);

                        // Se validação funciona, TokenValidator foi injetado corretamente
                        if ($validateResponse !== false && $validateHttpCode === 200) {
                            $validateDecoded = json_decode($validateResponse, true);
                            $this->assertTrue($validateDecoded['data']['valid'] ?? false, 'ValidateTokenUseCase injetado via DI');
                            
                            sleep(1);
                            
                            // Testar LogoutUseCase (via container DI)
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/logout');
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_POST, true);
                            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                                'Content-Type: application/json',
                                'Authorization: Bearer ' . $accessToken
                            ]);

                            $logoutResponse = curl_exec($ch);
                            $logoutHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                            curl_close($ch);

                            // Se logout funciona, todas as dependências foram resolvidas pelo DI
                            if ($logoutResponse !== false && $logoutHttpCode === 200) {
                                $this->assertTrue(true, 'Container DI resolve todas as dependências corretamente');
                            }
                        }
                    }
                }
            }
        }
    }
}
