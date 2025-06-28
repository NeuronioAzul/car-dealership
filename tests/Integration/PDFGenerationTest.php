<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class PDFGenerationTest extends TestCase
{
    private Client $httpClient;
    private string $baseUrl;
    private string $authToken;
    private string $saleId;

    protected function setUp(): void
    {
        $this->baseUrl = TEST_BASE_URL;
        $this->httpClient = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 60, // Timeout maior para geração de PDF
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]
        ]);
    }

    public function testPDFGenerationFlow(): void
    {
        test_log('Testando fluxo completo de geração de PDFs...');
        
        // 1. Configurar usuário e autenticação
        $this->setupUserAndAuth();
        
        // 2. Criar uma venda para gerar PDFs
        $this->createSampleSale();
        
        // 3. Testar geração de contrato
        $this->testContractGeneration();
        
        // 4. Testar geração de nota fiscal
        $this->testInvoiceGeneration();
        
        // 5. Verificar conteúdo dos PDFs
        $this->verifyPDFContent();
        
        test_log('✅ Fluxo completo de geração de PDFs concluído');
    }

    private function setupUserAndAuth(): void
    {
        test_log('Configurando usuário e autenticação...');
        
        $userData = [
            'name' => 'Cliente PDF Test',
            'email' => 'pdf_test_' . time() . '@email.com',
            'password' => 'senha123',
            'cpf' => '98765432100',
            'phone' => '11888888888',
            'address' => [
                'street' => 'Rua PDF, 123',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01234-567'
            ]
        ];
        
        try {
            // Registrar usuário
            $this->httpClient->post('/auth/register', ['json' => $userData]);
            
            // Fazer login
            $loginResponse = $this->httpClient->post('/auth/login', [
                'json' => [
                    'email' => $userData['email'],
                    'password' => $userData['password']
                ]
            ]);
            
            $loginBody = json_decode($loginResponse->getBody()->getContents(), true);
            $this->authToken = $loginBody['data']['access_token'];
            
            test_log('Usuário configurado e autenticado');
            
        } catch (RequestException $e) {
            $this->fail('Falha na configuração do usuário: ' . $e->getMessage());
        }
    }

    private function createSampleSale(): void
    {
        test_log('Criando venda de exemplo...');
        
        try {
            // Buscar veículo disponível
            $vehiclesResponse = $this->httpClient->get('/vehicles?available_only=true&limit=1');
            $vehiclesBody = json_decode($vehiclesResponse->getBody()->getContents(), true);
            
            if (empty($vehiclesBody['data']['vehicles'])) {
                $this->markTestSkipped('Nenhum veículo disponível para teste');
            }
            
            $vehicle = $vehiclesBody['data']['vehicles'][0];
            
            // Simular criação de venda diretamente (para teste)
            // Em produção, isso seria feito via SAGA
            $saleData = [
                'vehicle_id' => $vehicle['id'],
                'customer_data' => [
                    'name' => 'Cliente PDF Test',
                    'cpf' => '98765432100',
                    'email' => 'pdf_test@email.com',
                    'phone' => '11888888888',
                    'address' => 'Rua PDF, 123 - São Paulo/SP'
                ],
                'payment_data' => [
                    'method' => 'credit_card',
                    'amount' => $vehicle['price'],
                    'status' => 'completed'
                ]
            ];
            
            $saleResponse = $this->httpClient->post('/sales', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->authToken
                ],
                'json' => $saleData
            ]);
            
            $this->assertEquals(201, $saleResponse->getStatusCode());
            
            $saleBody = json_decode($saleResponse->getBody()->getContents(), true);
            $this->saleId = $saleBody['data']['sale']['id'];
            
            test_log("Venda criada: {$this->saleId}");
            
        } catch (RequestException $e) {
            $this->fail('Falha ao criar venda: ' . $e->getMessage());
        }
    }

    private function testContractGeneration(): void
    {
        test_log('Testando geração de contrato PDF...');
        
        try {
            $response = $this->httpClient->get("/sales/{$this->saleId}/contract", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->authToken
                ]
            ]);
            
            $this->assertEquals(200, $response->getStatusCode());
            
            // Verificar Content-Type
            $contentType = $response->getHeader('Content-Type')[0];
            $this->assertEquals('application/pdf', $contentType);
            
            // Verificar Content-Disposition
            $contentDisposition = $response->getHeader('Content-Disposition')[0];
            $this->assertStringContains('attachment', $contentDisposition);
            $this->assertStringContains('contrato', strtolower($contentDisposition));
            
            // Verificar tamanho do arquivo
            $pdfContent = $response->getBody()->getContents();
            $this->assertGreaterThan(1000, strlen($pdfContent)); // PDF deve ter pelo menos 1KB
            
            // Verificar assinatura PDF
            $this->assertStringStartsWith('%PDF-', $pdfContent);
            
            test_log('✅ Contrato PDF gerado corretamente');
            
        } catch (RequestException $e) {
            $this->fail('Falha na geração do contrato: ' . $e->getMessage());
        }
    }

    private function testInvoiceGeneration(): void
    {
        test_log('Testando geração de nota fiscal PDF...');
        
        try {
            $response = $this->httpClient->get("/sales/{$this->saleId}/invoice", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->authToken
                ]
            ]);
            
            $this->assertEquals(200, $response->getStatusCode());
            
            // Verificar Content-Type
            $contentType = $response->getHeader('Content-Type')[0];
            $this->assertEquals('application/pdf', $contentType);
            
            // Verificar Content-Disposition
            $contentDisposition = $response->getHeader('Content-Disposition')[0];
            $this->assertStringContains('attachment', $contentDisposition);
            $this->assertStringContains('nota', strtolower($contentDisposition));
            
            // Verificar tamanho do arquivo
            $pdfContent = $response->getBody()->getContents();
            $this->assertGreaterThan(1000, strlen($pdfContent)); // PDF deve ter pelo menos 1KB
            
            // Verificar assinatura PDF
            $this->assertStringStartsWith('%PDF-', $pdfContent);
            
            test_log('✅ Nota fiscal PDF gerada corretamente');
            
        } catch (RequestException $e) {
            $this->fail('Falha na geração da nota fiscal: ' . $e->getMessage());
        }
    }

    private function verifyPDFContent(): void
    {
        test_log('Verificando conteúdo dos PDFs...');
        
        try {
            // Obter detalhes da venda para verificar dados
            $saleResponse = $this->httpClient->get("/sales/{$this->saleId}", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->authToken
                ]
            ]);
            
            $saleBody = json_decode($saleResponse->getBody()->getContents(), true);
            $sale = $saleBody['data']['sale'];
            
            // Verificar se os dados da venda estão completos
            $this->assertArrayHasKey('vehicle', $sale);
            $this->assertArrayHasKey('customer', $sale);
            $this->assertArrayHasKey('payment', $sale);
            $this->assertArrayHasKey('total_amount', $sale);
            $this->assertArrayHasKey('sale_date', $sale);
            
            // Verificar dados do veículo
            $vehicle = $sale['vehicle'];
            $this->assertArrayHasKey('brand', $vehicle);
            $this->assertArrayHasKey('model', $vehicle);
            $this->assertArrayHasKey('year', $vehicle);
            $this->assertArrayHasKey('price', $vehicle);
            
            // Verificar dados do cliente
            $customer = $sale['customer'];
            $this->assertArrayHasKey('name', $customer);
            $this->assertArrayHasKey('cpf', $customer);
            $this->assertArrayHasKey('email', $customer);
            
            test_log('✅ Dados da venda verificados para PDFs');
            
        } catch (RequestException $e) {
            $this->fail('Falha na verificação dos dados: ' . $e->getMessage());
        }
    }

    public function testPDFAccessControl(): void
    {
        test_log('Testando controle de acesso aos PDFs...');
        
        // Primeiro criar uma venda
        $this->setupUserAndAuth();
        $this->createSampleSale();
        
        try {
            // Tentar acessar PDF sem autenticação
            $unauthorizedClient = new Client([
                'base_uri' => $this->baseUrl,
                'timeout' => 30
            ]);
            
            $response = $unauthorizedClient->get("/sales/{$this->saleId}/contract");
            $this->assertEquals(401, $response->getStatusCode());
            
        } catch (RequestException $e) {
            // Esperamos uma exceção 401
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
        }
        
        try {
            // Tentar acessar PDF com token inválido
            $response = $this->httpClient->get("/sales/{$this->saleId}/contract", [
                'headers' => [
                    'Authorization' => 'Bearer invalid-token'
                ]
            ]);
            $this->assertEquals(401, $response->getStatusCode());
            
        } catch (RequestException $e) {
            // Esperamos uma exceção 401
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
        }
        
        test_log('✅ Controle de acesso aos PDFs funcionando');
    }

    public function testPDFForNonexistentSale(): void
    {
        test_log('Testando PDF para venda inexistente...');
        
        $this->setupUserAndAuth();
        
        try {
            $response = $this->httpClient->get('/sales/nonexistent-sale-id/contract', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->authToken
                ]
            ]);
            
            $this->assertEquals(404, $response->getStatusCode());
            
        } catch (RequestException $e) {
            // Esperamos uma exceção 404
            $this->assertEquals(404, $e->getResponse()->getStatusCode());
        }
        
        test_log('✅ PDF para venda inexistente retorna 404');
    }

    public function testPDFPerformance(): void
    {
        test_log('Testando performance de geração de PDF...');
        
        $this->setupUserAndAuth();
        $this->createSampleSale();
        
        $startTime = microtime(true);
        
        try {
            $response = $this->httpClient->get("/sales/{$this->saleId}/contract", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->authToken
                ]
            ]);
            
            $endTime = microtime(true);
            $generationTime = $endTime - $startTime;
            
            $this->assertEquals(200, $response->getStatusCode());
            
            // PDF deve ser gerado em menos de 5 segundos
            $this->assertLessThan(5.0, $generationTime);
            
            test_log("PDF gerado em {$generationTime}s");
            test_log('✅ Performance de geração de PDF adequada');
            
        } catch (RequestException $e) {
            $this->fail('Falha no teste de performance: ' . $e->getMessage());
        }
    }

    public function testMultiplePDFDownloads(): void
    {
        test_log('Testando múltiplos downloads de PDF...');
        
        $this->setupUserAndAuth();
        $this->createSampleSale();
        
        try {
            // Download 1
            $response1 = $this->httpClient->get("/sales/{$this->saleId}/contract", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->authToken
                ]
            ]);
            
            // Download 2
            $response2 = $this->httpClient->get("/sales/{$this->saleId}/contract", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->authToken
                ]
            ]);
            
            $this->assertEquals(200, $response1->getStatusCode());
            $this->assertEquals(200, $response2->getStatusCode());
            
            // Conteúdo deve ser idêntico
            $content1 = $response1->getBody()->getContents();
            $content2 = $response2->getBody()->getContents();
            
            $this->assertEquals($content1, $content2);
            
            test_log('✅ Múltiplos downloads de PDF funcionando');
            
        } catch (RequestException $e) {
            $this->fail('Falha em múltiplos downloads: ' . $e->getMessage());
        }
    }
}

