<?php

namespace App\Infrastructure\Http;

use App\Presentation\Controllers\PaymentController;

class Router
{
    private array $routes = [];
    private PaymentController $paymentController;

    public function __construct()
    {
        $this->paymentController = new PaymentController();
        $this->setupRoutes();
    }

    private function setupRoutes(): void
    {
        $this->routes = [
            'POST /' => [$this->paymentController, 'processPayment'],
            'POST /create' => [$this->paymentController, 'createPayment'],
            'GET /my-payments' => [$this->paymentController, 'listCustomerPayments'],
            'GET /health' => [$this->paymentController, 'health']
        ];
    }

    public function handleRequest(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remover prefixos desnecessários
        $path = preg_replace('/^\/payments/', '', $path);
        if ($path === '') {
            $path = '/';
        }

        // Verificar se é uma rota de status do pagamento (GET /payments/{code})
        if ($method === 'GET' && preg_match('/^\/[A-Z0-9]+$/', $path)) {
            try {
                $this->paymentController->getPaymentStatus();
            } catch (\Exception $e) {
                $this->handleError($e);
            }
            return;
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

