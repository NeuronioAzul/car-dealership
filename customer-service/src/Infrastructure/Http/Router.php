<?php

namespace App\Infrastructure\Http;

use App\Presentation\Controllers\CustomerController;

class Router
{
    private array $routes = [];
    private CustomerController $customerController;

    public function __construct()
    {
        $this->customerController = new CustomerController();
        $this->setupRoutes();
    }

    private function setupRoutes(): void
    {
        $this->routes = [
            'POST /profile' => [$this->customerController, 'createCustomer'],
            'GET /profile' => [$this->customerController, 'getProfile'],
            'PUT /profile' => [$this->customerController, 'updateProfile'],
            'DELETE /profile' => [$this->customerController, 'deleteProfile'],
            'GET /health' => [$this->customerController, 'health']
        ];
    }

    public function handleRequest(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remover prefixos desnecessários
        $path = preg_replace('#^\/api\/v1\/customer#', '', $path);
        if ($path === '') {
            $path = '/';
        }

        $route = "{$method} {$path}";

        if (isset($this->routes[$route])) {
            [$controller, $action] = $this->routes[$route];
            
            try {
                $controller->$action();
            } catch (\Exception $e) {
                $this->handleError($e);
            }
        } else {
            $this->notFound();
        }
    }

    private function handleError(\Exception $e): void
    {
        $code = $e->getCode() ?: 500;
        http_response_code($code);
        
        echo json_encode([
            'error' => true,
            'message' => $e->getMessage(),
            'code' => $code
        ]);
    }

    private function notFound(): void
    {
        http_response_code(404);
        echo json_encode([
            'error' => true,
            'message' => 'Rota não encontrada',
            'code' => 404
        ]);
    }
}

