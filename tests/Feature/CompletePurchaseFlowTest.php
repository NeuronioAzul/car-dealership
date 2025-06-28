<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class CompletePurchaseFlowTest extends TestCase
{
    private Client $httpClient;
    private string $baseUrl;
    private array $testUser;
    private string $authToken;

    protected function setUp(): void
    {
        $this->baseUrl = TEST_BASE_URL;
        $this->httpClient = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 60, // Timeout maior para fluxo completo
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]
        ]);

        $this->testUser = [
            'name' => 'Cliente Teste Compra',
            'email' => 'compra_' . time() . '@email.com',
            'password' => 'senha123',
            'cpf' => '98765432100',
            'phone' => '11888888888',
            'address' => [
                'street' => 'Rua da Compra, 456',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01234-567'
            ]
        ];
    }

    public function testCompleteVehiclePurchaseFlow(): void
    {
        test_log('Iniciando teste de fluxo completo de compra de veículo');
        
        // Passo 1: Registrar usuário
        $this->registerUser();
        
        // Passo 2: Fazer login
        $this->loginUser();
        
        // Passo 3: Buscar veículo disponível
        $vehicleId = $this->findAvailableVehicle();
        
        // Passo 4: Criar reserva
        $reservationId = $this->createReservation($vehicleId);
        
        // Passo 5: Gerar código de pagamento
        $paymentCode = $this->generatePaymentCode($reservationId);
        
        // Passo 6: Processar pagamento
        $paymentId = $this->processPayment($paymentCode);
        
        // Passo 7: Verificar criação da venda
        $saleId = $this->verifySaleCreation($vehicleId);
        
        // Passo 8: Verificar documentos gerados
        $this->verifyDocuments($saleId);
        
        // Passo 9: Verificar status do veículo
        $this->verifyVehicleStatus($vehicleId, 'sold');
        
        test_log('Fluxo completo de compra concluído com sucesso', 'SUCCESS');
    }

    private function registerUser(): void
    {
        test_log('Registrando usuário de teste...');
        
        try {
            $response = $this->httpClient->post('/auth/register', [
                'json' => $this->testUser
            ]);
            
            $this->assertEquals(201, $response->getStatusCode());
            
            $body = json_decode($response->getBody()->getContents(), true);
            $this->assertTrue($body['success']);
            
            test_log('Usuário registrado com sucesso');
            
        } catch (RequestException $e) {
            $this->fail('Falha no registro do usuário: ' . $e->getMessage());
        }
    }

    private function loginUser(): void
    {
        test_log('Fazendo login...');
        
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
            
            $this->authToken = $body['data']['access_token'];
            
            test_log('Login realizado com sucesso');
            
        } catch (RequestException $e) {
            $this->fail('Falha no login: ' . $e->getMessage());
        }
    }

    private function findAvailableVehicle(): string
    {
        test_log('Buscando veículo disponível...');
        
        try {
            $response = $this->httpClient->get('/vehicles?available_only=true&limit=1');
            
            $this->assertEquals(200, $response->getStatusCode());
            
            $body = json_decode($response->getBody()->getContents(), true);
            $this->assertTrue($body['success']);
            
            $vehicles = $body['data']['vehicles'];
            $this->assertNotEmpty($vehicles, 'Nenhum veículo disponível para teste');
            
            $vehicleId = $vehicles[0]['id'];
            test_log("Veículo encontrado: {$vehicles[0]['brand']} {$vehicles[0]['model']} (ID: $vehicleId)");
            
            return $vehicleId;
            
        } catch (RequestException $e) {
            $this->fail('Falha ao buscar veículo: ' . $e->getMessage());
        }
    }

    private function createReservation(string $vehicleId): string
    {
        test_log('Criando reserva...');
        
        try {
            $response = $this->httpClient->post('/reservations', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->authToken
                ],
                'json' => [
                    'vehicle_id' => $vehicleId
                ]
            ]);
            
            $this->assertEquals(201, $response->getStatusCode());
            
            $body = json_decode($response->getBody()->getContents(), true);
            $this->assertTrue($body['success']);
            
            $reservationId = $body['data']['reservation']['id'];
            test_log("Reserva criada com sucesso (ID: $reservationId)");
            
            return $reservationId;
            
        } catch (RequestException $e) {
            $this->fail('Falha ao criar reserva: ' . $e->getMessage());
        }
    }

    private function generatePaymentCode(string $reservationId): string
    {
        test_log('Gerando código de pagamento...');
        
        try {
            $response = $this->httpClient->post('/reservations/generate-payment-code', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->authToken
                ],
                'json' => [
                    'reservation_id' => $reservationId
                ]
            ]);
            
            $this->assertEquals(200, $response->getStatusCode());
            
            $body = json_decode($response->getBody()->getContents(), true);
            $this->assertTrue($body['success']);
            
            $paymentCode = $body['data']['payment_code'];
            test_log("Código de pagamento gerado: $paymentCode");
            
            return $paymentCode;
            
        } catch (RequestException $e) {
            $this->fail('Falha ao gerar código de pagamento: ' . $e->getMessage());
        }
    }

    private function processPayment(string $paymentCode): string
    {
        test_log('Processando pagamento...');
        
        try {
            $response = $this->httpClient->post('/payments', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->authToken
                ],
                'json' => [
                    'payment_code' => $paymentCode,
                    'payment_method' => 'credit_card',
                    'card_data' => [
                        'number' => '4111111111111111',
                        'holder_name' => 'TESTE COMPRA',
                        'expiry_month' => '12',
                        'expiry_year' => '2025',
                        'cvv' => '123'
                    ]
                ]
            ]);
            
            $this->assertEquals(200, $response->getStatusCode());
            
            $body = json_decode($response->getBody()->getContents(), true);
            $this->assertTrue($body['success']);
            
            $paymentId = $body['data']['payment']['id'];
            $status = $body['data']['payment']['status'];
            
            $this->assertEquals('completed', $status);
            test_log("Pagamento processado com sucesso (ID: $paymentId)");
            
            return $paymentId;
            
        } catch (RequestException $e) {
            $this->fail('Falha no processamento do pagamento: ' . $e->getMessage());
        }
    }

    private function verifySaleCreation(string $vehicleId): string
    {
        test_log('Verificando criação da venda...');
        
        // Aguardar um pouco para o processamento assíncrono
        sleep(3);
        
        try {
            $response = $this->httpClient->get('/sales', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->authToken
                ]
            ]);
            
            $this->assertEquals(200, $response->getStatusCode());
            
            $body = json_decode($response->getBody()->getContents(), true);
            $this->assertTrue($body['success']);
            
            $sales = $body['data']['sales'];
            $this->assertNotEmpty($sales, 'Nenhuma venda encontrada');
            
            // Encontrar a venda do veículo específico
            $sale = null;
            foreach ($sales as $s) {
                if ($s['vehicle_id'] === $vehicleId) {
                    $sale = $s;
                    break;
                }
            }
            
            $this->assertNotNull($sale, 'Venda do veículo não encontrada');
            $this->assertEquals('completed', $sale['status']);
            
            test_log("Venda criada com sucesso (ID: {$sale['id']})");
            
            return $sale['id'];
            
        } catch (RequestException $e) {
            $this->fail('Falha ao verificar criação da venda: ' . $e->getMessage());
        }
    }

    private function verifyDocuments(string $saleId): void
    {
        test_log('Verificando documentos gerados...');
        
        try {
            // Verificar contrato
            $contractResponse = $this->httpClient->get("/sales/$saleId/contract", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->authToken
                ]
            ]);
            
            $this->assertEquals(200, $contractResponse->getStatusCode());
            $this->assertEquals('application/pdf', $contractResponse->getHeader('Content-Type')[0]);
            
            // Verificar nota fiscal
            $invoiceResponse = $this->httpClient->get("/sales/$saleId/invoice", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->authToken
                ]
            ]);
            
            $this->assertEquals(200, $invoiceResponse->getStatusCode());
            $this->assertEquals('application/pdf', $invoiceResponse->getHeader('Content-Type')[0]);
            
            test_log('Documentos verificados com sucesso');
            
        } catch (RequestException $e) {
            $this->fail('Falha ao verificar documentos: ' . $e->getMessage());
        }
    }

    private function verifyVehicleStatus(string $vehicleId, string $expectedStatus): void
    {
        test_log("Verificando status do veículo (esperado: $expectedStatus)...");
        
        try {
            $response = $this->httpClient->get("/vehicles/$vehicleId");
            
            $this->assertEquals(200, $response->getStatusCode());
            
            $body = json_decode($response->getBody()->getContents(), true);
            $this->assertTrue($body['success']);
            
            $vehicle = $body['data']['vehicle'];
            $this->assertEquals($expectedStatus, $vehicle['status']);
            
            test_log("Status do veículo verificado: {$vehicle['status']}");
            
        } catch (RequestException $e) {
            $this->fail('Falha ao verificar status do veículo: ' . $e->getMessage());
        }
    }

    public function testSagaOrchestrationFlow(): void
    {
        test_log('Testando fluxo de orquestração SAGA');
        
        // Registrar e fazer login
        $this->registerUser();
        $this->loginUser();
        
        // Buscar veículo
        $vehicleId = $this->findAvailableVehicle();
        
        try {
            // Iniciar transação SAGA
            $response = $this->httpClient->post('/saga/purchase', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->authToken
                ],
                'json' => [
                    'vehicle_id' => $vehicleId,
                    'customer_data' => [
                        'name' => $this->testUser['name'],
                        'cpf' => $this->testUser['cpf'],
                        'email' => $this->testUser['email'],
                        'phone' => $this->testUser['phone'],
                        'address' => implode(', ', $this->testUser['address'])
                    ]
                ]
            ]);
            
            $this->assertEquals(201, $response->getStatusCode());
            
            $body = json_decode($response->getBody()->getContents(), true);
            $this->assertTrue($body['success']);
            
            $transactionId = $body['data']['transaction_id'];
            test_log("Transação SAGA iniciada (ID: $transactionId)");
            
            // Monitorar execução da SAGA
            $this->monitorSagaExecution($transactionId);
            
        } catch (RequestException $e) {
            $this->fail('Falha no fluxo SAGA: ' . $e->getMessage());
        }
    }

    private function monitorSagaExecution(string $transactionId): void
    {
        test_log('Monitorando execução da SAGA...');
        
        $maxAttempts = 30;
        $attempt = 0;
        $stepsCompleted = [];
        
        while ($attempt < $maxAttempts) {
            sleep(2);
            $attempt++;
            
            try {
                $response = $this->httpClient->get("/saga/transactions/$transactionId", [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->authToken
                    ]
                ]);
                
                $this->assertEquals(200, $response->getStatusCode());
                
                $body = json_decode($response->getBody()->getContents(), true);
                $transaction = $body['data'];
                
                $status = $transaction['status'];
                $currentStep = $transaction['current_step'] ?? null;
                $completedSteps = $transaction['completed_steps'] ?? [];
                
                // Log de novos passos completados
                foreach ($completedSteps as $step) {
                    if (!in_array($step, $stepsCompleted)) {
                        test_log("Passo completado: $step");
                        $stepsCompleted[] = $step;
                    }
                }
                
                if ($currentStep && !in_array($currentStep, $stepsCompleted)) {
                    test_log("Executando: $currentStep");
                }
                
                test_log("Status SAGA: $status (tentativa $attempt/$maxAttempts)");
                
                if ($status === 'completed') {
                    test_log('SAGA concluída com sucesso');
                    $this->assertEquals('completed', $status);
                    return;
                } elseif ($status === 'failed') {
                    $failureReason = $transaction['failure_reason'] ?? 'Motivo não especificado';
                    $this->fail("SAGA falhou: $failureReason");
                } elseif ($status === 'compensated') {
                    test_log('SAGA foi compensada (rollback executado)');
                    $this->assertEquals('compensated', $status);
                    return;
                }
                
            } catch (RequestException $e) {
                test_log("Erro ao monitorar SAGA: " . $e->getMessage(), 'WARNING');
            }
        }
        
        $this->fail('Timeout na execução da SAGA');
    }
}

