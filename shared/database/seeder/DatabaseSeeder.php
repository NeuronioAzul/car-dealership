<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

echo "🌱 INICIANDO SEED DO SISTEMA DE CONCESSIONÁRIA\n";
echo "=============================================\n\n";

use Shared\Database\Seeder\AdminSeeder;
use Shared\Database\Seeder\AuthSeeder;
use Shared\Database\Seeder\CustomerSeeder;
use Shared\Database\Seeder\PaymentSeeder;
use Shared\Database\Seeder\ReservationSeeder;
use Shared\Database\Seeder\SagaSeeder;
use Shared\Database\Seeder\SalesSeeder;
use Shared\Database\Seeder\VehicleSeeder;

try {
    $startTime = microtime(true);

    switch ($selectedOption) {
        case '1':
            new AuthSeeder()->run();
            break;
        case '2':
            new VehicleSeeder()->run();
            break;
        case '3':
            new CustomerSeeder()->run();
            break;
        case '4':
            new ReservationSeeder()->run();
            break;
        case '5':
            new PaymentSeeder()->run();
            break;
        case '6':
            new SalesSeeder()->run();
            break;
        case '7':
            new AdminSeeder()->run();
            break;
        case '8':
            new SagaSeeder()->run();
            break;
        case '9':
            // Ordem de execução dos seeders (respeitando dependências)
            $seeders = [
                new AuthSeeder(),           // 1. Usuários (base para todos)
                new VehicleSeeder(),        // 2. Veículos (independente)
                new CustomerSeeder(),       // 3. Perfis de clientes (depende de auth)
                new ReservationSeeder(),    // 4. Reservas (depende de auth e vehicles)
                new PaymentSeeder(),        // 5. Pagamentos (depende de reservations)
                new SalesSeeder(),          // 6. Vendas (depende de payments)
                new AdminSeeder(),          // 7. Dados administrativos (independente)
                new SagaSeeder(),            // 8. Transações SAGA (depende de sales)
            ];
            foreach ($seeders as $seeder) {
                $seeder->run();
            }
            break;
        case '0':
            echo "❌ Saindo do seeder.\n";
            exit(0);
        default:
            echo "❌ Opção inválida. Por favor, escolha uma opção válida.\n";
            exit(1);
    }


    $endTime = microtime(true);
    $executionTime = round($endTime - $startTime, 2);

    echo "🎉 SEED FINALIZADO COM SUCESSO!\n";
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
    // Exibe resumo apenas dos dados realmente gerados conforme a opção selecionada
    $summaryOptions = [
        '1' => ["🙍🏻 Usuários:   1 admin + {$customersCount} clientes\n"],
        '2' => ["🚗 Veículos:   {$vehiclesCount} veículos com imagens\n"],
        '3' => ["📋 Perfis:     {$customersCount} perfis de clientes com endereços e preferências\n"],
        '4' => ["📅 Reservas:   {$reservationsCount} reservas com códigos de pagamento\n"],
        '5' => ["💳 Pagamentos: {$paymentsCount} transações com detalhes do gateway\n"],
        '6' => ["📄 Vendas:     {$salesCount} vendas com documentos e itens adicionais\n"],
        '7' => ["⚙️ Admin:      Configurações, logs, relatórios e notificações\n"],
        '8' => ["🔄 SAGA:       {$sagaCount} transações distribuídas com passos e eventos\n"],
        '9' => [
                "🙍🏻 Usuários:   1 admin + {$customersCount} clientes\n",
                "🚗 Veículos:   {$vehiclesCount} veículos com imagens\n",
                "📋 Perfis:     {$customersCount} perfis de clientes com endereços e preferências\n",
                "📅 Reservas:   {$reservationsCount} reservas com códigos de pagamento\n",
                "💳 Pagamentos: {$paymentsCount} transações com detalhes do gateway\n",
                "📄 Vendas:     {$salesCount} vendas com documentos e itens adicionais\n",
                "⚙️ Admin:      Configurações, logs, relatórios e notificações\n",
                "🔄 SAGA:       {$sagaCount} transações distribuídas com passos e eventos\n",
        ],
    ];

    if (isset($summaryOptions[$selectedOption])) {
        foreach ($summaryOptions[$selectedOption] as $line) {
            echo $line;
        }
        echo "\n";
    } else {
        echo "Nenhum resumo disponível para a opção selecionada.\n";
    }

    echo "🔑 CREDENCIAIS DE ACESSO:\n";
    echo "=========================\n";
    echo '👨‍💼 Admin: ' . ($_ENV['ADMIN_EMAIL'] ?? 'admin@concessionaria.com') . ' / ' . ($_ENV['ADMIN_PASSWORD'] ?? 'admin123') . "\n";
    echo '🧑🏻‍🦲 Cliente: Use qualquer email gerado / ' . ($_ENV['CUSTOMER_PASSWORD'] ?? 'cliente123') . "\n\n";

    if (in_array($selectedOption, ['7', '9'])) {
        echo "⚙️  CONFIGURAÇÕES APLICADAS:\n";
        echo "============================\n";
        echo '🏢 Empresa: ' . ($_ENV['COMPANY_NAME'] ?? 'Concessionária M&D Ultra Max') . "\n";
        echo '⏰ Expiração de reserva: ' . ($_ENV['RESERVATION_EXPIRY_HOURS'] ?? '24') . " horas\n";
        echo '📊 Taxa do gateway: ' . ($_ENV['GATEWAY_FEE_PERCENTAGE'] ?? '3.5') . "%\n";
        echo '🌍 Timezone: ' . ($_ENV['TIMEZONE'] ?? 'America/Sao_Paulo') . "\n\n";
    }

    echo "✅ Sistema pronto para uso!\n";
} catch (Exception $e) {
    echo '❌ ERRO DURANTE O SEED: ' . $e->getMessage() . "\n";
    echo '📍 Arquivo: ' . $e->getFile() . ' (linha ' . $e->getLine() . ")\n";
    echo "🔍 Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
