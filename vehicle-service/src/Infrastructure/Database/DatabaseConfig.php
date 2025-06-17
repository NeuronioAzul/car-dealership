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
            CREATE TABLE IF NOT EXISTS vehicles (
                id VARCHAR(36) PRIMARY KEY,
                brand VARCHAR(100) NOT NULL,
                model VARCHAR(100) NOT NULL,
                color VARCHAR(50) NOT NULL,
                manufacturing_year INT NOT NULL,
                model_year INT NOT NULL,
                mileage INT NOT NULL,
                fuel_type ENUM('Etanol', 'Gasolina', 'Flex', 'Diesel') NOT NULL,
                body_type VARCHAR(50) NOT NULL,
                steering_type ENUM('Hidráulica', 'Elétrica', 'Mecânica') NOT NULL,
                price DECIMAL(10,2) NOT NULL,
                transmission_type ENUM('Manual', 'Automático', 'CVT') NOT NULL,
                seats INT NOT NULL,
                license_plate_end VARCHAR(10) NOT NULL,
                description TEXT,
                status ENUM('available', 'reserved', 'sold') DEFAULT 'available',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP NULL,
                INDEX idx_brand (brand),
                INDEX idx_model (model),
                INDEX idx_status (status),
                INDEX idx_price (price),
                INDEX idx_year (model_year),
                INDEX idx_fuel_type (fuel_type),
                INDEX idx_deleted_at (deleted_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";

        self::$connection->exec($sql);
    }
}

