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

// Caminho do seeder principal
$seederPath = __DIR__ . '/seeder/DatabaseSeeder.php';
if (!file_exists($seederPath)) {
    echo "❌ Seeder principal não encontrado em $seederPath\n";
    exit(1);
}

// Executar o seeder principal
echo "🌱 Executando seeders...\n";
require_once $seederPath;

echo "\n🎉 Seeders executados com sucesso!\n";
echo "\n🔗 LINKS ÚTEIS:\n";
echo "===============\n";
echo "🌐 API Gateway: http://localhost:8000\n";
echo "📚 Documentação: http://localhost:8089\n";
echo "🗄️  phpMyAdmin: http://localhost:8090\n";
echo "🐰 RabbitMQ: http://localhost:15672\n";
echo "\n🔑 CREDENCIAIS:\n";
echo "===============\n";
echo "👨‍💼 Admin: admin@concessionaria.com / admin123\n";
echo "👨‍💻 Vendedor: vendedor1@concessionaria.com / vendedor123\n";
echo "👤 Cliente: Use qualquer email gerado / cliente123\n";