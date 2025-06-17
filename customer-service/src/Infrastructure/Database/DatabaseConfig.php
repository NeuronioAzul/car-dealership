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
            -- Criar tabela de perfis de clientes
            CREATE TABLE IF NOT EXISTS customer_profiles (
                id VARCHAR(36) PRIMARY KEY COMMENT 'UUID do cliente (mesmo do auth)',

                -- Informações pessoais
                full_name VARCHAR(255) NOT NULL COMMENT 'Nome completo',
                email VARCHAR(255) UNIQUE NOT NULL COMMENT 'Email',
                cpf VARCHAR(11) UNIQUE NOT NULL COMMENT 'CPF',
                rg VARCHAR(20) COMMENT 'RG',
                birth_date DATE COMMENT 'Data de nascimento',
                gender ENUM('M', 'F', 'Other') COMMENT 'Gênero',
                marital_status ENUM('single', 'married', 'divorced', 'widowed') COMMENT 'Estado civil',
                
                -- Contato
                phone VARCHAR(20) COMMENT 'Telefone principal',
                mobile VARCHAR(20) COMMENT 'Celular',
                whatsapp VARCHAR(20) COMMENT 'WhatsApp',
                
                -- Endereço principal
                street VARCHAR(255) COMMENT 'Rua',
                number VARCHAR(20) COMMENT 'Número',
                complement VARCHAR(100) COMMENT 'Complemento',
                neighborhood VARCHAR(100) COMMENT 'Bairro',
                city VARCHAR(100) COMMENT 'Cidade',
                state VARCHAR(2) COMMENT 'Estado',
                zip_code VARCHAR(10) COMMENT 'CEP',
                
                -- Informações profissionais
                occupation VARCHAR(255) COMMENT 'Profissão',
                company VARCHAR(255) COMMENT 'Empresa',
                monthly_income DECIMAL(10,2) COMMENT 'Renda mensal',
                
                -- Preferências
                preferred_contact ENUM('email', 'phone', 'whatsapp') DEFAULT 'email' COMMENT 'Forma preferida de contato',
                newsletter_subscription BOOLEAN DEFAULT FALSE COMMENT 'Inscrição newsletter',
                sms_notifications BOOLEAN DEFAULT FALSE COMMENT 'Notificações SMS',
                
                -- Histórico de compras
                total_purchases INT DEFAULT 0 COMMENT 'Total de compras realizadas',
                total_spent DECIMAL(12,2) DEFAULT 0.00 COMMENT 'Total gasto',
                last_purchase_date TIMESTAMP NULL COMMENT 'Data da última compra',
                
                -- Score e classificação
                customer_score INT DEFAULT 0 COMMENT 'Score do cliente (0-1000)',
                customer_tier ENUM('bronze', 'silver', 'gold', 'platinum') DEFAULT 'bronze' COMMENT 'Categoria do cliente',
                
                -- Timestamps
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
                deleted_at TIMESTAMP NULL COMMENT 'Data de exclusão (soft delete)',
                
                -- Índices
                INDEX idx_user_id (user_id),
                INDEX idx_email (email),
                INDEX idx_cpf (cpf),
                INDEX idx_city_state (city, state),
                INDEX idx_customer_tier (customer_tier),
                INDEX idx_total_spent (total_spent),
                INDEX idx_created_at (created_at),
                INDEX idx_deleted_at (deleted_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Perfis detalhados dos clientes';
        ";

        self::$connection->exec($sql);
    }
}

