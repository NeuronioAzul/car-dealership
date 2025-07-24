<?php

declare(strict_types=1);
// filepath: /migrations/migrate.php

// Caminho absoluto para a raiz do projeto (ajuste se necessário)
$projectRoot = __DIR__ . '/..';

// Se não encontrar a pasta vendor, avisa para rodar o composer install
if (!file_exists($projectRoot . '/vendor/autoload.php')) {
    echo "❌ Pasta 'vendor' não encontrada! Por favor, execute 'make shared-install' na raiz do projeto.\n";
    exit(1);
}

// Carrega o autoload do Composer da raiz do projeto
require_once $projectRoot . '/vendor/autoload.php';

use Dotenv\Dotenv;

// Carrega as variáveis de ambiente do arquivo .env na raiz do projeto
if (!file_exists($projectRoot . '/.env')) {
    echo "❌ Arquivo .env não encontrado na raiz do projeto! Por favor, crie um arquivo .env com as variáveis necessárias.\n";
    exit(1);
}
$dotenv = Dotenv::createImmutable($projectRoot);
$dotenv->load();

/**
 * Script para executar todas as migrations do sistema (MySQL rodando dentro do container)
 * Uso: php /migrations/migrate_docker.php [--fresh]
 */
function color($text, $color)
{
    $colors = [
        'red' => "\033[0;31m",
        'green' => "\033[0;32m",
        'yellow' => "\033[1;33m",
        'blue' => "\033[0;34m",
        'reset' => "\033[0m",
    ];

    return $colors[$color] . $text . $colors['reset'];
}

function log_info($msg)
{
    echo color("ℹ️  $msg\n", 'blue');
}
function log_success($msg)
{
    echo color("✅ $msg\n", 'green');
}
function log_warning($msg)
{
    echo color("⚠️  $msg\n", 'yellow');
}
function log_error($msg)
{
    echo color("❌ $msg\n", 'red');
}

echo "🗄️  Car Dealership - Database Migrations (Docker)\n";
echo "========================================\n";

// Função para executar migration
function run_migration($service, $migration_file, $db_name)
{
    log_info("Executando migration: $service/$migration_file");

    $file_path = __DIR__ . "/migrations/$service/$migration_file";

    if (!file_exists($file_path)) {
        log_error("Arquivo de migration não encontrado: /migrations/$service/$migration_file");

        return false;
    }

    $db_user = getenv('DB_USERNAME');
    $db_password = getenv('DB_PASSWORD');
    $db_host = getenv('DB_HOST') ?: 'localhost'; // Padrão para localhost
    $db_port = getenv('DB_PORT') ?: '3306'; // Padrão para porta 3306
    $cmd = "mysql -h $db_host -P $db_port -u $db_user -p$db_password $db_name < \"$file_path\" 2>&1";
    exec($cmd, $output, $status);
    $outputText = implode("\n", $output);

    if ($status === 0) {
        log_success("Migration executada: /migrations/$service/$migration_file");

        return true;
    } else {
        log_error("Falha na migration: /migrations/$service/$migration_file");
        echo color("Detalhe do erro:\n$outputText\n", 'red');

        return false;
    }
}

// Função para criar banco se não existir
function create_database($db_name)
{
    log_info("Criando banco de dados: $db_name");
    $db_user = getenv('DB_USERNAME');
    $db_password = getenv('DB_PASSWORD');
    $db_host = getenv('DB_HOST') ?: 'localhost'; // Padrão para localhost
    $db_port = getenv('DB_PORT') ?: '3306'; // Padrão para porta 3306
    $cmd = "mysql -h $db_host -P $db_port -u $db_user -p$db_password -e \"CREATE DATABASE IF NOT EXISTS $db_name;\" 2>&1";
    exec($cmd, $output, $status);

    if ($status === 0) {
        log_success("Banco criado/verificado: $db_name");

        return true;
    } else {
        log_error("Falha ao criar banco: $db_name");
        echo color("Detalhe do erro:\n" . implode("\n", $output) . "\n", 'red');

        return false;
    }
}

// Função para excluir banco de dados
function drop_database($db_name)
{
    log_info("Excluindo banco de dados: $db_name");
    $db_user = getenv('DB_USERNAME');
    $db_password = getenv('DB_PASSWORD');
    $db_host = getenv('DB_HOST') ?: 'localhost'; // Padrão para localhost
    $db_port = getenv('DB_PORT') ?: '3306'; // Padrão para porta 3306
    $cmd = "mysql -h $db_host -P $db_port -u $db_user -p$db_password -e \"DROP DATABASE IF EXISTS $db_name;\" 2>&1";
    exec($cmd, $output, $status);

    if ($status === 0) {
        log_success("Banco excluído: $db_name");

        return true;
    } else {
        log_error("Falha ao excluir banco: $db_name");
        echo color("Detalhe do erro:\n" . implode("\n", $output) . "\n", 'red');

        return false;
    }
}

