<?php
// filepath: /home/mauro/projects/car-dealership/shared/database/migrations/run-migrations.php

/**
 * Script para executar todas as migrations do sistema (MySQL via Docker)
 * Uso: php run-migrations.php
 */

function color($text, $color) {
    $colors = [
        'red'    => "\033[0;31m",
        'green'  => "\033[0;32m",
        'yellow' => "\033[1;33m",
        'blue'   => "\033[0;34m",
        'reset'  => "\033[0m"
    ];
    return $colors[$color] . $text . $colors['reset'];
}

function log_info($msg)    { echo color("ℹ️  $msg\n", 'blue'); }
function log_success($msg) { echo color("✅ $msg\n", 'green'); }
function log_warning($msg) { echo color("⚠️  $msg\n", 'yellow'); }
function log_error($msg)   { echo color("❌ $msg\n", 'red'); }

echo "🗄️  Car Dealership - Database Migrations\n";
echo "========================================\n";

// Verificar se Docker está rodando
exec('docker info > /dev/null 2>&1', $out, $dockerStatus);
if ($dockerStatus !== 0) {
    log_error("Docker não está rodando!");
    echo "💡 Inicie o Docker e execute: docker-compose up -d\n";
    exit(1);
}

// Verificar se MySQL está rodando
log_info("Verificando se MySQL está disponível...");
for ($i = 1; $i <= 30; $i++) {
    exec('docker-compose exec -T mysql mysqladmin ping -h localhost --silent 2>/dev/null', $out, $mysqlStatus);
    if ($mysqlStatus === 0) {
        log_success("MySQL está pronto!");
        break;
    }
    if ($i === 30) {
        log_error("MySQL não está respondendo após 30 tentativas");
        exit(1);
    }
    echo "⏳ Aguardando MySQL... ($i/30)\n";
    sleep(2);
}

// Função para executar migration
function run_migration($service, $migration_file, $db_name) {
    log_info("Executando migration: $service/$migration_file");

    $file_path = __DIR__ . "/$service/$migration_file";
    if (!file_exists($file_path)) {
        log_error("Arquivo de migration não encontrado: $service/$migration_file");
        return false;
    }

    $cmd = "docker-compose exec -T mysql mysql -u root -prootpassword123 $db_name < \"$file_path\" 2>&1";
    exec($cmd, $output, $status);
    $outputText = implode("\n", $output);

    if ($status === 0) {
        log_success("Migration executada: $service/$migration_file");
        return true;
    } else {
        log_error("Falha na migration: $service/$migration_file");
        echo color("Detalhe do erro:\n$outputText\n", 'red');
        return false;
    }
}

// Função para criar banco se não existir
function create_database($db_name) {
    log_info("Criando banco de dados: $db_name");
    $cmd = "docker-compose exec -T mysql mysql -u root -prootpassword123 -e \"CREATE DATABASE IF NOT EXISTS $db_name;\" 2>/dev/null";
    exec($cmd, $output, $status);
    if ($status === 0) {
        log_success("Banco criado/verificado: $db_name");
        return true;
    } else {
        log_error("Falha ao criar banco: $db_name");
        return false;
    }
}

// Verifica se foi passado o parâmetro --fresh
$fresh = in_array('--fresh', $argv);

// Lista de bancos de dados
$databases = [
    "auth_db", "customer_db", "vehicle_db", "reservation_db",
    "payment_db", "sales_db", "admin_db", "saga_db"
];

// Se --fresh, excluir todos os bancos antes de criar
if ($fresh) {
    log_warning("Parâmetro --fresh detectado: Isso irá recriar todos os bancos de dados!");
// pergunta se tem certeza
    $confirm = readline("Tem certeza que deseja excluir todos os bancos de dados? (s/N): ");
    if (strtolower($confirm) !== 's') {
        log_info("Operação cancelada pelo usuário.");
        exit(0);
    }    
    foreach ($databases as $db) {
        $cmd = "docker-compose exec -T mysql mysql -u root -prootpassword123 -e \"DROP DATABASE IF EXISTS $db;\" 2>/dev/null";
        exec($cmd, $output, $status);
        if ($status === 0) {
            log_success("Banco excluído: $db");
        } else {
            log_error("Falha ao excluir banco: $db");
        }
    }
}

// Criar todos os bancos de dados
log_info("Criando bancos de dados...");
foreach ($databases as $db) {
    create_database($db);
}

echo "\n";
log_info("Executando migrations...");

// Lista de migrations na ordem correta
$migrations = [
    "auth:001_create_users_table.sql",
    "vehicle:001_create_vehicles_table.sql",
    "customer:001_create_customer_tables.sql",
    "reservation:001_create_reservation_tables.sql",
    "payment:001_create_payment_tables.sql",
    "sales:001_create_sales_tables.sql",
    "saga:001_create_saga_tables.sql",
    "admin:001_create_admin_tables.sql"
];

// Executar migrations
$failed_migrations = [];
$successful_migrations = [];

foreach ($migrations as $migration) {
    list($service, $file) = explode(':', $migration, 2);
    $db_name = "{$service}_db";
    if (run_migration($service, $file, $db_name)) {
        $successful_migrations[] = $migration;
    } else {
        $failed_migrations[] = $migration;
    }
    echo "\n";
}

// Relatório final
echo "📊 Relatório de Migrations\n";
echo "==========================\n";

if (count($successful_migrations) > 0) {
    log_success("Migrations executadas com sucesso (" . count($successful_migrations) . "):");
    foreach ($successful_migrations as $migration) {
        echo "  ✅ $migration\n";
    }
}

if (count($failed_migrations) > 0) {
    echo "\n";
    log_error("Migrations que falharam (" . count($failed_migrations) . "):");
    foreach ($failed_migrations as $migration) {
        echo "  ❌ $migration\n";
    }
    echo "\n";
    log_warning("Execute novamente o script para tentar corrigir as falhas");
    exit(1);
}

echo "\n";
log_success("Todas as migrations foram executadas com sucesso!");

// Verificar estrutura criada
log_info("Verificando estrutura criada...");

// Contar tabelas por banco
foreach ($databases as $db) {
    $cmd = "docker-compose exec -T mysql mysql -u root -prootpassword123 -e \"SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = '$db';\" 2>/dev/null";
    exec($cmd, $output, $status);
    $table_count = isset($output[1]) ? (int)trim($output[1]) : 0;
    if ($table_count > 0) {
        log_success("$db: $table_count tabelas criadas");
    } else {
        log_warning("$db: Nenhuma tabela encontrada");
    }
    unset($output);
}

echo "\n";
log_success("🎉 Sistema de banco de dados configurado com sucesso!");
echo "\n";
echo "📋 Próximos passos:\n";
echo "  1. Execute: ./seed-database.php (para dados de exemplo)\n";
echo "  2. Teste os endpoints da API\n";
echo "  3. Acesse o painel administrativo\n";
echo "\n";
echo "🌐 URLs úteis:\n";
echo "  - API: http://localhost:8000/api/v1\n";
echo "  - Documentação: http://localhost:8089\n";
echo "  - phpMyAdmin: http://localhost:8090\n";
echo "  - RabbitMQ: http://localhost:15672\n";
echo "\n";