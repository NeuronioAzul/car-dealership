<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected string $authServiceUrl = 'http://localhost:8081/api/v1/auth';
    protected string $vehicleServiceUrl = 'http://localhost:8083/api/v1/vehicles';
    protected string $customerServiceUrl = 'http://localhost:8082/api/v1/customer';
    protected string $paymentServiceUrl = 'http://localhost:8085/api/v1/payments';
    protected string $reservationServiceUrl = 'http://localhost:8084/api/v1/reservations';
    protected string $salesServiceUrl = 'http://localhost:8086/api/v1/sales';
    protected string $adminServiceUrl = 'http://localhost:8087/api/v1/admin';

    protected function setUp(): void
    {
        parent::setUp();
        
        // Carregar variáveis de ambiente de todos os serviços
        $this->loadEnvironmentVariables();
    }

    private function loadEnvironmentVariables(): void
    {
        $services = [
            'auth-service',
            'vehicle-service',
            'customer-service',
            'payment-service',
            'reservation-service',
            'sales-service',
            'admin-service',
            'saga-orchestrator'
        ];

        foreach ($services as $service) {
            $envFile = __DIR__ . "/../{$service}/.env";
            if (file_exists($envFile)) {
                $this->loadEnvFile($envFile);
            }
        }
    }

    private function loadEnvFile(string $envFile): void
    {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                if (!isset($_ENV[$key])) {
                    $_ENV[$key] = $value;
                }
            }
        }
    }

    /**
     * Faz uma requisição HTTP
     */
    protected function makeRequest(string $url, string $method = 'GET', ?array $data = null, array $headers = []): array
    {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            $headers[] = 'Content-Type: application/json';
        }
        
        if ($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            throw new \Exception("cURL Error: $error");
        }
        
        return [
            'code' => $httpCode,
            'body' => json_decode($response, true) ?: $response,
            'raw' => $response
        ];
    }

    /**
     * Faz login e retorna o token de acesso
     */
    protected function loginAndGetToken(string $email = 'admin@concessionaria.com', string $password = 'admin123'): array
    {
        $loginData = [
            'email' => $email,
            'password' => $password
        ];

        $response = $this->makeRequest("{$this->authServiceUrl}/login", 'POST', $loginData);
        
        if ($response['code'] !== 200 || !isset($response['body']['data']['access_token'])) {
            throw new \Exception('Falha no login durante o teste');
        }

        return [
            'access_token' => $response['body']['data']['access_token'],
            'refresh_token' => $response['body']['data']['refresh_token'],
            'user' => $response['body']['data']['user']
        ];
    }

    /**
     * Gera headers de autorização com token
     */
    protected function getAuthHeaders(string $token): array
    {
        return [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ];
    }

    /**
     * Verifica se um serviço está rodando
     */
    protected function isServiceRunning(string $url): bool
    {
        try {
            $response = $this->makeRequest($url . '/health');
            return $response['code'] === 200;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Aguarda um serviço ficar disponível
     */
    protected function waitForService(string $url, int $maxAttempts = 30): bool
    {
        for ($i = 0; $i < $maxAttempts; $i++) {
            if ($this->isServiceRunning($url)) {
                return true;
            }
            sleep(1);
        }
        return false;
    }
}
