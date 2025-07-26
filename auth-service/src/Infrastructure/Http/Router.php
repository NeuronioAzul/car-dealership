<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Presentation\Controllers\AuthController;

class Router
{
    private array $routes = [];
    private AuthController $authController;

    public function __construct()
    {
        $this->authController = new AuthController();
        $this->setupRoutes();
    }

    private function setupRoutes(): void
    {
        $this->routes = [
            'POST /login' => [$this->authController, 'login'],
            'POST /register' => [$this->authController, 'register'],
            'POST /refresh' => [$this->authController, 'refresh'],
            'POST /logout' => [$this->authController, 'logout'],
            'POST /validate' => [$this->authController, 'validate'],
            'GET /health' => [$this->authController, 'health'],
        ];
    }

    public function handleRequest(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Remover prefixos desnecessários
        $path = preg_replace('#^\/api\/v1\/auth#', '', $path);

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
