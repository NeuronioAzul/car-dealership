<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Shared\Database\Seeder\AuthSeeder;
use Shared\Database\Seeder\VehicleSeeder;
use Shared\Database\Seeder\CustomerSeeder;
use Shared\Database\Seeder\ReservationSeeder;
use Shared\Database\Seeder\PaymentSeeder;
use Shared\Database\Seeder\SalesSeeder;
use Shared\Database\Seeder\AdminSeeder;
use Shared\Database\Seeder\SagaSeeder;

echo "🌱 INICIANDO SEED COMPLETO DO SISTEMA DE CONCESSIONÁRIA\n";
echo "======================================================\n\n";

try {
    // Ordem de execução dos seeders (respeitando dependências)
    $seeders = [
        new AuthSeeder(),           // 1. Usuários (base para todos)
        new VehicleSeeder(),        // 2. Veículos (independente)
        new CustomerSeeder(),       // 3. Perfis de clientes (depende de auth)
        new ReservationSeeder(),    // 4. Reservas (depende de auth e vehicles)
        new PaymentSeeder(),        // 5. Pagamentos (depende de reservations)
        new SalesSeeder(),          // 6. Vendas (depende de payments)
        new AdminSeeder(),          // 7. Dados administrativos (independente)
        new SagaSeeder()            // 8. Transações SAGA (depende de sales)
    ];
    
    $startTime = microtime(true);
    
    foreach ($seeders as $seeder) {
        $seeder->run();
    }
    
    $endTime = microtime(true);
    $executionTime = round($endTime - $startTime, 2);
    
    echo "🎉 SEED COMPLETO FINALIZADO COM SUCESSO!\n";
    echo "⏱️  Tempo de execução: {$executionTime} segundos\n\n";
    
    // Ler configurações do .env para exibir resumo
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
    $dotenv->load();
    
    $sellersCount = $_ENV['SEED_SELLERS_COUNT'] ?? 5;
    $customersCount = $_ENV['SEED_CUSTOMERS_COUNT'] ?? 50;
    $vehiclesCount = $_ENV['SEED_VEHICLES_COUNT'] ?? 100;
    $reservationsCount = $_ENV['SEED_RESERVATIONS_COUNT'] ?? 15;
    $paymentsCount = $_ENV['SEED_PAYMENTS_COUNT'] ?? 25;
    $salesCount = $_ENV['SEED_SALES_COUNT'] ?? 15;
    $sagaCount = $_ENV['SEED_SAGA_TRANSACTIONS_COUNT'] ?? 10;
    
    echo "📊 RESUMO DOS DADOS CRIADOS:\n";
    echo "============================\n";
    echo "👥 Usuários: 1 admin + {$sellersCount} vendedores + {$customersCount} clientes\n";
    echo "🚗 Veículos: {$vehiclesCount} veículos com imagens\n";
    echo "📋 Perfis: {$customersCount} perfis de clientes com endereços e preferências\n";
    echo "📅 Reservas: {$reservationsCount} reservas com códigos de pagamento\n";
    echo "💳 Pagamentos: {$paymentsCount} transações com detalhes do gateway\n";
    echo "📄 Vendas: {$salesCount} vendas com documentos e itens adicionais\n";
    echo "⚙️  Admin: Configurações, logs, relatórios e notificações\n";
    echo "🔄 SAGA: {$sagaCount} transações distribuídas com passos e eventos\n\n";
    
    echo "🔑 CREDENCIAIS DE ACESSO:\n";
    echo "=========================\n";
    echo "👨‍💼 Admin: " . ($_ENV['ADMIN_EMAIL'] ?? 'admin@concessionaria.com') . " / " . ($_ENV['ADMIN_PASSWORD'] ?? 'admin123') . "\n";
    echo "👨‍💻 Vendedor: vendedor1@concessionaria.com / " . ($_ENV['SELLER_PASSWORD'] ?? 'vendedor123') . "\n";
    echo "👤 Cliente: Use qualquer email gerado / " . ($_ENV['CUSTOMER_PASSWORD'] ?? 'cliente123') . "\n\n";
    
    echo "🌐 ENDPOINTS DISPONÍVEIS:\n";
    echo "=========================\n";
    echo "🚪 API Gateway: http://localhost:8000\n";
    echo "📚 Documentação: http://localhost:8089\n";
    echo "🗄️  phpMyAdmin: http://localhost:8090\n";
    echo "🐰 RabbitMQ: http://localhost:15672\n\n";
    
    echo "⚙️  CONFIGURAÇÕES APLICADAS:\n";
    echo "============================\n";
    echo "🏢 Empresa: " . ($_ENV['COMPANY_NAME'] ?? 'Concessionária AutoMax') . "\n";
    echo "⏰ Expiração de reserva: " . ($_ENV['RESERVATION_EXPIRY_HOURS'] ?? '24') . " horas\n";
    echo "📊 Taxa do gateway: " . ($_ENV['GATEWAY_FEE_PERCENTAGE'] ?? '3.5') . "%\n";
    echo "🌍 Timezone: " . ($_ENV['TIMEZONE'] ?? 'America/Sao_Paulo') . "\n\n";
    
    echo "✅ Sistema pronto para uso com UUID v6 e configurações do .env!\n";
    
} catch (Exception $e) {
    echo "❌ ERRO DURANTE O SEED: " . $e->getMessage() . "\n";
    echo "📍 Arquivo: " . $e->getFile() . " (linha " . $e->getLine() . ")\n";
    echo "🔍 Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

