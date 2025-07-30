<?php

require_once __DIR__ . '/vendor/autoload.php';

// Carregar autoloaders de todos os serviços
$services = [
    'auth-service',
    'vehicle-service', 
    'customer-service',
    'payment-service',
    'reservation-service',
    'sales-service',
    'admin-service',
    'saga-orchestrator'
];

foreach ($services as $service) {
    $autoloadPath = __DIR__ . "/../{$service}/vendor/autoload.php";
    if (file_exists($autoloadPath)) {
        require_once $autoloadPath;
    }
}

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
    define('TEST_AUTH_SERVICE_URL', 'http://auth-service:80/api/v1/auth');
    define('TEST_VEHICLE_SERVICE_URL', 'http://vehicle-service:80/api/v1/vehicles');
    define('TEST_CUSTOMER_SERVICE_URL', 'http://customer-service:80/api/v1/customer');
    define('TEST_PAYMENT_SERVICE_URL', 'http://payment-service:80/api/v1/payments');
    define('TEST_RESERVATION_SERVICE_URL', 'http://reservation-service:80/api/v1/reservations');
    define('TEST_SALES_SERVICE_URL', 'http://sales-service:80/api/v1/sales');
    define('TEST_ADMIN_SERVICE_URL', 'http://admin-service:80/api/v1/admin');
    echo "🐳 Detectado ambiente Docker\n";
} else {
    // Fora do Docker - usar localhost
    define('TEST_DB_HOST', 'localhost');
    define('TEST_DB_PORT', '3306');
    define('TEST_DB_USERNAME', 'root');
    define('TEST_DB_PASSWORD', 'rootpassword123');
    define('TEST_BASE_URL', 'http://localhost:8000/api/v1');
    define('TEST_AUTH_SERVICE_URL', 'http://localhost:8081/api/v1/auth');
    define('TEST_VEHICLE_SERVICE_URL', 'http://localhost:8083/api/v1/vehicles');
    define('TEST_CUSTOMER_SERVICE_URL', 'http://localhost:8082/api/v1/customer');
    define('TEST_PAYMENT_SERVICE_URL', 'http://localhost:8085/api/v1/payments');
    define('TEST_RESERVATION_SERVICE_URL', 'http://localhost:8084/api/v1/reservations');
    define('TEST_SALES_SERVICE_URL', 'http://localhost:8086/api/v1/sales');
    define('TEST_ADMIN_SERVICE_URL', 'http://localhost:8087/api/v1/admin');
    echo "💻 Detectado ambiente local (host)\n";
}

// Carregar variáveis de ambiente de todos os serviços
foreach ($services as $service) {
    $envFiles = [
        __DIR__ . "/../{$service}/.env",
        __DIR__ . "/../{$service}/.env.example"
    ];
    
    foreach ($envFiles as $envFile) {
        if (file_exists($envFile)) {
            loadEnvFile($envFile);
            break;
        }
    }
}

function loadEnvFile(string $envFile): void {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if (!isset($_ENV[$key])) {
                $_ENV[$key] = $value;
                putenv("{$key}={$value}");
            }
        }
    }
}

// Configurar timezone
date_default_timezone_set('America/Sao_Paulo');

// Configurar error reporting para testes
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configurar valores padrão se não estiverem definidos
if (!isset($_ENV['JWT_SECRET'])) {
    $_ENV['JWT_SECRET'] = 'your-super-secret-jwt-key-for-auth-service-2025';
}

if (!isset($_ENV['JWT_EXPIRATION'])) {
    $_ENV['JWT_EXPIRATION'] = '3600';
}

if (!isset($_ENV['JWT_ALGORITHM'])) {
    $_ENV['JWT_ALGORITHM'] = 'HS256';
}

echo "✅ Bootstrap concluído - Ambiente de testes configurado\n";

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

