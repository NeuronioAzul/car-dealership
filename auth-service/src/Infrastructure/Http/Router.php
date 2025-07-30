<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Infrastructure\DI\Container;
use App\Presentation\Controllers\AuthController;
use App\Presentation\Controllers\UserController;
use App\Presentation\Middleware\AuthMiddleware;

class Router
{
    private array $routes = [];
    private array $middlewares = [];
    private AuthController $authController;
    private UserController $userController;
    private AuthMiddleware $authMiddleware;
    private Container $container;

    public function __construct(?Container $container = null)
    {
        $this->container = $container;
        $this->authController = new AuthController($container);
        $this->userController = new UserController();
        $this->authMiddleware = new AuthMiddleware();
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
            'DELETE /users/{id}' => [$this->userController, 'delete'],
        ];
        // Definir middlewares para rotas específicas
        $this->middlewares = [
            'DELETE /users/{id}' => ['admin'],
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
        $params = [];

        // Primeiro tenta encontrar rota exata
        if (isset($this->routes[$route])) {
            // Verificar middleware para rota exata
            $this->checkMiddleware($route);

            [$controller, $action] = $this->routes[$route];

            try {
                $controller->$action();
            } catch (\Exception $e) {
                $this->handleError($e);
            }

            return;
        }

        // Se não encontrou rota exata, procura por rotas com parâmetros
        foreach ($this->routes as $routePattern => $handler) {
            $routeParams = $this->matchRoute($routePattern, $route);

            if ($routeParams !== false) {
                // Verificar middleware para rota com padrão
                $this->checkMiddleware($routePattern);

                [$controller, $action] = $handler;
                $params = $routeParams;

                try {
                    $controller->$action(...array_values($params));
                } catch (\Exception $e) {
                    $this->handleError($e);
                }

                return;
            }
        }

        $this->notFound();
    }

    private function checkMiddleware(string $route): void
    {
        if (!isset($this->middlewares[$route])) {
            return;
        }

        $requiredRoles = $this->middlewares[$route];

        try {
            foreach ($requiredRoles as $role) {
                switch ($role) {
                    case 'admin':
                        $this->authMiddleware->requireAdmin();
                        break;
                    case 'customer':
                        $this->authMiddleware->requireCustomer();
                        break;
                    case 'auth':
                        $this->authMiddleware->authenticate();
                        break;
                }
            }
        } catch (\Exception $e) {
            // Tratar erros de autenticação/autorização
            $this->handleAuthError($e);
        }
    }

    private function matchRoute(string $routePattern, string $actualRoute): array|false
    {
        // Converter padrão de rota em regex
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $routePattern);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $actualRoute, $matches)) {
            array_shift($matches); // Remove o match completo

            // Extrair nomes dos parâmetros do padrão original
            preg_match_all('/\{([^}]+)\}/', $routePattern, $paramNames);
            $paramNames = $paramNames[1];

            $params = [];
            foreach ($paramNames as $index => $paramName) {
                $params[$paramName] = $matches[$index] ?? null;
            }

            return $params;
        }

        return false;
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

    private function handleAuthError(\Exception $e): void
    {
        $code = $e->getCode() ?: 401;
        http_response_code($code);

        $response = [
            'error' => true,
            'message' => $e->getMessage(),
            'code' => $code,
        ];

        // Adicionar tipo de erro específico para o frontend
        if ($code === 401) {
            $response['type'] = 'authentication_error';
            $response['action'] = 'redirect_to_login';
        } elseif ($code === 403) {
            $response['type'] = 'authorization_error';
            $response['action'] = 'insufficient_permissions';
        }

        echo json_encode($response);
        exit; // Parar execução após erro de autenticação
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
