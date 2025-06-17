#!/bin/bash

echo "🌱 EXECUTANDO SEEDERS DO SISTEMA DE CONCESSIONÁRIA"
echo "=================================================="

# Verificar se o Docker está rodando
if ! docker ps > /dev/null 2>&1; then
    echo "❌ Docker não está rodando. Execute 'docker-compose up -d' primeiro."
    exit 1
fi

# Verificar se os containers estão rodando
if ! docker-compose ps | grep -q "Up"; then
    echo "❌ Containers não estão rodando. Execute 'docker-compose up -d' primeiro."
    exit 1
fi

# Aguardar MySQL estar pronto
echo "⏳ Aguardando MySQL estar pronto..."
until docker-compose exec -T mysql mysqladmin ping -h localhost --silent; do
    echo "   Aguardando MySQL..."
    sleep 2
done

echo "✅ MySQL está pronto!"
echo ""

# Executar migrations primeiro
echo "🗄️  Executando migrations..."
if [ -f "./run-migrations.sh" ]; then
    ./run-migrations.sh
else
    echo "⚠️  Arquivo run-migrations.sh não encontrado, pulando migrations..."
fi

echo ""

# Executar seeders
echo "🌱 Executando seeders..."
cd /home/ubuntu/car-dealership/shared

# Verificar se o autoload existe
if [ ! -f "vendor/autoload.php" ]; then
    echo "❌ Autoload não encontrado. Execute 'composer install' primeiro."
    exit 1
fi

# Executar o seeder principal
php database/seeder/DatabaseSeeder.php

echo ""
echo "🎉 Seeders executados com sucesso!"
echo ""
echo "🔗 LINKS ÚTEIS:"
echo "==============="
echo "🌐 API Gateway: http://localhost:8000"
echo "📚 Documentação: http://localhost:8089"
echo "🗄️  phpMyAdmin: http://localhost:8090"
echo "🐰 RabbitMQ: http://localhost:15672"
echo ""
echo "🔑 CREDENCIAIS:"
echo "==============="
echo "👨‍💼 Admin: admin@concessionaria.com / admin123"
echo "👨‍💻 Vendedor: vendedor1@concessionaria.com / vendedor123"
echo "👤 Cliente: Use qualquer email gerado / cliente123"

