-- Migration: Admin Service - Admin Tables
-- Database: admin_db
-- Description: Tabelas para painel administrativo

USE admin_db;

-- Inserir configurações padrão do sistema
INSERT IGNORE INTO system_settings (id, setting_key, setting_value, setting_type, category, description, is_public) VALUES
(UUID(), 'company_name', 'Concessionária M&D Ultra Max', 'string', 'company', 'Nome da empresa', TRUE),
(UUID(), 'company_cnpj', '12.345.678/0001-90', 'string', 'company', 'CNPJ da empresa', TRUE),
(UUID(), 'company_address', 'Rua das Concessionárias, 123 - São Paulo/SP', 'string', 'company', 'Endereço da empresa', TRUE),
(UUID(), 'company_phone', '(11) 3000-0000', 'string', 'company', 'Telefone da empresa', TRUE),
(UUID(), 'company_email', 'contato@mdultramax.com.br', 'string', 'company', 'Email da empresa', TRUE),
(UUID(), 'reservation_expiry_hours', '24', 'number', 'business', 'Horas para expiração da reserva', FALSE),
(UUID(), 'max_reservations_per_customer', '3', 'number', 'business', 'Máximo de reservas por cliente', FALSE),
(UUID(), 'payment_gateway_fee', '3.5', 'number', 'payment', 'Taxa do gateway de pagamento (%)', FALSE),
(UUID(), 'enable_notifications', 'true', 'boolean', 'system', 'Habilitar notificações', FALSE),
(UUID(), 'maintenance_mode', 'false', 'boolean', 'system', 'Modo de manutenção', FALSE);

