<?php

namespace App\Infrastructure\Database;

use PDO;
use PDOException;

class DatabaseConfig
{
    private static ?PDO $connection = null;

    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            try {
                $host = $_ENV['DB_HOST'];
                $port = $_ENV['DB_PORT'];
                $database = $_ENV['DB_DATABASE'];
                $username = $_ENV['DB_USERNAME'];
                $password = $_ENV['DB_PASSWORD'];

                $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
                
                self::$connection = new PDO($dsn, $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
                
                // Criar tabelas se não existirem
                self::createTables();
                
            } catch (PDOException $e) {
                throw new \Exception('Erro na conexão com o banco de dados: ' . $e->getMessage());
            }
        }

        return self::$connection;
    }

    private static function createTables(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS saga_transactions (
                id VARCHAR(36) PRIMARY KEY,
                customer_id VARCHAR(36) NOT NULL,
                vehicle_id VARCHAR(36) NOT NULL,
                type VARCHAR(50) NOT NULL,
                status ENUM('started', 'in_progress', 'completed', 'failed', 'compensating', 'compensated') DEFAULT 'started',
                steps JSON NOT NULL,
                completed_steps JSON NOT NULL,
                compensation_steps JSON NOT NULL,
                current_step VARCHAR(100) NULL,
                failure_reason TEXT NULL,
                context JSON NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                completed_at TIMESTAMP NULL,
                INDEX idx_customer_id (customer_id),
                INDEX idx_vehicle_id (vehicle_id),
                INDEX idx_status (status),
                INDEX idx_type (type),
                INDEX idx_current_step (current_step)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";

        self::$connection->exec($sql);
    }
}

