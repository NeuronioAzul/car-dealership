<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Infrastructure\Http\Router;
use App\Infrastructure\Database\DatabaseConfig;
use App\Infrastructure\Messaging\RabbitMQConnection;

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
    // Inicializar conexões
    $database = DatabaseConfig::getConnection();
    $rabbitmq = RabbitMQConnection::getInstance();
    
    // Inicializar roteador
    $router = new Router();
    
    // Processar requisição
    $router->handleRequest();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Internal Server Error',
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}

