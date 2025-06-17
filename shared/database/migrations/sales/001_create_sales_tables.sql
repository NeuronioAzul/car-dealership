-- Migration: Sales Service - Sales Table
-- Database: sales_db
-- Description: Sistema de vendas e documentos

USE sales_db;

-- Criar tabela de vendas
CREATE TABLE IF NOT EXISTS sales (
    id VARCHAR(36) PRIMARY KEY COMMENT 'UUID da venda',
    sale_number VARCHAR(20) UNIQUE NOT NULL COMMENT 'Número único da venda',
    
    -- Relacionamentos
    customer_id VARCHAR(36) NOT NULL COMMENT 'ID do cliente',
    vehicle_id VARCHAR(36) NOT NULL COMMENT 'ID do veículo',
    reservation_id VARCHAR(36) COMMENT 'ID da reserva relacionada',
    payment_id VARCHAR(36) COMMENT 'ID do pagamento relacionado',
    
    -- Informações da venda
    sale_price DECIMAL(10,2) NOT NULL COMMENT 'Preço final de venda',
    discount_amount DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Valor do desconto',
    tax_amount DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Valor dos impostos',
    total_amount DECIMAL(10,2) NOT NULL COMMENT 'Valor total',
    
    -- Status da venda
    status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending' COMMENT 'Status da venda',
    
    -- Datas importantes
    sale_date DATE NOT NULL COMMENT 'Data da venda',
    delivery_date DATE COMMENT 'Data de entrega',
    contract_signed_at TIMESTAMP NULL COMMENT 'Data da assinatura do contrato',
    
    -- Observações
    notes TEXT COMMENT 'Observações da venda',
    terms_conditions TEXT COMMENT 'Termos e condições específicos',
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
    
    -- Índices
    INDEX idx_sale_number (sale_number),
    INDEX idx_customer_id (customer_id),
    INDEX idx_vehicle_id (vehicle_id),
    INDEX idx_reservation_id (reservation_id),
    INDEX idx_payment_id (payment_id),
    INDEX idx_status (status),
    INDEX idx_sale_date (sale_date),
    INDEX idx_created_at (created_at),
    INDEX idx_customer_status (customer_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Vendas realizadas';

-- Criar tabela de documentos da venda
CREATE TABLE IF NOT EXISTS sale_documents (
    id VARCHAR(36) PRIMARY KEY COMMENT 'UUID do documento',
    sale_id VARCHAR(36) NOT NULL COMMENT 'ID da venda',
    
    -- Informações do documento
    document_type ENUM('contract', 'invoice', 'receipt', 'warranty', 'manual', 'other') NOT NULL COMMENT 'Tipo do documento',
    document_name VARCHAR(255) NOT NULL COMMENT 'Nome do documento',
    file_path VARCHAR(500) NOT NULL COMMENT 'Caminho do arquivo',
    file_size INT COMMENT 'Tamanho do arquivo em bytes',
    mime_type VARCHAR(100) COMMENT 'Tipo MIME do arquivo',
    
    -- Timestamps
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de geração',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
    deleted_at TIMESTAMP NULL COMMENT 'Data de exclusão (soft delete)',
    
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    INDEX idx_sale_id (sale_id),
    INDEX idx_document_type (document_type),
    INDEX idx_generated_at (generated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Documentos das vendas';

-- Criar tabela de itens adicionais da venda
CREATE TABLE IF NOT EXISTS sale_items (
    id VARCHAR(36) PRIMARY KEY COMMENT 'UUID do item',
    sale_id VARCHAR(36) NOT NULL COMMENT 'ID da venda',
    
    -- Informações do item
    item_type ENUM('service', 'accessory', 'insurance', 'warranty', 'fee', 'other') NOT NULL COMMENT 'Tipo do item',
    item_name VARCHAR(255) NOT NULL COMMENT 'Nome do item',
    item_description TEXT COMMENT 'Descrição do item',
    
    -- Valores
    quantity INT DEFAULT 1 COMMENT 'Quantidade',
    unit_price DECIMAL(10,2) NOT NULL COMMENT 'Preço unitário',
    total_price DECIMAL(10,2) NOT NULL COMMENT 'Preço total',
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
    deleted_at TIMESTAMP NULL COMMENT 'Data de exclusão (soft delete)',
    
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    INDEX idx_sale_id (sale_id),
    INDEX idx_item_type (item_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Itens adicionais das vendas';