// Verifica se foi passado o parâmetro --fresh
$fresh = in_array('--fresh', $argv);

// Lista de bancos de dados
$databases = [
    'auth_db', 'customer_db', 'vehicle_db', 'reservation_db',
    'payment_db', 'sales_db', 'admin_db', 'saga_db',
];

// Se --fresh, excluir todos os bancos antes de criar
if ($fresh) {
    log_warning('Parâmetro --fresh detectado: Isso irá recriar todos os bancos de dados!');
    $confirm = readline('Tem certeza que deseja excluir todos os bancos de dados? (s/N): ');

    if (strtolower($confirm) !== 's') {
        log_info('Operação cancelada pelo usuário.');
        exit(0);
    }
    foreach ($databases as $db) {
        drop_database($db);
    }
}

// Criar todos os bancos de dados
log_info('Criando bancos de dados...');
foreach ($databases as $db) {
    create_database($db);
}

echo "\n";
log_info('Executando migrations...');

// Verifica se o diretório de migrations existe
log_info('Verificando se o diretório de migrations existe...');
$migration_dir = __DIR__ . '/migrations';

if (!is_dir($migration_dir)) {
    log_error("Diretório de migrations não encontrado: $migration_dir");
    exit(1);
}

// Lista de migrations na ordem correta
log_info('Listando as migrations disponíveis...');
$files = scandir($migration_dir);
$migrations = [];
$services = ['auth', 'vehicle', 'customer', 'reservation', 'payment', 'sales', 'saga', 'admin'];
log_info('Preparando as migrations para execução...');
foreach ($files as $file) {
    if (is_file($migration_dir . '/' . $file) && preg_match('/^(\w+):(\d+_.*\.sql)$/', $file, $matches)) {
        $service = $matches[1];

        if (in_array($service, $services)) {
            $migrations[] = "$service:$matches[2]";
        } else {
            log_warning("Serviço desconhecido na migration: $file");
        }
    } elseif (is_dir($migration_dir . '/' . $file) && in_array($file, $services)) {
        // Se for um diretório de serviço, procurar migrations dentro dele
        $sub_files = scandir($migration_dir . '/' . $file);
        foreach ($sub_files as $sub_file) {
            if (is_file($migration_dir . '/' . $file . '/' . $sub_file) && preg_match('/^(\d+_.*\.sql)$/', $sub_file)) {
                $migrations[] = "$file:$sub_file";
            }
        }
    }
}

// Executar migrations
$failed_migrations = [];
$successful_migrations = [];

foreach ($migrations as $migration) {
    [$service, $file] = explode(':', $migration, 2);
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
    log_success('Migrations executadas com sucesso (' . count($successful_migrations) . '):');
    foreach ($successful_migrations as $migration) {
        echo "  ✅ $migration\n";
    }
}

if (count($failed_migrations) > 0) {
    echo "\n";
    log_error('Migrations que falharam (' . count($failed_migrations) . '):');
    foreach ($failed_migrations as $migration) {
        echo "  ❌ $migration\n";
    }
    echo "\n";
    log_warning('Execute novamente o script para tentar corrigir as falhas');
    exit(1);
}

echo "\n";
log_success('Todas as migrations foram executadas com sucesso!');

// Verificar estrutura criada
log_info('Verificando estrutura criada...');

// Contar tabelas por banco
foreach ($databases as $db) {
    log_info("Contando tabelas no banco: $db");
    $db_user = getenv('DB_USERNAME');
    $db_password = getenv('DB_PASSWORD');
    $db_host = getenv('DB_HOST') ?: 'localhost'; // Padrão para localhost
    $db_port = getenv('DB_PORT') ?: '3306'; // Padrão para porta 3306
    $cmd = "mysql -h $db_host -P $db_port -u $db_user -p$db_password -e \"SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = '$db';\" 2>&1";
    exec($cmd, $output, $status);
    $table_count = 0;
    foreach ($output as $line) {
        if (is_numeric(trim($line))) {
            $table_count = (int) trim($line);
            break;
        }
    }

    if ($table_count > 0) {
        log_success("$db: $table_count tabelas criadas");
    } else {
        log_warning("$db: Nenhuma tabela encontrada");
    }
    unset($output);
}

echo "\n";
log_success('🎉 Sistema de banco de dados configurado com sucesso!');
echo "\n";
echo "📋 Próximos passos:\n";
echo "  1. Execute: make seeder (para dados de exemplo)\n";
echo "  2. Teste os endpoints da API\n";
echo "\n";
