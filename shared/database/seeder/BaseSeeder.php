<?php

namespace Shared\Database\Seeder;

use PDO;
use PDOException;
use Ramsey\Uuid\Uuid;
use Dotenv\Dotenv;

abstract class BaseSeeder
{
    protected PDO $connection;
    protected string $database;
    protected array $config;
    
    public function __construct(string $database = '')
    {
        $this->loadEnvironment();
        $this->database = $database;
        $this->initializeConnection();
    }
    
    private function loadEnvironment(): void
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();
        
        $this->config = [
            'host' => $_ENV['DB_HOST'],
            'port' => $_ENV['DB_PORT'],
            'username' => $_ENV['DB_USERNAME'],
            'password' => $_ENV['DB_PASSWORD'],
            'timezone' => $_ENV['TIMEZONE'] ?? 'America/Sao_Paulo'
        ];
        
        // Configurar timezone
        date_default_timezone_set($this->config['timezone']);
    }
    
    private function initializeConnection(): void
    {
        // Detectar se está rodando no Docker
        $host = $this->config['host'];
        if (getenv('DOCKER_ENV') === 'true' || file_exists('/.dockerenv')) {
            $host = 'mysql';
            echo "🐳 Detectado ambiente Docker - usando host: mysql\n";
        }
        
        try {
            $dsn = "mysql:host={$host};port={$this->config['port']};dbname={$this->database};charset=utf8mb4";
            $this->connection = new PDO($dsn, $this->config['username'], $this->config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ]);
            
            echo "✅ Conectado ao banco: {$this->database}\n";
        } catch (PDOException $e) {
            echo "❌ Erro ao conectar ao banco {$this->database}: " . $e->getMessage() . "\n";
            exit(1);
        }
    }
    
    abstract public function run(): void;
    
    protected function truncateTable(string $table): void
    {
        try {
            $this->connection->exec("SET FOREIGN_KEY_CHECKS = 0");
            $this->connection->exec("TRUNCATE TABLE {$table}");
            $this->connection->exec("SET FOREIGN_KEY_CHECKS = 1");
            echo "🗑️  Tabela {$table} limpa\n";
        } catch (PDOException $e) {
            echo "⚠️  Aviso ao limpar tabela {$table}: " . $e->getMessage() . "\n";
        }
    }
    
    protected function insertBatch(string $table, array $data): void
    {
        if (empty($data)) {
            return;
        }
        
        $columns = array_keys($data[0]);
        $placeholders = ':' . implode(', :', $columns);
        $columnsList = implode(', ', $columns);
        
        $sql = "INSERT INTO {$table} ({$columnsList}) VALUES ({$placeholders})";
        $stmt = $this->connection->prepare($sql);
        
        $inserted = 0;
        foreach ($data as $row) {
            try {
                $stmt->execute($row);
                $inserted++;
            } catch (PDOException $e) {
                echo "⚠️  Erro ao inserir em {$table}: " . $e->getMessage() . "\n";
            }
        }
        
        echo "✅ Inseridos {$inserted} registros em {$table}\n";
    }
    
    protected function generateUuid(): string
    {
        return Uuid::uuid6()->toString();
    }
    
    protected function getCurrentTimestamp(): string
    {
        return date('Y-m-d H:i:s');
    }
    
    protected function getEnv(string $key, $default = null)
    {
        return $_ENV[$key] ?? $default;
    }
    
    protected function getDbConnection(string $database): PDO
    {
        $host = $this->config['host'];
        if (getenv('DOCKER_ENV') === 'true' || file_exists('/.dockerenv')) {
            $host = 'mysql';
        }
        
        $dsn = "mysql:host={$host};port={$this->config['port']};dbname={$database};charset=utf8mb4";
        return new PDO($dsn, $this->config['username'], $this->config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]);
    }
    
    protected function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
}

