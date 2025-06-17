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
            CREATE TABLE IF NOT EXISTS payments (
                id VARCHAR(36) PRIMARY KEY,
                customer_id VARCHAR(36) NOT NULL,
                reservation_id VARCHAR(36) NOT NULL,
                vehicle_id VARCHAR(36) NOT NULL,
                payment_code VARCHAR(20) NOT NULL UNIQUE,
                amount DECIMAL(10,2) NOT NULL,
                status ENUM('pending', 'processing', 'completed', 'failed', 'refunded') DEFAULT 'pending',
                method ENUM('credit_card', 'debit_card', 'pix', 'bank_transfer') DEFAULT 'credit_card',
                transaction_id VARCHAR(100) NULL,
                gateway_response TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                processed_at TIMESTAMP NULL,
                deleted_at TIMESTAMP NULL,
                INDEX idx_customer_id (customer_id),
                INDEX idx_reservation_id (reservation_id),
                INDEX idx_vehicle_id (vehicle_id),
                INDEX idx_payment_code (payment_code),
                INDEX idx_status (status),
                INDEX idx_transaction_id (transaction_id),
                INDEX idx_deleted_at (deleted_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";

        self::$connection->exec($sql);
    }
}

