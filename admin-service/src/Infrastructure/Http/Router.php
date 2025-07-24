<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Presentation\Controllers\AdminController;

class Router
{
    private array $routes = [];
    private AdminController $adminController;

    public function __construct()
    {
        $this->adminController = new AdminController();
        $this->setupRoutes();
    }

    private function setupRoutes(): void
    {
        $this->routes = [
            'GET /dashboard' => [$this->adminController, 'getDashboard'],
            'GET /reports/sales' => [$this->adminController, 'getSalesReport'],
            'GET /reports/customers' => [$this->adminController, 'getCustomerReport'],
            'GET /reports/vehicles' => [$this->adminController, 'getVehicleReport'],
            'GET /health' => [$this->adminController, 'health'],
        ];
    }

    public function handleRequest(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Remover prefixos desnecessários
        $path = preg_replace('#^\/api\/v1\/admin#', '', $path);

        if ($path === '') {
            $path = '/dashboard';
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
            'code' => $code,
        ]);
    }

    private function notFound(): void
    {
        http_response_code(404);
        echo json_encode([
            'error' => true,
            'message' => 'Rota não encontrada',
            'code' => 404,
        ]);
    }
}
