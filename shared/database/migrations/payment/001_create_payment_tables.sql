-- Migration: Payment Service - Payments Table
-- Database: payment_db
-- Description: Sistema de processamento de pagamentos

USE payment_db;

-- Criar tabela de pagamentos
CREATE TABLE IF NOT EXISTS payments (
    id VARCHAR(36) PRIMARY KEY COMMENT 'UUID do pagamento',
    payment_code VARCHAR(50) UNIQUE NOT NULL COMMENT 'Código único do pagamento',
    reservation_id VARCHAR(36) COMMENT 'ID da reserva relacionada',
    customer_id VARCHAR(36) NOT NULL COMMENT 'ID do cliente',
    
    -- Informações do pagamento
    amount DECIMAL(10,2) NOT NULL COMMENT 'Valor do pagamento',
    currency VARCHAR(3) DEFAULT 'BRL' COMMENT 'Moeda',
    payment_method ENUM('credit_card', 'debit_card', 'pix', 'bank_transfer') NOT NULL COMMENT 'Método de pagamento',
    
    -- Status do pagamento
    status ENUM('pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded') DEFAULT 'pending' COMMENT 'Status do pagamento',
    
    -- Informações do cartão (se aplicável)
    card_last_four VARCHAR(4) COMMENT 'Últimos 4 dígitos do cartão',
    card_brand VARCHAR(20) COMMENT 'Bandeira do cartão',
    card_holder_name VARCHAR(255) COMMENT 'Nome no cartão',
    
    -- Informações PIX (se aplicável)
    pix_key VARCHAR(255) COMMENT 'Chave PIX utilizada',
    pix_qr_code TEXT COMMENT 'QR Code PIX',
    
    -- Gateway de pagamento
    gateway_transaction_id VARCHAR(255) COMMENT 'ID da transação no gateway',
    gateway_response JSON COMMENT 'Resposta completa do gateway',
    gateway_fee DECIMAL(8,2) COMMENT 'Taxa do gateway',
    
    -- Controle de tentativas
    attempts INT DEFAULT 0 COMMENT 'Número de tentativas',
    max_attempts INT DEFAULT 3 COMMENT 'Máximo de tentativas',
    
    -- Datas importantes
    processed_at TIMESTAMP NULL COMMENT 'Data do processamento',
    completed_at TIMESTAMP NULL COMMENT 'Data da conclusão',
    failed_at TIMESTAMP NULL COMMENT 'Data da falha',
    refunded_at TIMESTAMP NULL COMMENT 'Data do estorno',
    
    -- Informações de estorno
    refund_amount DECIMAL(10,2) COMMENT 'Valor estornado',
    refund_reason TEXT COMMENT 'Motivo do estorno',
    
    -- Observações
    notes TEXT COMMENT 'Observações do pagamento',
    failure_reason TEXT COMMENT 'Motivo da falha',
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
    
    -- Índices
    INDEX idx_payment_code (payment_code),
    INDEX idx_reservation_id (reservation_id),
    INDEX idx_customer_id (customer_id),
    INDEX idx_status (status),
    INDEX idx_payment_method (payment_method),
    INDEX idx_gateway_transaction_id (gateway_transaction_id),
    INDEX idx_created_at (created_at),
    INDEX idx_processed_at (processed_at),
    INDEX idx_customer_status (customer_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Pagamentos processados';

-- Criar tabela de transações do gateway
CREATE TABLE IF NOT EXISTS gateway_transactions (
    id VARCHAR(36) PRIMARY KEY COMMENT 'UUID da transação',
    payment_id VARCHAR(36) NOT NULL COMMENT 'ID do pagamento',
    
    -- Informações da transação
    transaction_type ENUM('payment', 'refund', 'chargeback') NOT NULL COMMENT 'Tipo da transação',
    gateway_name VARCHAR(50) NOT NULL COMMENT 'Nome do gateway',
    external_id VARCHAR(255) COMMENT 'ID externo da transação',
    
    -- Request/Response
    request_data JSON COMMENT 'Dados enviados para o gateway',
    response_data JSON COMMENT 'Resposta do gateway',
    
    -- Status e controle
    status VARCHAR(50) NOT NULL COMMENT 'Status da transação',
    amount DECIMAL(10,2) NOT NULL COMMENT 'Valor da transação',
    fee DECIMAL(8,2) COMMENT 'Taxa cobrada',
    
    -- Timestamps
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data do envio',
    received_at TIMESTAMP NULL COMMENT 'Data da resposta',
    
    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE,
    INDEX idx_payment_id (payment_id),
    INDEX idx_transaction_type (transaction_type),
    INDEX idx_gateway_name (gateway_name),
    INDEX idx_external_id (external_id),
    INDEX idx_status (status),
    INDEX idx_sent_at (sent_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Transações do gateway de pagamento';

