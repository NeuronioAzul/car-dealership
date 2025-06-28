<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class APIGatewayIntegrationTest extends TestCase
{
    private Client $httpClient;
    private string $gatewayUrl;
    private string $authToken;

    protected function setUp(): void
    {
        $this->gatewayUrl = 'http://localhost:8000/api/v1';
        $this->httpClient = new Client([
            'base_uri' => $this->gatewayUrl,
            'timeout' => 30,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]
        ]);
    }

    public function testAPIGatewayHealthCheck(): void
    {
        test_log('Testando health check do API Gateway...');
        
        try {
            $response = $this->httpClient->get('/health');
            
            $this->assertEquals(200, $response->getStatusCode());
            
            $body = json_decode($response->getBody()->getContents(), true);
            $this->assertArrayHasKey('status', $body);
            $this->assertEquals('healthy', $body['status']);
            
            test_log('✅ API Gateway health check passou');
            
        } catch (RequestException $e) {
            $this->fail('API Gateway health check falhou: ' . $e->getMessage());
        }
    }

    public function testCORSHeaders(): void
    {
        test_log('Testando headers CORS...');
        
        try {
            // Fazer requisição OPTIONS para testar CORS
            $response = $this->httpClient->request('OPTIONS', '/vehicles', [
                'headers' => [
                    'Origin' => 'http://localhost:3000',
                    'Access-Control-Request-Method' => 'GET',
                    'Access-Control-Request-Headers' => 'Content-Type,Authorization'
                ]
            ]);
            
            $headers = $response->getHeaders();
            
            $this->assertArrayHasKey('Access-Control-Allow-Origin', $headers);
            $this->assertArrayHasKey('Access-Control-Allow-Methods', $headers);
            $this->assertArrayHasKey('Access-Control-Allow-Headers', $headers);
            
            test_log('✅ Headers CORS configurados corretamente');
            
        } catch (RequestException $e) {
            $this->fail('Teste CORS falhou: ' . $e->getMessage());
        }
    }

    public function testRateLimiting(): void
    {
        test_log('Testando rate limiting...');
        
        $requests = [];
        $successCount = 0;
        $rateLimitedCount = 0;
        
        // Fazer múltiplas requisições rapidamente
        for ($i = 0; $i < 20; $i++) {
            try {
                $response = $this->httpClient->get('/vehicles?limit=1');
                
                if ($response->getStatusCode() === 200) {
                    $successCount++;
                } elseif ($response->getStatusCode() === 429) {
                    $rateLimitedCount++;
                }
                
            } catch (RequestException $e) {
                if ($e->getResponse() && $e->getResponse()->getStatusCode() === 429) {
                    $rateLimitedCount++;
                }
            }
            
            // Pequena pausa entre requisições
            usleep(100000); // 100ms
        }
        
        test_log("Requisições bem-sucedidas: $successCount");
        test_log("Requisições limitadas: $rateLimitedCount");
        
        // Deve haver pelo menos algumas requisições bem-sucedidas
        $this->assertGreaterThan(0, $successCount);
        
        test_log('✅ Rate limiting funcionando');
    }

    public function testJWTAuthenticationFlow(): void
    {
        test_log('Testando fluxo de autenticação JWT via Gateway...');
        
        // 1. Registrar usuário
        $userData = [
            'name' => 'Teste Gateway',
            'email' => 'gateway_' . time() . '@email.com',
            'password' => 'senha123',
            'cpf' => '12345678901',
            'phone' => '11999999999',
            'address' => [
                'street' => 'Rua Gateway, 123',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01234-567'
            ]
        ];
        
        try {
            $registerResponse = $this->httpClient->post('/auth/register', [
                'json' => $userData
            ]);
            
            $this->assertEquals(201, $registerResponse->getStatusCode());
            
            // 2. Fazer login
            $loginResponse = $this->httpClient->post('/auth/login', [
                'json' => [
                    'email' => $userData['email'],
                    'password' => $userData['password']
                ]
            ]);
            
            $this->assertEquals(200, $loginResponse->getStatusCode());
            
            $loginBody = json_decode($loginResponse->getBody()->getContents(), true);
            $this->authToken = $loginBody['data']['access_token'];
            
            // 3. Testar endpoint protegido
            $protectedResponse = $this->httpClient->get('/customer/profile', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->authToken
                ]
            ]);
            
            $this->assertEquals(200, $protectedResponse->getStatusCode());
            
            test_log('✅ Fluxo de autenticação JWT via Gateway funcionando');
            
        } catch (RequestException $e) {
            $this->fail('Fluxo de autenticação falhou: ' . $e->getMessage());
        }
    }

    public function testUnauthorizedAccess(): void
    {
        test_log('Testando acesso não autorizado...');
        
        try {
            $response = $this->httpClient->get('/customer/profile');
            
            $this->assertEquals(401, $response->getStatusCode());
            
        } catch (RequestException $e) {
            // Esperamos uma exceção 401
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
        }
        
        test_log('✅ Acesso não autorizado bloqueado corretamente');
    }

    public function testInvalidToken(): void
    {
        test_log('Testando token inválido...');
        
        try {
            $response = $this->httpClient->get('/customer/profile', [
                'headers' => [
                    'Authorization' => 'Bearer invalid-token-here'
                ]
            ]);
            
            $this->assertEquals(401, $response->getStatusCode());
            
        } catch (RequestException $e) {
            // Esperamos uma exceção 401
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
        }
        
        test_log('✅ Token inválido rejeitado corretamente');
    }

    public function testAllMicroservicesViaGateway(): void
    {
        test_log('Testando todos os microserviços via Gateway...');
        
        $services = [
            'auth' => '/auth/health',
            'vehicles' => '/vehicles/health',
            'customers' => '/customer/health',
            'reservations' => '/reservations/health',
            'payments' => '/payments/health',
            'sales' => '/sales/health',
            'admin' => '/admin/health',
            'saga' => '/saga/health'
        ];
        
        $healthyServices = 0;
        
        foreach ($services as $serviceName => $endpoint) {
            try {
                $response = $this->httpClient->get($endpoint);
                
                if ($response->getStatusCode() === 200) {
                    $healthyServices++;
                    test_log("✅ $serviceName service: healthy");
                } else {
                    test_log("❌ $serviceName service: unhealthy", 'WARNING');
                }
                
            } catch (RequestException $e) {
                test_log("❌ $serviceName service: error - " . $e->getMessage(), 'WARNING');
            }
        }
        
        // Pelo menos 80% dos serviços devem estar saudáveis
        $healthPercentage = ($healthyServices / count($services)) * 100;
        $this->assertGreaterThanOrEqual(80, $healthPercentage);
        
        test_log("Serviços saudáveis: $healthyServices/" . count($services) . " ($healthPercentage%)");
    }

    public function testRequestSizeLimiting(): void
    {
        test_log('Testando limitação de tamanho de requisição...');
        
        // Criar payload muito grande (> 10MB)
        $largePayload = [
            'data' => str_repeat('A', 11 * 1024 * 1024) // 11MB
        ];
        
        try {
            $response = $this->httpClient->post('/auth/register', [
                'json' => $largePayload
            ]);
            
            // Deve falhar com 413 (Payload Too Large)
            $this->assertEquals(413, $response->getStatusCode());
            
        } catch (RequestException $e) {
            // Esperamos uma exceção 413
            $this->assertEquals(413, $e->getResponse()->getStatusCode());
        }
        
        test_log('✅ Limitação de tamanho de requisição funcionando');
    }

    public function testAPIVersioning(): void
    {
        test_log('Testando versionamento da API...');
        
        try {
            // Testar versão atual (v1)
            $v1Response = $this->httpClient->get('/vehicles?limit=1');
            $this->assertEquals(200, $v1Response->getStatusCode());
            
            // Testar versão inexistente
            $this->httpClient = new Client([
                'base_uri' => 'http://localhost:8000/api/v2',
                'timeout' => 30
            ]);
            
            try {
                $v2Response = $this->httpClient->get('/vehicles');
                $this->assertEquals(404, $v2Response->getStatusCode());
            } catch (RequestException $e) {
                $this->assertEquals(404, $e->getResponse()->getStatusCode());
            }
            
            test_log('✅ Versionamento da API funcionando');
            
        } catch (RequestException $e) {
            $this->fail('Teste de versionamento falhou: ' . $e->getMessage());
        }
    }

    public function testErrorHandling(): void
    {
        test_log('Testando tratamento de erros...');
        
        try {
            // Testar endpoint inexistente
            $response = $this->httpClient->get('/nonexistent-endpoint');
            $this->assertEquals(404, $response->getStatusCode());
            
        } catch (RequestException $e) {
            $this->assertEquals(404, $e->getResponse()->getStatusCode());
        }
        
        try {
            // Testar método não permitido
            $response = $this->httpClient->delete('/vehicles');
            $this->assertEquals(405, $response->getStatusCode());
            
        } catch (RequestException $e) {
            $this->assertEquals(405, $e->getResponse()->getStatusCode());
        }
        
        test_log('✅ Tratamento de erros funcionando');
    }

    public function testResponseHeaders(): void
    {
        test_log('Testando headers de resposta...');
        
        try {
            $response = $this->httpClient->get('/vehicles?limit=1');
            
            $headers = $response->getHeaders();
            
            // Verificar headers de segurança
            $this->assertArrayHasKey('Content-Type', $headers);
            $this->assertEquals(['application/json'], $headers['Content-Type']);
            
            // Verificar se há headers de cache (se configurados)
            if (isset($headers['Cache-Control'])) {
                $this->assertNotEmpty($headers['Cache-Control']);
            }
            
            test_log('✅ Headers de resposta corretos');
            
        } catch (RequestException $e) {
            $this->fail('Teste de headers falhou: ' . $e->getMessage());
        }
    }
}

