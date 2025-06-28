<?php
// filepath: /home/mauro/projects/car-dealership/shared/database/seed.php

echo "🌱 EXECUTANDO SEEDERS DO SISTEMA DE CONCESSIONÁRIA\n";
echo "==================================================\n";

// Verificar se o autoload existe
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    echo "❌ Autoload não encontrado. Execute 'composer install' primeiro.\n";
    exit(1);
}

require_once $autoloadPath;

$menuOptions = [
    '1' => '👥 Executar Seeder de Usuários',
    '2' => '🚗 Executar Seeder de Veículos',
    '3' => '👤 Executar Seeder de Clientes',
    '4' => '📅 Executar Seeder de Reservas',
    '5' => '💳 Executar Seeder de Pagamentos',
    '6' => '💼 Executar Seeder de Vendas',
    '7' => '🛠️ Executar Seeder Administrativo',
    '8' => '🔄 Executar Seeder SAGA',
    '9' => '🌱 Executar Todos os Seeders',
    '0' => '❌ Sair'
];

// Função para exibir o menu
function displayMenu($options) {
    echo "\nEscolha uma opção:\n";
    foreach ($options as $key => $value) {
        echo "[$key] $value\n";
    }
}

$selectedOption = null;

while ($selectedOption === null) {
    displayMenu($menuOptions);
    $input = trim(fgets(STDIN));

    if (array_key_exists($input, $menuOptions)) {
        $selectedOption = $input;
    } elseif ($input === '') {
        continue; // Executar todos os seeders
    } else {
        echo "❌ Opção inválida. Tente novamente.\n\n";
    }
}


// Caminho do seeder principal
$seederPath = __DIR__ . '/seeder/DatabaseSeeder.php';

if (!file_exists($seederPath)) {
    echo "❌ Seeder principal não encontrado em $seederPath\n";
    exit(1);
}


// Executar o seeder principal
echo "\n\n🌱 Executando seeders...\n\n";

require_once $seederPath;

