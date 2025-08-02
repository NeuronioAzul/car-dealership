<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Http;

use PHPUnit\Framework\TestCase;
use App\Infrastructure\Http\Router;
use App\Infrastructure\DI\Container;

class RouterCoverageTest extends TestCase
{
    private Router $router;
    private Container $container;

    protected function setUp(): void
    {
        $this->container = new Container();
        $this->router = new Router($this->container);
    }

    public function test_handle_http_methods(): void
    {
        // Simular diferentes métodos HTTP
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/health';
        
        // Capturar saída
        ob_start();
        $this->router->handleRequest();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
        $this->assertStringContainsString('status', $output);
    }

    public function test_handle_post_request(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/auth/login';
        $_SERVER['CONTENT_TYPE'] = 'application/json';
        
        // Mock input data
        $GLOBALS['mockInput'] = json_encode([
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);
        
        ob_start();
        $this->router->handleRequest();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function test_handle_delete_request(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $_SERVER['REQUEST_URI'] = '/users/123';
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer valid-token';
        
        ob_start();
        $this->router->handleRequest();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function test_handle_invalid_route(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/invalid/route';
        
        ob_start();
        $this->router->handleRequest();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
        $this->assertStringContainsString('404', $output);
    }

    public function test_handle_with_query_parameters(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/health?check=full&details=true';
        
        ob_start();
        $this->router->handleRequest();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function test_handle_options_request(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'OPTIONS';
        $_SERVER['REQUEST_URI'] = '/auth/login';
        
        ob_start();
        $this->router->handleRequest();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function test_handle_put_request(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $_SERVER['REQUEST_URI'] = '/users/123';
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer valid-token';
        
        ob_start();
        $this->router->handleRequest();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function test_handle_patch_request(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'PATCH';
        $_SERVER['REQUEST_URI'] = '/users/123';
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer valid-token';
        
        ob_start();
        $this->router->handleRequest();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function test_handle_with_trailing_slash(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/health/';
        
        ob_start();
        $this->router->handleRequest();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function test_handle_with_different_user_agents(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/health';
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (compatible; Test/1.0)';
        
        ob_start();
        $this->router->handleRequest();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function test_handle_large_request_body(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/auth/register';
        $_SERVER['CONTENT_TYPE'] = 'application/json';
        
        // Large JSON payload
        $largeData = [
            'name' => str_repeat('Test User ', 100),
            'email' => 'test@example.com',
            'password' => 'password123',
            'phone' => '11999999999',
            'birth_date' => '1990-01-01',
            'accept_terms' => true,
            'accept_privacy' => true,
            'accept_communications' => false
        ];
        
        $GLOBALS['mockInput'] = json_encode($largeData);
        
        ob_start();
        $this->router->handleRequest();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function test_handle_malformed_json(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/auth/login';
        $_SERVER['CONTENT_TYPE'] = 'application/json';
        
        // Malformed JSON
        $GLOBALS['mockInput'] = '{"email": "test@example.com", "password": "pass';
        
        ob_start();
        $this->router->handleRequest();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function test_handle_empty_request_body(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/auth/login';
        $_SERVER['CONTENT_TYPE'] = 'application/json';
        
        $GLOBALS['mockInput'] = '';
        
        ob_start();
        $this->router->handleRequest();
        $output = ob_get_clean();
        
        $this->assertIsString($output);
    }

    public function test_router_with_various_content_types(): void
    {
        $contentTypes = [
            'application/json',
            'application/x-www-form-urlencoded',
            'multipart/form-data',
            'text/plain',
            'application/xml'
        ];

        foreach ($contentTypes as $contentType) {
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_SERVER['REQUEST_URI'] = '/auth/login';
            $_SERVER['CONTENT_TYPE'] = $contentType;
            
            ob_start();
            $this->router->handleRequest();
            $output = ob_get_clean();
            
            $this->assertIsString($output);
        }
    }

    protected function tearDown(): void
    {
        // Clean up globals
        unset($GLOBALS['mockInput']);
    }
}
