<?php

namespace App\Infrastructure\Http;

use App\Presentation\Controllers\ReservationController;

class Router
{
    private array $routes = [];
    private ReservationController $reservationController;

    public function __construct()
    {
        $this->reservationController = new ReservationController();
        $this->setupRoutes();
    }

    private function setupRoutes(): void
    {
        $this->routes = [
            'POST /' => [$this->reservationController, 'createReservation'],
            'GET /' => [$this->reservationController, 'listReservations'],
            'POST /generate-payment-code' => [$this->reservationController, 'generatePaymentCode'],
            'GET /health' => [$this->reservationController, 'health']
        ];
    }

    public function handleRequest(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remover prefixos desnecessários
        $path = preg_replace('/^\/reservations/', '', $path);
        if ($path === '') {
            $path = '/';
        }

        // Verificar se é uma rota de detalhes ou cancelamento da reserva
        if ($method === 'GET' && preg_match('/^\/[a-f0-9\-]{36}$/', $path)) {
            try {
                $this->reservationController->getReservationDetails();
            } catch (\Exception $e) {
                $this->handleError($e);
            }
            return;
        }

        if ($method === 'DELETE' && preg_match('/^\/[a-f0-9\-]{36}$/', $path)) {
            try {
                $this->reservationController->cancelReservation();
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

