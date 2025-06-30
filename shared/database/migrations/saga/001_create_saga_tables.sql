-- Migration: SAGA Orchestrator - Transactions Table
-- Database: saga_db
-- Description: Orquestração de transações distribuídas

USE saga_db;

-- Criar tabela de transações SAGA
CREATE TABLE IF NOT EXISTS saga_transactions (
    id VARCHAR(36) PRIMARY KEY COMMENT 'UUID da transação SAGA',
    transaction_type VARCHAR(50) NOT NULL COMMENT 'Tipo da transação (ex: vehicle_purchase)',
    -- Status da transação
    status ENUM('started', 'in_progress', 'completed', 'failed', 'compensating', 'compensated') DEFAULT 'started' COMMENT 'Status da transação',
    current_step VARCHAR(100) COMMENT 'Passo atual da transação',
    -- Contexto da transação
    context JSON NOT NULL COMMENT 'Contexto completo da transação',
    -- Informações do cliente
    customer_id VARCHAR(36) NOT NULL COMMENT 'ID do cliente',
    customer_name VARCHAR(255) COMMENT 'Nome do cliente',
    customer_email VARCHAR(255) COMMENT 'Email do cliente',
    -- Informações do veículo (se aplicável)
    vehicle_id VARCHAR(36) COMMENT 'ID do veículo',
    vehicle_info JSON COMMENT 'Informações do veículo',
    -- Controle de execução
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de início',
    completed_at TIMESTAMP NULL COMMENT 'Data de conclusão',
    failed_at TIMESTAMP NULL COMMENT 'Data da falha',
    -- Informações de erro
    error_message TEXT COMMENT 'Mensagem de erro',
    error_details JSON COMMENT 'Detalhes do erro',
    -- Controle de retry
    retry_count INT DEFAULT 0 COMMENT 'Número de tentativas',
    max_retries INT DEFAULT 3 COMMENT 'Máximo de tentativas',
    next_retry_at TIMESTAMP NULL COMMENT 'Próxima tentativa',
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
    deleted_at TIMESTAMP NULL COMMENT 'Data de exclusão (soft delete)',
    -- Índices
    INDEX idx_saga_transaction_id (saga_transaction_id),
    INDEX idx_status (status),
    INDEX idx_customer_id (customer_id),
    INDEX idx_vehicle_id (vehicle_id),
    INDEX idx_current_step (current_step),
    INDEX idx_started_at (started_at),
    INDEX idx_next_retry_at (next_retry_at),
    INDEX idx_status_retry (status, next_retry_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Transações SAGA';

-- Criar tabela de passos da transação
CREATE TABLE IF NOT EXISTS saga_steps (
    id VARCHAR(36) PRIMARY KEY COMMENT 'UUID do passo',
    saga_transaction_id VARCHAR(36) NOT NULL COMMENT 'ID da transação SAGA',
    -- Informações do passo
    step_name VARCHAR(100) NOT NULL COMMENT 'Nome do passo',
    step_order INT NOT NULL COMMENT 'Ordem de execução',
    service_name VARCHAR(100) NOT NULL COMMENT 'Nome do serviço',
    -- Status do passo
    status ENUM('pending', 'executing', 'completed', 'failed', 'compensating', 'compensated', 'skipped') DEFAULT 'pending' COMMENT 'Status do passo',
    -- Dados do passo
    input_data JSON COMMENT 'Dados de entrada',
    output_data JSON COMMENT 'Dados de saída',
    -- Controle de execução
    started_at TIMESTAMP NULL COMMENT 'Data de início',
    completed_at TIMESTAMP NULL COMMENT 'Data de conclusão',
    failed_at TIMESTAMP NULL COMMENT 'Data da falha',
    -- Informações de erro
    error_message TEXT COMMENT 'Mensagem de erro',
    error_details JSON COMMENT 'Detalhes do erro',
    -- Compensação
    compensation_action VARCHAR(100) COMMENT 'Ação de compensação',
    compensated_at TIMESTAMP NULL COMMENT 'Data da compensação',
    -- Controle de retry
    retry_count INT DEFAULT 0 COMMENT 'Número de tentativas',
    max_retries INT DEFAULT 3 COMMENT 'Máximo de tentativas',
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
    FOREIGN KEY (transaction_id) REFERENCES saga_transactions(id) ON DELETE CASCADE,
    INDEX idx_transaction_id (transaction_id),
    INDEX idx_step_name (step_name),
    INDEX idx_step_order (step_order),
    INDEX idx_status (status),
    INDEX idx_service_name (service_name),
    INDEX idx_started_at (started_at),
    INDEX idx_transaction_order (transaction_id, step_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Passos das transações SAGA';

-- Criar tabela de eventos SAGA
CREATE TABLE IF NOT EXISTS saga_events (
    id VARCHAR(36) PRIMARY KEY COMMENT 'UUID do evento',
    saga_transaction_id VARCHAR(36) NOT NULL COMMENT 'ID da transação SAGA',
    step_id VARCHAR(36) COMMENT 'ID do passo (se aplicável)',
    -- Informações do evento
    event_type VARCHAR(100) NOT NULL COMMENT 'Tipo do evento',
    event_name VARCHAR(100) NOT NULL COMMENT 'Nome do evento',
    -- Dados do evento
    event_data JSON COMMENT 'Dados do evento',
    -- Origem do evento
    source_service VARCHAR(100) COMMENT 'Serviço de origem',
    correlation_id VARCHAR(100) COMMENT 'ID de correlação',
    -- Processamento
    processed BOOLEAN DEFAULT FALSE COMMENT 'Evento processado',
    processed_at TIMESTAMP NULL COMMENT 'Data do processamento',
    -- Timestamp
    occurred_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data do evento',
    FOREIGN KEY (transaction_id) REFERENCES saga_transactions(id) ON DELETE CASCADE,
    FOREIGN KEY (step_id) REFERENCES saga_steps(id) ON DELETE SET NULL,
    INDEX idx_transaction_id (transaction_id),
    INDEX idx_step_id (step_id),
    INDEX idx_event_type (event_type),
    INDEX idx_event_name (event_name),
    INDEX idx_processed (processed),
    INDEX idx_occurred_at (occurred_at),
    INDEX idx_correlation_id (correlation_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Eventos das transações SAGA';

