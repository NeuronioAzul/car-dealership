<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected string $authServiceUrl = 'http://localhost:8081/api/v1/auth';

    protected function setUp(): void
    {
        parent::setUp();
        
        // Carregar variáveis de ambiente se necessário
        if (file_exists(__DIR__ . '/../.env')) {
            $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                    [$key, $value] = explode('=', $line, 2);
                    $_ENV[trim($key)] = trim($value);
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
            $this->fail("Erro na requisição: $error");
        }
        
        return [
            'code' => $httpCode,
            'body' => json_decode($response, true) ?: []
        ];
    }

    /**
     * Faz login e retorna o token
     */
    protected function loginAndGetToken(string $email = 'admin@example.com', string $password = 'admin123'): string
    {
        $response = $this->makeRequest(
            "{$this->authServiceUrl}/login",
            'POST',
            ['email' => $email, 'password' => $password]
        );

        $this->assertEquals(200, $response['code'], 'Login deve ser bem-sucedido');
        $this->assertArrayHasKey('data', $response['body']);
        $this->assertArrayHasKey('access_token', $response['body']['data']);

        return $response['body']['data']['access_token'];
    }

    /**
     * Verifica se o serviço de auth está disponível
     */
    protected function checkAuthServiceAvailability(): void
    {
        $response = $this->makeRequest("{$this->authServiceUrl}/health");
        
        if ($response['code'] !== 200) {
            $this->markTestSkipped('Auth service não está disponível em ' . $this->authServiceUrl);
        }
    }
}
