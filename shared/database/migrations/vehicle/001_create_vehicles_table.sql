-- Migration: Vehicle Service - Vehicles Table
-- Database: vehicle_db
-- Description: Tabela de veículos para o catálogo

USE vehicle_db;

-- Criar tabela de veículos
CREATE TABLE IF NOT EXISTS vehicles (
    id VARCHAR(36) PRIMARY KEY COMMENT 'UUID do veículo',
    brand VARCHAR(100) NOT NULL COMMENT 'Marca do veículo',
    model VARCHAR(100) NOT NULL COMMENT 'Modelo do veículo',
    manufacturing_year INT NOT NULL COMMENT 'Ano de fabricação',
    model_year INT NOT NULL COMMENT 'Ano do modelo',
    color VARCHAR(50) NOT NULL COMMENT 'Cor do veículo',
    mileage INT DEFAULT 0 COMMENT 'Quilometragem',
    fuel_type ENUM('Gasolina', 'Etanol', 'Flex', 'Diesel', 'Híbrido', 'Elétrico') NOT NULL COMMENT 'Tipo de combustível',
    transmission_type ENUM('Manual', 'Automático', 'CVT') NOT NULL COMMENT 'Tipo de transmissão',
    price DECIMAL(10,2) NOT NULL COMMENT 'Preço do veículo',
    status ENUM('available', 'reserved', 'sold') DEFAULT 'available' COMMENT 'Status do veículo',
    description TEXT COMMENT 'Descrição detalhada do veículo',
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
    license_plate VARCHAR(10) UNIQUE COMMENT 'Placa do veículo',
    chassis_number VARCHAR(50) UNIQUE COMMENT 'Número do chassi',
    renavam VARCHAR(20) COMMENT 'Código RENAVAM',
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
    deleted_at TIMESTAMP NULL COMMENT 'Data de exclusão (soft delete)',
    
    -- Índices
    INDEX idx_brand (brand),
    INDEX idx_model (model),
    INDEX idx_year (manufacturing_year, model_year),
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
    
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
    INDEX idx_vehicle_id (vehicle_id),
    INDEX idx_image_type (image_type),
    INDEX idx_display_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Imagens dos veículos';

-- Inserir veículos de exemplo
INSERT IGNORE INTO vehicles (
    id, brand, model, manufacturing_year, model_year, color, mileage, 
    fuel_type, transmission_type, price, status, description, features,
    engine_size, doors, seats, license_plate, chassis_number
) VALUES 
(UUID(), 'Toyota', 'Corolla', 2023, 2023, 'Branco', 15000, 'Flex', 'Automático', 85000.00, 'available', 
 'Toyota Corolla 2023 em excelente estado de conservação, único dono, revisões em dia.',
 '["Ar condicionado", "Direção hidráulica", "Vidros elétricos", "Trava elétrica", "Som automotivo", "Airbag duplo"]',
 '2.0', 4, 5, 'ABC1234', '9BWZZZ377VT004251'),

(UUID(), 'Honda', 'Civic', 2022, 2022, 'Preto', 25000, 'Flex', 'Manual', 75000.00, 'available',
 'Honda Civic 2022 muito conservado, pneus novos, bateria nova.',
 '["Ar condicionado", "Som automotivo", "Trava elétrica", "Alarme", "Rodas de liga leve"]',
 '2.0', 4, 5, 'DEF5678', '9BWZZZ377VT004252'),

(UUID(), 'Volkswagen', 'Jetta', 2021, 2021, 'Prata', 35000, 'Flex', 'Automático', 68000.00, 'available',
 'Volkswagen Jetta 2021 completo, central multimídia, câmera de ré.',
 '["Ar condicionado", "GPS", "Câmera de ré", "Sensor de estacionamento", "Bancos de couro"]',
 '2.0', 4, 5, 'GHI9012', '9BWZZZ377VT004253'),

(UUID(), 'Ford', 'Focus', 2020, 2020, 'Azul', 45000, 'Flex', 'Manual', 55000.00, 'available',
 'Ford Focus 2020 econômico, ideal para o dia a dia.',
 '["Ar condicionado", "Direção hidráulica", "Vidros elétricos", "Som MP3"]',
 '2.0', 4, 5, 'JKL3456', '9BWZZZ377VT004254'),

(UUID(), 'Chevrolet', 'Cruze', 2023, 2023, 'Vermelho', 8000, 'Flex', 'Automático', 78000.00, 'available',
 'Chevrolet Cruze 2023 seminovo, garantia de fábrica.',
 '["Ar condicionado", "Multimídia", "Sensor de estacionamento", "Controle de cruzeiro", "Faróis de LED"]',
 '1.4 Turbo', 4, 5, 'MNO7890', '9BWZZZ377VT004255');

