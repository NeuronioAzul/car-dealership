-- Migration: Auth Service - Cleanup Expired Token Blacklist Table
-- Database: auth_db
-- Description: Evento para limpeza automática de tokens expirados na blacklist

USE auth_db;

-- Nota: Certifique-se de que o evento esteja habilitado no servidor MySQL
-- Para habilitar eventos, execute: SET GLOBAL event_scheduler = ON;

SET GLOBAL event_scheduler = ON;

DELIMITER //
CREATE EVENT IF NOT EXISTS cleanup_expired_tokens
ON SCHEDULE EVERY 1 HOUR
DO
BEGIN
    DELETE FROM token_blacklist WHERE expires_at <= NOW();
END //
DELIMITER ;


-- Verifique se o evento foi criado corretamente
-- SHOW EVENTS LIKE 'cleanup_expired_tokens';