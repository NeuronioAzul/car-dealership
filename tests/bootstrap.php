<?php

require_once __DIR__ . '/vendor/autoload.php';

// Detectar se está rodando dentro do Docker
function isRunningInDocker(): bool {
    return file_exists('/.dockerenv') || 
           (isset($_ENV['DOCKER_CONTAINER']) && $_ENV['DOCKER_CONTAINER'] === 'true') ||
           gethostname() === 'auth-service' ||
           strpos(gethostname(), 'car_dealership_') !== false;
}

// Configurar variáveis baseado no ambiente
if (isRunningInDocker()) {
    // Dentro do Docker - usar hostnames dos containers
    define('TEST_DB_HOST', 'mysql');
    define('TEST_DB_PORT', '3306');
    define('TEST_DB_USERNAME', 'root');
    define('TEST_DB_PASSWORD', 'rootpassword123');
    define('TEST_BASE_URL', 'http://kong:8000/api/v1');
    echo "🐳 Detectado ambiente Docker\n";
} else {
    // Fora do Docker - usar localhost
    define('TEST_DB_HOST', 'localhost');
    define('TEST_DB_PORT', '3306');
    define('TEST_DB_USERNAME', 'root');
    define('TEST_DB_PASSWORD', 'rootpassword123');
    define('TEST_BASE_URL', 'http://localhost:8000/api/v1');
    echo "💻 Detectado ambiente local (host)\n";
}

// Carregar variáveis de ambiente se existir arquivo .env
if (file_exists(__DIR__ . '/.env')) {
    try {
        if (class_exists('Dotenv\Dotenv')) {
            $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
            $dotenv->safeLoad();
        }
    } catch (Exception $e) {
        // Ignorar erros do dotenv
    }
}

// Configurar timezone
date_default_timezone_set('America/Sao_Paulo');

// Configurar error reporting para testes
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Função auxiliar para logs de teste
function test_log(string $message, string $level = 'INFO'): void
{
    $timestamp = date('Y-m-d H:i:s');
    echo "[{$timestamp}] {$level}: {$message}" . PHP_EOL;
}

// Função para testar conectividade
function testDatabaseConnection(): bool
{
    try {
        $pdo = new PDO(
            "mysql:host=" . TEST_DB_HOST . ";port=" . TEST_DB_PORT,
            TEST_DB_USERNAME,
            TEST_DB_PASSWORD,
            [PDO::ATTR_TIMEOUT => 5]
        );
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

echo "📊 Configurações de teste:\n";
echo "  - Host: " . TEST_DB_HOST . ":" . TEST_DB_PORT . "\n";
echo "  - Usuário: " . TEST_DB_USERNAME . "\n";
echo "  - API Base: " . TEST_BASE_URL . "\n";

// Testar conectividade
if (testDatabaseConnection()) {
    echo "✅ Conexão com banco de dados OK\n\n";
} else {
    echo "❌ Falha na conexão com banco de dados\n";
    echo "💡 Dicas:\n";
    echo "  - Verifique se o Docker está rodando: docker-compose ps\n";
    echo "  - Ou execute dentro do Docker: docker-compose exec auth-service php tests/Scripts/DatabaseSeeder.php\n\n";
}

