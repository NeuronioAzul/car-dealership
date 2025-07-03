-- Migration: Customer Service - Customer Profiles Table
-- Database: customer_db
-- Description: Perfis de clientes (espelho dos dados de auth)

USE customer_db;

-- Criar tabela de perfis de clientes
CREATE TABLE IF NOT EXISTS customer_profiles (
    id VARCHAR(36) PRIMARY KEY COMMENT 'UUID do cliente (mesmo do auth)',
    user_id VARCHAR(36) UNIQUE NOT NULL COMMENT 'ID do usuário associado (mesmo do auth)',
    
    -- Informações pessoais
    full_name VARCHAR(255) NOT NULL COMMENT 'Nome completo',
    birth_date DATE COMMENT 'Data de nascimento',
    cpf VARCHAR(11) UNIQUE NOT NULL COMMENT 'CPF',
    rg VARCHAR(20) COMMENT 'RG',
    gender ENUM('M', 'F', 'Other') COMMENT 'Gênero',
    marital_status ENUM('Single', 'Married', 'Divorced', 'Widowed', 'Common Law') COMMENT 'Estado civil',
    
    -- Contato
    email VARCHAR(255) UNIQUE NOT NULL COMMENT 'Email',
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
    INDEX idx_email (email),
    INDEX idx_cpf (cpf),
    INDEX idx_city_state (city, state),
    INDEX idx_customer_tier (customer_tier),
    INDEX idx_total_spent (total_spent),
    INDEX idx_created_at (created_at),
    INDEX idx_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Perfis detalhados dos clientes';
