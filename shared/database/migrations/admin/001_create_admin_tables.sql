-- Migration: Admin Service - Admin Tables
-- Database: admin_db
-- Description: Tabelas para painel administrativo

USE admin_db;

-- Criar tabela de configurações do sistema
CREATE TABLE IF NOT EXISTS system_settings (
    id VARCHAR(36) PRIMARY KEY COMMENT 'UUID da configuração',
    setting_key VARCHAR(100) UNIQUE NOT NULL COMMENT 'Chave da configuração',
    setting_value TEXT COMMENT 'Valor da configuração',
    setting_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string' COMMENT 'Tipo do valor',
    category VARCHAR(50) COMMENT 'Categoria da configuração',
    description TEXT COMMENT 'Descrição da configuração',
    
    -- Controle
    is_public BOOLEAN DEFAULT FALSE COMMENT 'Configuração pública',
    is_editable BOOLEAN DEFAULT TRUE COMMENT 'Configuração editável',
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
    
    INDEX idx_setting_key (setting_key),
    INDEX idx_category (category),
    INDEX idx_is_public (is_public)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Configurações do sistema';

-- Criar tabela de logs de auditoria
CREATE TABLE IF NOT EXISTS audit_logs (
    id VARCHAR(36) PRIMARY KEY COMMENT 'UUID do log',
    
    -- Informações da ação
    action VARCHAR(100) NOT NULL COMMENT 'Ação realizada',
    entity_type VARCHAR(100) COMMENT 'Tipo da entidade',
    entity_id VARCHAR(36) COMMENT 'ID da entidade',
    
    -- Usuário que realizou a ação
    user_id VARCHAR(36) COMMENT 'ID do usuário',
    user_name VARCHAR(255) COMMENT 'Nome do usuário',
    user_email VARCHAR(255) COMMENT 'Email do usuário',
    user_role VARCHAR(50) COMMENT 'Role do usuário',
    
    -- Dados da ação
    old_values JSON COMMENT 'Valores anteriores',
    new_values JSON COMMENT 'Novos valores',
    changes JSON COMMENT 'Mudanças realizadas',
    
    -- Contexto
    ip_address VARCHAR(45) COMMENT 'Endereço IP',
    user_agent TEXT COMMENT 'User Agent',
    request_id VARCHAR(100) COMMENT 'ID da requisição',
    
    -- Timestamp
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data da ação',
    
    INDEX idx_action (action),
    INDEX idx_entity_type (entity_type),
    INDEX idx_entity_id (entity_id),
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at),
    INDEX idx_user_action (user_id, action),
    INDEX idx_entity_action (entity_type, action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Logs de auditoria do sistema';

-- Criar tabela de relatórios salvos
CREATE TABLE IF NOT EXISTS saved_reports (
    id VARCHAR(36) PRIMARY KEY COMMENT 'UUID do relatório',
    
    -- Informações do relatório
    report_name VARCHAR(255) NOT NULL COMMENT 'Nome do relatório',
    report_type VARCHAR(100) NOT NULL COMMENT 'Tipo do relatório',
    description TEXT COMMENT 'Descrição do relatório',
    
    -- Configuração do relatório
    filters JSON COMMENT 'Filtros aplicados',
    columns JSON COMMENT 'Colunas selecionadas',
    sort_config JSON COMMENT 'Configuração de ordenação',
    
    -- Criador do relatório
    created_by VARCHAR(36) NOT NULL COMMENT 'ID do usuário criador',
    creator_name VARCHAR(255) COMMENT 'Nome do criador',
    
    -- Controle de acesso
    is_public BOOLEAN DEFAULT FALSE COMMENT 'Relatório público',
    shared_with JSON COMMENT 'Usuários com acesso',
    
    -- Agendamento
    is_scheduled BOOLEAN DEFAULT FALSE COMMENT 'Relatório agendado',
    schedule_config JSON COMMENT 'Configuração do agendamento',
    last_generated_at TIMESTAMP NULL COMMENT 'Última geração',
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
    
    INDEX idx_report_type (report_type),
    INDEX idx_created_by (created_by),
    INDEX idx_is_public (is_public),
    INDEX idx_is_scheduled (is_scheduled),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Relatórios salvos';

-- Criar tabela de notificações administrativas
CREATE TABLE IF NOT EXISTS admin_notifications (
    id VARCHAR(36) PRIMARY KEY COMMENT 'UUID da notificação',
    
    -- Conteúdo da notificação
    title VARCHAR(255) NOT NULL COMMENT 'Título da notificação',
    message TEXT NOT NULL COMMENT 'Mensagem da notificação',
    notification_type ENUM('info', 'warning', 'error', 'success') DEFAULT 'info' COMMENT 'Tipo da notificação',
    
    -- Destinatários
    target_users JSON COMMENT 'Usuários específicos (IDs)',
    target_roles JSON COMMENT 'Roles específicas',
    is_global BOOLEAN DEFAULT FALSE COMMENT 'Notificação global',
    
    -- Controle
    is_read BOOLEAN DEFAULT FALSE COMMENT 'Notificação lida',
    read_by JSON COMMENT 'Usuários que leram',
    
    -- Ação relacionada
    action_url VARCHAR(500) COMMENT 'URL da ação',
    action_label VARCHAR(100) COMMENT 'Label da ação',
    
    -- Expiração
    expires_at TIMESTAMP NULL COMMENT 'Data de expiração',
    
    -- Criador
    created_by VARCHAR(36) COMMENT 'ID do criador',
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualização',
    
    INDEX idx_notification_type (notification_type),
    INDEX idx_is_global (is_global),
    INDEX idx_is_read (is_read),
    INDEX idx_expires_at (expires_at),
    INDEX idx_created_by (created_by),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Notificações administrativas';

-- Inserir configurações padrão do sistema
INSERT IGNORE INTO system_settings (id, setting_key, setting_value, setting_type, category, description, is_public) VALUES
(UUID(), 'company_name', 'Concessionária AutoMax', 'string', 'company', 'Nome da empresa', TRUE),
(UUID(), 'company_cnpj', '12.345.678/0001-90', 'string', 'company', 'CNPJ da empresa', TRUE),
(UUID(), 'company_address', 'Rua das Concessionárias, 123 - São Paulo/SP', 'string', 'company', 'Endereço da empresa', TRUE),
(UUID(), 'company_phone', '(11) 3000-0000', 'string', 'company', 'Telefone da empresa', TRUE),
(UUID(), 'company_email', 'contato@automax.com.br', 'string', 'company', 'Email da empresa', TRUE),
(UUID(), 'reservation_expiry_hours', '24', 'number', 'business', 'Horas para expiração da reserva', FALSE),
(UUID(), 'max_reservations_per_customer', '3', 'number', 'business', 'Máximo de reservas por cliente', FALSE),
(UUID(), 'payment_gateway_fee', '3.5', 'number', 'payment', 'Taxa do gateway de pagamento (%)', FALSE),
(UUID(), 'enable_notifications', 'true', 'boolean', 'system', 'Habilitar notificações', FALSE),
(UUID(), 'maintenance_mode', 'false', 'boolean', 'system', 'Modo de manutenção', FALSE);

