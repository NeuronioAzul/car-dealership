<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class AuthServiceIntegrationTest extends TestCase
{
    private Client $httpClient;
    private string $baseUrl;
    private array $testUser;

    protected function setUp(): void
    {
        $this->baseUrl = TEST_BASE_URL;
        $this->httpClient = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 30,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]
        ]);

        $this->testUser = [
            'name' => 'Teste Integração',
            'email' => 'teste_' . time() . '@email.com',
            'password' => 'senha123',
            'cpf' => '12345678901',
            'phone' => '11999999999',
            'address' => [
                'street' => 'Rua Teste, 123',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01234-567'
            ]
        ];
    }

    public function testHealthCheck(): void
    {
        try {
            $response = $this->httpClient->get('/auth/health');
            
            $this->assertEquals(200, $response->getStatusCode());
            
            $body = json_decode($response->getBody()->getContents(), true);
            $this->assertTrue($body['success']);
            $this->assertEquals('auth-service', $body['service']);
            
        } catch (RequestException $e) {
            $this->fail('Auth Service health check failed: ' . $e->getMessage());
        }
    }

    public function testUserRegistration(): void
    {
        try {
            $response = $this->httpClient->post('/auth/register', [
                'json' => $this->testUser
            ]);
            
            $this->assertEquals(201, $response->getStatusCode());
            
            $body = json_decode($response->getBody()->getContents(), true);
            $this->assertTrue($body['success']);
            $this->assertArrayHasKey('data', $body);
            $this->assertArrayHasKey('access_token', $body['data']);
            $this->assertArrayHasKey('user', $body['data']);
            
            $user = $body['data']['user'];
            $this->assertEquals($this->testUser['name'], $user['name']);
            $this->assertEquals($this->testUser['email'], $user['email']);
            $this->assertEquals('customer', $user['role']);
            
        } catch (RequestException $e) {
            $this->fail('User registration failed: ' . $e->getMessage());
        }
    }

    public function testUserLogin(): void
    {
        // Primeiro registrar o usuário
        $this->testUserRegistration();
        
        try {
            $response = $this->httpClient->post('/auth/login', [
                'json' => [
                    'email' => $this->testUser['email'],
                    'password' => $this->testUser['password']
                ]
            ]);
            
            $this->assertEquals(200, $response->getStatusCode());
            
            $body = json_decode($response->getBody()->getContents(), true);
            $this->assertTrue($body['success']);
            $this->assertArrayHasKey('access_token', $body['data']);
            $this->assertArrayHasKey('refresh_token', $body['data']);
            $this->assertArrayHasKey('expires_in', $body['data']);
            
        } catch (RequestException $e) {
            $this->fail('User login failed: ' . $e->getMessage());
        }
    }

    public function testTokenValidation(): void
    {
        // Registrar e fazer login para obter token
        $this->httpClient->post('/auth/register', ['json' => $this->testUser]);
        
        $loginResponse = $this->httpClient->post('/auth/login', [
            'json' => [
                'email' => $this->testUser['email'],
                'password' => $this->testUser['password']
            ]
        ]);
        
        $loginBody = json_decode($loginResponse->getBody()->getContents(), true);
        $token = $loginBody['data']['access_token'];
        
        try {
            $response = $this->httpClient->post('/auth/validate', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token
                ]
            ]);
            
            $this->assertEquals(200, $response->getStatusCode());
            
            $body = json_decode($response->getBody()->getContents(), true);
            $this->assertTrue($body['success']);
            
        } catch (RequestException $e) {
            $this->fail('Token validation failed: ' . $e->getMessage());
        }
    }

    public function testInvalidLogin(): void
    {
        try {
            $response = $this->httpClient->post('/auth/login', [
                'json' => [
                    'email' => 'inexistente@email.com',
                    'password' => 'senhaerrada'
                ]
            ]);
            
            $this->assertEquals(401, $response->getStatusCode());
            
        } catch (RequestException $e) {
            // Esperamos uma exceção 401
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
        }
    }

    public function testDuplicateEmailRegistration(): void
    {
        // Registrar usuário pela primeira vez
        $this->httpClient->post('/auth/register', ['json' => $this->testUser]);
        
        try {
            // Tentar registrar novamente com o mesmo email
            $response = $this->httpClient->post('/auth/register', [
                'json' => $this->testUser
            ]);
            
            $this->assertEquals(409, $response->getStatusCode());
            
        } catch (RequestException $e) {
            // Esperamos uma exceção 409 (Conflict)
            $this->assertEquals(409, $e->getResponse()->getStatusCode());
        }
    }

    public function testInvalidTokenValidation(): void
    {
        try {
            $response = $this->httpClient->post('/auth/validate', [
                'headers' => [
                    'Authorization' => 'Bearer invalid-token-here'
                ]
            ]);
            
            $this->assertEquals(401, $response->getStatusCode());
            
        } catch (RequestException $e) {
            // Esperamos uma exceção 401
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
        }
    }

    public function testTokenRefresh(): void
    {
        // Registrar e fazer login
        $this->httpClient->post('/auth/register', ['json' => $this->testUser]);
        
        $loginResponse = $this->httpClient->post('/auth/login', [
            'json' => [
                'email' => $this->testUser['email'],
                'password' => $this->testUser['password']
            ]
        ]);
        
        $loginBody = json_decode($loginResponse->getBody()->getContents(), true);
        $refreshToken = $loginBody['data']['refresh_token'];
        
        try {
            $response = $this->httpClient->post('/auth/refresh', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $refreshToken
                ]
            ]);
            
            $this->assertEquals(200, $response->getStatusCode());
            
            $body = json_decode($response->getBody()->getContents(), true);
            $this->assertTrue($body['success']);
            $this->assertArrayHasKey('access_token', $body['data']);
            
        } catch (RequestException $e) {
            $this->fail('Token refresh failed: ' . $e->getMessage());
        }
    }

    public function testRegistrationValidation(): void
    {
        $invalidUser = [
            'name' => '', // Nome vazio
            'email' => 'email-invalido', // Email inválido
            'password' => '123', // Senha muito curta
            'cpf' => '123', // CPF inválido
            'phone' => '123' // Telefone inválido
        ];
        
        try {
            $response = $this->httpClient->post('/auth/register', [
                'json' => $invalidUser
            ]);
            
            $this->assertEquals(400, $response->getStatusCode());
            
        } catch (RequestException $e) {
            // Esperamos uma exceção 400 (Bad Request)
            $this->assertEquals(400, $e->getResponse()->getStatusCode());
        }
    }
}

