# Sistema de Concessionária de Veículos

**Versão:** 1.0.0
**Data:** 13 de junho de 2025
**Autor:** Mauro Rocha Tavares
**Tecnologias:** PHP 8.4, MySQL 8, RabbitMQ 3, Kong API Gateway, Docker

---

## Visão Geral

O Sistema de Concessionária de Veículos contém o básico para gestão de concessionárias.
Desenvolvi usando a arquitetura de microserviços e Clean Architecture. 
O sistema oferece a gestão de catálogo de veículos até processamento de vendas e geração automática de documentação.

### Principais características

🏗️ **Arquitetura de Microserviços**
- 8 microserviços independentes
- Clean Architecture em cada serviço
- Comunicação via REST APIs e mensageria
- Escalabilidade horizontal

🔐 **Segurança**
- Autenticação JWT com refresh tokens
- Controle de acesso baseado em roles (RBAC)
- Rate limiting e proteção contra abuso
- Validação de dados

🚗 **Funcionalidades Completas**
- Catálogo de veículos com busca
- Sistema de reservas com expiração automática
- Processamento de pagamentos
- Geração automática de documentos PDF
- Painel administrativo com relatórios

⚡ **Performance e Confiabilidade**
- Padrão SAGA Orquestrada para transações distribuídas
- Compensação automática em caso de falhas
- Testes
- Monitoramento e observabilidade

## Arquitetura do Sistema

### Microserviços Implementados

1. **Auth Service** (8081) - Autenticação e autorização
2. **Customer Service** (8082) - Gestão de clientes
3. **Vehicle Service** (8083) - Catálogo de veículos
4. **Reservation Service** (8084) - Sistema de reservas
5. **Payment Service** (8085) - Processamento de pagamentos
6. **Sales Service** (8086) - Gestão de vendas e documentos
7. **Admin Service** (8087) - Painel administrativo
8. **SAGA Orchestrator** - Coordenação de transações

### Infraestrutura

- **Kong API Gateway** (8000) - Ponto único de entrada
- **MySQL 8** - Bancos de dados separados por serviço
- **RabbitMQ 3** (15672) - Message broker para eventos
- **phpMyAdmin** (8090) - Interface de administração do banco
- **Swagger UI** (8089) - Documentação interativa da API

## Instalação e Configuração

### Pré-requisitos

- Sistema Linux Ubuntu ou wsl2 no windows ( testado e recomendado )
- Docker 20.10+
- Docker Compose 2.0+
- Git
- 8GB RAM disponível
- 20GB espaço em disco

### Instalação Rápida

```bash
# 1. Clone o repositório
git clone <repository-url>
cd car-dealership

# 2. Inicie todos os serviços
docker-compose up -d

# 3. Aguarde inicialização (2-3 minutos)
docker-compose logs -f

# 4. Execute migration do banco (necessário)
php shared/database/migration.php

# 5. Execute seeding do banco (recomendado)
php shared/database/seeding.php

# 6. Verifique se todos os serviços estão funcionando
curl http://localhost:8000/api/v1/auth/health
```

### Verificação da Instalação

Acesse os seguintes URLs para verificar se tudo está funcionando:

- **API Gateway:** http://localhost:8000/api/v1/auth/health
- **Documentação Swagger:** http://localhost:8089
- **phpMyAdmin:** http://localhost:8090 (root/rootpassword)
- **RabbitMQ Management:** http://localhost:15672 (guest/guest)

## Uso da API

### Autenticação

Todos os endpoints protegidos requerem autenticação JWT. Primeiro, registre um usuário e faça login:

```bash
# Registrar usuário
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "João Silva",
    "email": "joao@email.com",
    "password": "senha123",
    "cpf": "12345678901",
    "phone": "11999887766",
    "address": {
      "street": "Rua das Flores, 123",
      "city": "São Paulo",
      "state": "SP",
      "zip_code": "01234-567"
    }
  }'

# Fazer login
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "joao@email.com",
    "password": "senha123"
  }'
```

### Fluxo de Compra Completo

```bash
# 1. Buscar veículos disponíveis
curl -X GET "http://localhost:8000/api/v1/vehicles?status=available&limit=5"

# 2. Criar reserva (com token de autenticação)
curl -X POST http://localhost:8000/api/v1/reservations \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"vehicle_id": "VEHICLE_ID"}'

# 3. Gerar código de pagamento
curl -X POST http://localhost:8000/api/v1/reservations/generate-payment-code \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"reservation_id": "RESERVATION_ID"}'

# 4. Processar pagamento
curl -X POST http://localhost:8000/api/v1/payments \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "payment_code": "PAYMENT_CODE",
    "payment_method": "credit_card",
    "card_data": {
      "number": "4111111111111111",
      "holder_name": "JOAO SILVA",
      "expiry_month": "12",
      "expiry_year": "2025",
      "cvv": "123"
    }
  }'

# 5. Verificar venda criada
curl -X GET http://localhost:8000/api/v1/sales \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
```

### Usando SAGA Orchestrator

Para transações mais complexas, use o SAGA Orchestrator:

```bash
curl -X POST http://localhost:8000/api/v1/saga/purchase \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "vehicle_id": "VEHICLE_ID",
    "customer_data": {
      "name": "João Silva",
      "cpf": "12345678901",
      "email": "joao@email.com",
      "phone": "11999887766",
      "address": "Rua das Flores, 123 - São Paulo/SP"
    },
    "payment_data": {
      "method": "credit_card",
      "card_data": {
        "number": "4111111111111111",
        "holder_name": "JOAO SILVA",
        "expiry_month": "12",
        "expiry_year": "2025",
        "cvv": "123"
      }
    }
  }'
```

