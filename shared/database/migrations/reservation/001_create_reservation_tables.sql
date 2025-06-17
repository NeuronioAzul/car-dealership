-- Migration: Reservation Service - Reservations Table
-- Database: reservation_db
-- Description: Sistema de reservas de veículos

USE reservation_db;

-- Criar tabela de reservas
CREATE TABLE IF NOT EXISTS reservations (
    id VARCHAR(36) PRIMARY KEY COMMENT 'UUID da reserva',
    customer_id VARCHAR(36) NOT NULL COMMENT 'ID do cliente',
    vehicle_id VARCHAR(36) NOT NULL COMMENT 'ID do veículo',
    
    -- Informações da reserva
    reservation_code VARCHAR(20) UNIQUE NOT NULL COMMENT 'Código único da reserva',
    status ENUM('active', 'expired', 'cancelled', 'completed') DEFAULT 'active' COMMENT 'Status da reserva',
    
    -- Datas importantes
    reserved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data da reserva',
    expires_at TIMESTAMP NOT NULL COMMENT 'Data de expiração (24h)',
    cancelled_at TIMESTAMP NULL COMMENT 'Data do cancelamento',
    completed_at TIMESTAMP NULL COMMENT 'Data da conclusão',

    vehicle_price DECIMAL(10,2) NOT NULL COMMENT 'Preço na data da reserva',

    -- Observações
    notes TEXT COMMENT 'Observações da reserva',
    cancellation_reason TEXT COMMENT 'Motivo do cancelamento',
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
    
    -- Índices
    INDEX idx_customer_id (customer_id),
    INDEX idx_vehicle_id (vehicle_id),
    INDEX idx_reservation_code (reservation_code),
    INDEX idx_status (status),
    INDEX idx_expires_at (expires_at),
    INDEX idx_created_at (created_at),
    INDEX idx_customer_status (customer_id, status),
    INDEX idx_vehicle_status (vehicle_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Reservas de veículos';

-- Criar tabela de códigos de pagamento
CREATE TABLE IF NOT EXISTS payment_codes (
    id VARCHAR(36) PRIMARY KEY COMMENT 'UUID do código',
    reservation_id VARCHAR(36) NOT NULL COMMENT 'ID da reserva',
    payment_code VARCHAR(50) UNIQUE NOT NULL COMMENT 'Código de pagamento único',
    
    -- Informações do pagamento
    amount DECIMAL(10,2) NOT NULL COMMENT 'Valor do pagamento',
    
    -- Status e controle
    status ENUM('pending', 'used', 'expired', 'cancelled') DEFAULT 'pending' COMMENT 'Status do código',
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de geração',
    expires_at TIMESTAMP NOT NULL COMMENT 'Data de expiração',
    used_at TIMESTAMP NULL COMMENT 'Data de uso',
    
    -- Informações adicionais
    payment_method ENUM('credit_card', 'debit_card', 'pix', 'bank_transfer') COMMENT 'Método de pagamento sugerido',
    instructions TEXT COMMENT 'Instruções de pagamento',
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
    
    -- FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE CASCADE,
    INDEX idx_reservation_id (reservation_id),
    INDEX idx_payment_code (payment_code),
    INDEX idx_status (status),
    INDEX idx_expires_at (expires_at),
    INDEX idx_generated_at (generated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Códigos de pagamento para reservas';

-- Criar tabela de histórico de reservas
CREATE TABLE IF NOT EXISTS reservation_history (
    id VARCHAR(36) PRIMARY KEY COMMENT 'UUID do histórico',
    reservation_id VARCHAR(36) NOT NULL COMMENT 'ID da reserva',
    
    -- Mudança de status
    previous_status VARCHAR(50) COMMENT 'Status anterior',
    new_status VARCHAR(50) NOT NULL COMMENT 'Novo status',
    
    -- Detalhes da mudança
    changed_by VARCHAR(36) COMMENT 'ID do usuário que fez a mudança',
    change_reason TEXT COMMENT 'Motivo da mudança',
    additional_data JSON COMMENT 'Dados adicionais da mudança',
    
    -- Timestamp
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data da mudança',
    
    -- FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE CASCADE,
    INDEX idx_reservation_id (reservation_id),
    INDEX idx_changed_at (changed_at),
    INDEX idx_new_status (new_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Histórico de mudanças nas reservas';

