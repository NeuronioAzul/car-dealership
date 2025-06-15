-- Criação dos bancos de dados para cada microserviço
CREATE DATABASE IF NOT EXISTS auth_db;
CREATE DATABASE IF NOT EXISTS customer_db;
CREATE DATABASE IF NOT EXISTS vehicle_db;
CREATE DATABASE IF NOT EXISTS reservation_db;
CREATE DATABASE IF NOT EXISTS payment_db;
CREATE DATABASE IF NOT EXISTS sales_db;
CREATE DATABASE IF NOT EXISTS admin_db;
CREATE DATABASE IF NOT EXISTS saga_db;
CREATE DATABASE IF NOT EXISTS shared_db;
CREATE DATABASE IF NOT EXISTS logs_db;

-- Usuários específicos para cada serviço (opcional, usando root por simplicidade)
-- CREATE USER 'auth_user'@'%' IDENTIFIED BY 'auth_pass';
-- GRANT ALL PRIVILEGES ON auth_db.* TO 'auth_user'@'%';

-- CREATE USER 'customer_user'@'%' IDENTIFIED BY 'customer_pass';
-- GRANT ALL PRIVILEGES ON customer_db.* TO 'customer_user'@'%';

-- CREATE USER 'vehicle_user'@'%' IDENTIFIED BY 'vehicle_pass';
-- GRANT ALL PRIVILEGES ON vehicle_db.* TO 'vehicle_user'@'%';

-- CREATE USER 'reservation_user'@'%' IDENTIFIED BY 'reservation_pass';
-- GRANT ALL PRIVILEGES ON reservation_db.* TO 'reservation_user'@'%';

-- CREATE USER 'payment_user'@'%' IDENTIFIED BY 'payment_pass';
-- GRANT ALL PRIVILEGES ON payment_db.* TO 'payment_user'@'%';

-- CREATE USER 'sales_user'@'%' IDENTIFIED BY 'sales_pass';
-- GRANT ALL PRIVILEGES ON sales_db.* TO 'sales_user'@'%';

-- CREATE USER 'admin_user'@'%' IDENTIFIED BY 'admin_pass';
-- GRANT ALL PRIVILEGES ON admin_db.* TO 'admin_user'@'%';

-- CREATE USER 'saga_user'@'%' IDENTIFIED BY 'saga_pass';
-- GRANT ALL PRIVILEGES ON saga_db.* TO 'saga_user'@'%';

FLUSH PRIVILEGES;

