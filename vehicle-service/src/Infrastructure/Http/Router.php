<?php

namespace App\Infrastructure\Http;

use App\Presentation\Controllers\VehicleController;

class Router
{
    private array $routes = [];
    private VehicleController $vehicleController;

    public function __construct()
    {
        $this->vehicleController = new VehicleController();
        $this->setupRoutes();
    }

    private function setupRoutes(): void
    {
        $this->routes = [
            'GET /' => [$this->vehicleController, 'listVehicles'],
            'GET /search' => [$this->vehicleController, 'searchVehicles'],
            'GET /health' => [$this->vehicleController, 'health']
        ];
    }

    public function handleRequest(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remover prefixos desnecessários
        $path = preg_replace('/^\/vehicles/', '', $path);
        if ($path === '') {
            $path = '/';
        }

        // Verificar se é uma rota de detalhes do veículo (GET /vehicles/{id})
        if ($method === 'GET' && preg_match('/^\/[a-f0-9\-]{36}$/', $path)) {
            try {
                $this->vehicleController->getVehicleDetails();
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

