-- Migration: Vehicle Service - Vehicles Table
-- Database: vehicle_db
-- Description: Tabela de veículos para o catálogo

USE vehicle_db;

-- Criar tabela de veículos
CREATE TABLE IF NOT EXISTS vehicles (
    id VARCHAR(36) PRIMARY KEY COMMENT 'UUID do veículo',
    brand VARCHAR(100) NOT NULL COMMENT 'Marca do veículo',
    model VARCHAR(100) NOT NULL COMMENT 'Modelo do veículo',
    year INT NOT NULL COMMENT 'Ano do veículo',
    color VARCHAR(50) NOT NULL COMMENT 'Cor do veículo',
    fuel_type ENUM('Gasolina', 'Etanol', 'Flex', 'Diesel', 'Hibrido', 'Eletrico') NOT NULL COMMENT 'Tipo de combustível',
    transmission_type ENUM('Manual', 'Automatico', 'CVT') NOT NULL COMMENT 'Tipo de transmissão',
    mileage INT DEFAULT 0 COMMENT 'Quilometragem',
    price DECIMAL(10,2) NOT NULL COMMENT 'Preço do veículo',
    description TEXT COMMENT 'Descrição detalhada do veículo',

    status ENUM('available', 'reserved', 'sold') DEFAULT 'available' COMMENT 'Status do veículo',
    features JSON COMMENT 'Lista de características/opcionais',
    
    -- Informações técnicas
    engine_size VARCHAR(20) COMMENT 'Cilindrada do motor',
    doors INT COMMENT 'Número de portas',
    seats INT COMMENT 'Número de assentos',
    trunk_capacity INT COMMENT 'Capacidade do porta-malas (litros)',
    
    -- Informações comerciais
    purchase_price DECIMAL(10,2) COMMENT 'Preço de compra (interno)',
    profit_margin DECIMAL(5,2) COMMENT 'Margem de lucro (%)',
    supplier VARCHAR(255) COMMENT 'Fornecedor/origem',
    
    -- Documentação
    chassis_number VARCHAR(50) UNIQUE COMMENT 'Número do chassi',
    license_plate VARCHAR(10) UNIQUE COMMENT 'Placa do veículo',
    renavam VARCHAR(20) COMMENT 'Código RENAVAM',
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
    deleted_at TIMESTAMP NULL COMMENT 'Data de exclusão (soft delete)',
    
    -- Índices
    INDEX idx_brand (brand),
    INDEX idx_model (model),
    INDEX idx_price (price),
    INDEX idx_status (status),
    INDEX idx_fuel_type (fuel_type),
    INDEX idx_transmission (transmission_type),
    INDEX idx_created_at (created_at),
    INDEX idx_deleted_at (deleted_at),
    INDEX idx_brand_model (brand, model),
    INDEX idx_price_range (price, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabela de veículos do catálogo';

-- Criar tabela de imagens dos veículos
CREATE TABLE IF NOT EXISTS vehicle_images (
    id VARCHAR(36) PRIMARY KEY COMMENT 'UUID da imagem',
    vehicle_id VARCHAR(36) NOT NULL COMMENT 'ID do veículo',
    image_url VARCHAR(500) NOT NULL COMMENT 'URL da imagem',
    image_type ENUM('main', 'gallery', 'interior', 'exterior', 'engine') DEFAULT 'gallery' COMMENT 'Tipo da imagem',
    display_order INT DEFAULT 0 COMMENT 'Ordem de exibição',
    alt_text VARCHAR(255) COMMENT 'Texto alternativo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
    deleted_at TIMESTAMP NULL COMMENT 'Data de exclusão (soft delete)',
    
    INDEX idx_vehicle_id (vehicle_id),
    INDEX idx_image_type (image_type),
    INDEX idx_display_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Imagens dos veículos';

