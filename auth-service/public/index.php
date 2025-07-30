<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Infrastructure\Database\DatabaseConfig;
use App\Infrastructure\DI\Container;
use App\Infrastructure\Http\Router;
use App\Infrastructure\Messaging\RabbitMQConnection;
use App\Presentation\Exceptions\InternalServerErrorException;
use Dotenv\Dotenv;

// Carregar variáveis de ambiente
if (class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

// Configurar headers CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json');

// Tratar requisições OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Inicializar o Container de Dependências
    $container = new Container();
    
    // Inicializar conexões
    $database = DatabaseConfig::getConnection();
    $rabbitmq = RabbitMQConnection::getInstance();

    // Inicializar roteador com o container
    $router = new Router($container);

    // Processar requisição
    $router->handleRequest();
} catch (Exception $e) {
    $exception = new InternalServerErrorException($e->getMessage());
    http_response_code($exception->getStatusCode());
    echo json_encode([
        'error' => 'Internal Server Error',
        'message' => $exception->getMessage(),
        'trace' => $e->getTraceAsString(),
    ]);
}