## Testes

### Executar Todos os Testes

```bash
cd tests/
./run_tests.sh
```

### Testes Específicos

```bash
# Apenas testes unitários
./run_tests.sh --unit

# Apenas testes de integração
./run_tests.sh --integration

# Testes com relatório de cobertura
./run_tests.sh --coverage
```

### Estrutura de Testes

- **Unit Tests:** Testam entidades e serviços isoladamente
- **Integration Tests:** Testam comunicação entre serviços
- **Feature Tests:** Testam fluxos completos end-to-end

## Painel Administrativo

### Acesso ao Dashboard

Para acessar funcionalidades administrativas, registre um usuário admin:

```bash
# Registrar admin (via seeding ou manualmente no banco)
# Depois fazer login normalmente

# Acessar dashboard
curl -X GET http://localhost:8000/api/v1/admin/dashboard \
  -H "Authorization: Bearer ADMIN_ACCESS_TOKEN"
```

### Relatórios Disponíveis

- **Vendas:** Análise detalhada com filtros por período
- **Clientes:** Perfil de clientes e histórico de compras
- **Veículos:** Status do estoque e análise por marca
- **Performance:** Métricas de conversão e performance

## Monitoramento

### Health Checks

Todos os serviços possuem endpoints de health check:

```bash
# Verificar saúde de todos os serviços
curl http://localhost:8000/api/v1/auth/health
curl http://localhost:8000/api/v1/vehicles/health
curl http://localhost:8000/api/v1/reservations/health
# ... outros serviços
```

### Logs

```bash
# Ver logs de todos os serviços
docker-compose logs -f

# Ver logs de serviço específico
docker-compose logs -f auth-service
```

### Métricas

- **RabbitMQ Management:** http://localhost:15672
- **phpMyAdmin:** http://localhost:8090
- **Logs estruturados** em JSON para integração com ferramentas de monitoramento

## Configuração de Produção

### Variáveis de Ambiente

Crie arquivo `.env.production` com:

```bash
APP_ENV=production
JWT_SECRET=your-super-secret-jwt-key-for-auth-service-2025
DB_PASSWORD=secure-database-password
RABBITMQ_PASSWORD=secure-rabbitmq-password
KONG_ADMIN_TOKEN=secure-kong-admin-token
```

### Deploy de Produção

```bash
# Build para produção
docker-compose -f docker-compose.prod.yml build

# Deploy
docker-compose -f docker-compose.prod.yml up -d

# Verificar saúde
curl https://your-domain.com/api/v1/auth/health
```

### Backup

```bash
# Backup do banco de dados
docker exec mysql mysqldump -u root -p --all-databases > backup.sql

# Backup de volumes
docker run --rm -v car-dealership_mysql_data:/data -v $(pwd):/backup alpine tar czf /backup/mysql_backup.tar.gz /data
```

## Solução de Problemas

### Problemas Comuns

**Serviços não inicializam:**
```bash
# Verificar logs
docker-compose logs

# Reiniciar serviços
docker-compose restart

# Rebuild se necessário
docker-compose build --no-cache
```

**Erro de conexão com banco:**
```bash
# Verificar se MySQL está rodando
docker-compose ps mysql

# Verificar logs do MySQL
docker-compose logs mysql

# Aguardar inicialização completa
sleep 60
```

**Problemas de autenticação:**
```bash
# Verificar se JWT_SECRET está configurado
docker-compose exec auth-service env | grep JWT

# Verificar logs do Auth Service
docker-compose logs auth-service
```

### Logs Detalhados

```bash
# Habilitar debug
export APP_DEBUG=true
docker-compose restart

# Ver logs em tempo real
docker-compose logs -f --tail=100
```

## Documentação Adicional

- **[Guia de Instalação Detalhado](docs/INSTALLATION_GUIDE.md)**
- **[Manual de Uso da API](docs/API_USER_GUIDE.md)**
- **[Documentação Técnica Completa](docs/TECHNICAL_DOCUMENTATION.md)**
- **[Documentação da API](docs/API_DOCUMENTATION.md)**
- **[Especificação OpenAPI](docs/openapi.yml)**

## Suporte e Contribuição

### Reportar Problemas

Para reportar bugs ou solicitar funcionalidades:

1. Verifique se o problema já foi reportado
2. Inclua logs relevantes
3. Descreva passos para reproduzir
4. Inclua informações do ambiente

### Desenvolvimento

```bash
# Configurar ambiente de desenvolvimento
git clone <repository-url>
cd car-dealership

# Instalar dependências de teste
cd tests/
composer install

# Executar testes antes de contribuir
./run_tests.sh
```

### Estrutura do Projeto

```
car-dealership/
├── auth-service/           # Microserviço de autenticação
├── customer-service/       # Microserviço de clientes
├── vehicle-service/        # Microserviço de veículos
├── reservation-service/    # Microserviço de reservas
├── payment-service/        # Microserviço de pagamentos
├── sales-service/          # Microserviço de vendas
├── admin-service/          # Microserviço administrativo
├── saga-orchestrator/      # Orquestrador SAGA
├── api-gateway/           # Configuração do Kong
├── shared/                # Recursos compartilhados
├── tests/                 # Testes do sistema
├── docs/                  # Documentação
└── docker-compose.yml     # Orquestração Docker
```

## Licença

Este projeto é desenvolvido para Fase final da Pós Graduação FIAP de Software Archtecture.

---

**Versão:** 1.0.0  
**Última atualização:** 13 de junho de 2025  
**Contato:** Mauro Rocha Tavares

