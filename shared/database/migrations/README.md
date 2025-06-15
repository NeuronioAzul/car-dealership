# 🗄️ Sistema de Migrations - Car Dealership

## 📋 Visão Geral

Este sistema de migrations cria toda a estrutura de banco de dados necessária para o funcionamento completo do sistema de concessionária de veículos.

## 🏗️ Estrutura de Bancos de Dados

### 1. **auth_db** - Serviço de Autenticação
- `users` - Usuários do sistema (clientes e administradores)
- `refresh_tokens` - Tokens JWT de refresh

### 2. **vehicle_db** - Catálogo de Veículos
- `vehicles` - Catálogo completo de veículos
- `vehicle_images` - Imagens dos veículos

### 3. **customer_db** - Perfis de Clientes
- `customer_profiles` - Perfis detalhados dos clientes
- `customer_addresses` - Endereços adicionais
- `customer_vehicle_preferences` - Preferências de veículos

### 4. **reservation_db** - Sistema de Reservas
- `reservations` - Reservas de veículos (24h)
- `payment_codes` - Códigos de pagamento
- `reservation_history` - Histórico de mudanças

### 5. **payment_db** - Processamento de Pagamentos
- `payments` - Pagamentos processados
- `gateway_transactions` - Transações do gateway

### 6. **sales_db** - Gestão de Vendas
- `sales` - Vendas realizadas
- `sale_documents` - Documentos gerados (PDFs)
- `sale_items` - Itens adicionais das vendas

### 7. **saga_db** - Orquestração SAGA
- `saga_transactions` - Transações distribuídas
- `saga_steps` - Passos das transações
- `saga_events` - Eventos das transações

### 8. **admin_db** - Painel Administrativo
- `system_settings` - Configurações do sistema
- `audit_logs` - Logs de auditoria
- `saved_reports` - Relatórios salvos
- `admin_notifications` - Notificações administrativas

## 🚀 Como Executar

### Execução Automática (Recomendado)
```bash
# Executar todas as migrations
php migrate.php --fresh
```

### Execução Manual por Serviço
```bash
# Auth Service
docker-compose exec -T mysql mysql -u root -prootpassword123 < migrations/auth/001_create_users_table.sql

# Vehicle Service
docker-compose exec -T mysql mysql -u root -prootpassword123 < migrations/vehicle/001_create_vehicles_table.sql

# E assim por diante...
```

## 📊 Dados Incluídos

### Usuários Padrão
- **Admin:** admin@concessionaria.com / admin123
- **Configurações:** Empresa, limites, taxas

### Veículos de Exemplo
- Toyota Corolla 2023 - R$ 85.000
- Honda Civic 2022 - R$ 75.000
- Volkswagen Jetta 2021 - R$ 68.000
- Ford Focus 2020 - R$ 55.000
- Chevrolet Cruze 2023 - R$ 78.000

### Configurações do Sistema
- Nome da empresa: "Concessionária Auto Ultra Max"
- Expiração de reserva: 24 horas
- Máximo de reservas por cliente: 3
- Taxa do gateway: 3.5%

## 🔧 Características Técnicas

### Índices Otimizados
- Consultas por status, datas, relacionamentos
- Busca de veículos por marca, modelo, preço
- Histórico de transações e auditoria

### Relacionamentos
- Soft delete em todas as entidades principais
- Timestamps automáticos

### Tipos de Dados
- UUIDs para chaves primárias
- JSON para dados flexíveis
- ENUMs para status controlados
- DECIMAL para valores monetários

## 📈 Monitoramento

### Verificação de Estrutura
```bash
# Verificar tabelas criadas
docker-compose exec mysql mysql -u root -prootpassword123 -e "
SELECT table_schema as 'Database', 
       COUNT(*) as 'Tables' 
FROM information_schema.tables 
WHERE table_schema LIKE '%_db' 
GROUP BY table_schema;"
```

#### Resposta esperada:

+----------------+--------+
| Database       | Tables |
+----------------+--------+
| auth_db        |      2 |
| vehicle_db     |      2 |
| customer_db    |      1 |
| reservation_db |      3 |
| payment_db     |      2 |
| sales_db       |      3 |
| saga_db        |      3 |
| admin_db       |      4 |
+----------------+--------+

### Verificação de Dados
```bash
# Verificar dados inseridos
docker-compose exec mysql mysql -u root -prootpassword123 -e "
SELECT 'users' as table_name, COUNT(*) as count FROM auth_db.users
UNION ALL
SELECT 'vehicles', COUNT(*) FROM vehicle_db.vehicles
UNION ALL
SELECT 'settings', COUNT(*) FROM admin_db.system_settings;"
```

#### Resposta esperada

+------------+-------+
| table_name | count |
+------------+-------+
| users      |     1 |
| vehicles   |     5 |
| settings   |    10 |
+------------+-------+

## 🛠️ Troubleshooting

### Problemas Comuns

1. **MySQL não responde**
```bash
docker-compose restart mysql
docker-compose logs mysql
```

2. **Permissões negadas**
```bash
# Verificar se containers estão rodando
docker-compose ps
```

3. **Migration falha**
```bash
# Executar migration específica
docker-compose exec -T mysql mysql -u root -prootpassword123 < migrations/auth/001_create_users_table.sql
```

### Reset Completo do banco de dados
```bash
# CUIDADO: Remove todos os dados
php migrate.php --fresh
```

## 📝 Logs

O script `migrate.php` fornece logs detalhados:
- ✅ Migrations executadas com sucesso
- ❌ Migrations que falharam
- 📊 Relatório final com contagem de tabelas
- 💡 Próximos passos sugeridos

## 🔄 Versionamento

As migrations seguem o padrão:
- `001_create_[entity]_table.sql` - Criação inicial
- `002_add_[feature]_to_[entity].sql` - Adições futuras
- `003_modify_[field]_in_[entity].sql` - Modificações

Para adicionar novas migrations, mantenha a numeração sequencial e atualize o script `migrate.php`.

