-- Migration: Auth Service - Users Table
-- Database: auth_db
-- Description: Tabela de usuários para autenticação e autorização

USE auth_db;

-- Criar tabela de usuários
CREATE TABLE IF NOT EXISTS users (
    id VARCHAR(36) PRIMARY KEY COMMENT 'UUID do usuário',
    name VARCHAR(255) NOT NULL COMMENT 'Nome completo do usuário',
    email VARCHAR(255) UNIQUE NOT NULL COMMENT 'Email único do usuário',
    password VARCHAR(255) NOT NULL COMMENT 'Hash da senha',
    phone VARCHAR(20) COMMENT 'Telefone do usuário',
    birth_date DATE COMMENT 'Data de nascimento',
    
    -- Endereço (JSON)
    street VARCHAR(255) COMMENT 'Rua do endereço',
    number VARCHAR(20) COMMENT 'Número do endereço',
    neighborhood VARCHAR(100) COMMENT 'Bairro',
    city VARCHAR(100) COMMENT 'Cidade',
    state VARCHAR(2) COMMENT 'Estado (UF)',
    zip_code VARCHAR(10) COMMENT 'CEP',
    
    role ENUM('customer', 'admin') DEFAULT 'customer' COMMENT 'Papel do usuário no sistema',
    -- Termos e privacidade
    accept_terms BOOLEAN DEFAULT FALSE COMMENT 'Aceitou termos de uso',
    accept_privacy BOOLEAN DEFAULT FALSE COMMENT 'Aceitou política de privacidade',
    accept_communications BOOLEAN DEFAULT FALSE COMMENT 'Aceitou comunicações',
        
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
    deleted_at TIMESTAMP NULL COMMENT 'Data de exclusão (soft delete)',
    
    -- Índices
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_created_at (created_at),
    INDEX idx_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabela de usuários do sistema';

-- Criar tabela de tokens de refresh
CREATE TABLE IF NOT EXISTS refresh_tokens (
    id VARCHAR(36) PRIMARY KEY COMMENT 'UUID do token',
    user_id VARCHAR(36) NOT NULL COMMENT 'ID do usuário',
    token_hash VARCHAR(255) NOT NULL COMMENT 'Hash do token',
    expires_at TIMESTAMP NOT NULL COMMENT 'Data de expiração',
    revoked BOOLEAN DEFAULT FALSE COMMENT 'Token revogado',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
    
    INDEX idx_user_id (user_id),
    INDEX idx_token_hash (token_hash),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tokens de refresh JWT';

-- Inserir usuário administrador padrão
INSERT IGNORE INTO users (
    id, name, email, password, phone, role, 
    street, number, neighborhood, city, state, zip_code,
    accept_terms, accept_privacy, accept_communications, created_at, updated_at
) VALUES (
    UUID(),
    'Administrador Sistema',
    'admin@concessionaria.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: admin123
    '11000000000',
    'admin',
    'Rua da Administração',
    '1',
    'Centro',
    'São Paulo',
    'SP',
    '00000-000',
    TRUE,
    TRUE,
    TRUE,
    NOW(),
    NOW()
);

