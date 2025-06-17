<?php

namespace App\Infrastructure\Http;

use App\Presentation\Controllers\SaleController;

class Router
{
    private array $routes = [];
    private SaleController $saleController;

    public function __construct()
    {
        $this->saleController = new SaleController();
        $this->setupRoutes();
    }

    private function setupRoutes(): void
    {
        $this->routes = [
            'POST /' => [$this->saleController, 'createSale'],
            'GET /' => [$this->saleController, 'listSales'],
            'GET /health' => [$this->saleController, 'health']
        ];
    }

    public function handleRequest(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remover prefixos desnecessários
        $path = preg_replace('/^\/sales/', '', $path);
        if ($path === '') {
            $path = '/';
        }

        // Verificar se é uma rota de detalhes da venda (GET /sales/{id})
        if ($method === 'GET' && preg_match('/^\/[a-f0-9\-]{36}$/', $path)) {
            try {
                $this->saleController->getSaleDetails();
            } catch (\Exception $e) {
                $this->handleError($e);
            }
            return;
        }

        // Verificar se é uma rota de download de documento (GET /sales/{id}/{document_type})
        if ($method === 'GET' && preg_match('/^\/[a-f0-9\-]{36}\/(contract|invoice)$/', $path)) {
            try {
                $this->saleController->downloadDocument();
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

