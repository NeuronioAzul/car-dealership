# Cardealership - Sistema de Concessionária de Veículos

**Versão:** 1.0.0
**Autor:** Mauro Rocha Tavares

## Tecnologias Utilizadas

### Controle de Versão e Automação

- **Git:** Controle de versionamento de código-fonte.
- **Makefile:** Automação de tarefas comuns do projeto.

### Backend e Frameworks

- **Laravel 11:** Framework PHP moderno para desenvolvimento web.
- **PHP 8.4 (PSR-12):** Linguagem principal seguindo padrão de codificação PSR-12.
- **PHPUnit:** Framework para testes automatizados em PHP.
- **PHP CS Fixer:** Ferramenta para padronização e formatação automática do código.

### Arquitetura e Padrões

- **Clean Architecture:** Organização do código em camadas independentes para maior manutenibilidade.
- **Orquestração SAGA:** Gerenciamento de transações distribuídas entre microserviços.

### Banco de Dados e Mensageria

- **MySQL 8:** Banco de dados relacional para persistência de dados.
- **RabbitMQ 3:** Message broker para comunicação assíncrona entre serviços.

### APIs e Documentação

- **Swagger/OpenAPI:** Documentação interativa e padronizada das APIs.
- **Documentação OpenAPI:** Especificação formal dos endpoints da API.

### Segurança

- **Autenticação JWT:** Autenticação baseada em tokens seguros e stateless.

### Infraestrutura e Deploy

- **Docker:** Contêinerização dos serviços para ambientes isolados.
- **Docker Compose:** Orquestração de múltiplos contêineres Docker.
- **Dockerfile:** Definição de imagens customizadas para cada serviço.

### API Gateway e Gerenciamento

- **Kong API Gateway (Community):** Gateway para roteamento, autenticação e rate limiting das APIs.
- **Kong Deck:** Ferramenta para gerenciar configurações do Kong via código ([documentação](https://github.com/kong/deck/?tab=readme-ov-file#documentation)).

---

## Visão Geral

O Cardealership - Sistema de Concessionária de Veículos contém o básico para gestão de concessionárias.
Desenvolvido usando a arquitetura de microserviços e Clean Architecture.
O sistema oferece a gestão de catálogo de veículos até processamento de vendas e geração automática de documentação.

### Principais características

🏗️ **Arquitetura de Microserviços**

- 8 microserviços independentes
- Clean Architecture em cada serviço
- Comunicação via REST APIs e mensageria
- Escalabilidade horizontal

🔐 **Segurança**

- Autenticação JWT com refresh tokens
- Controle de acesso baseado em roles (RBAC) Role Based Access Control
- Proteção contra CSRF e XSS
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
8. **SAGA Orchestrator** (8088) - Coordenação de transações

### Infraestrutura

- **Swagger UI** (8089) - Documentação interativa da API
- **phpMyAdmin** (8090) - Interface de administração do banco
- **Kong API Gateway** (8000) - Ponto único de entrada
- **RabbitMQ 3** (15672) - Message broker para eventos
- **MySQL 8** - Bancos de dados separados por serviço
- **Makefile** - Automação de tarefas

## Instalação e Configuração

### Pré-requisitos

- Sistema Linux Ubuntu ou wsl2 no windows ( testado )
- Docker
- Docker Compose
- Git
- 4GB RAM disponível
- 20GB espaço em disco

### Instalação Rápida

```bash
# 1. Clone o repositório
git clone https://github.com/NeuronioAzul/car-dealership.git
cd car-dealership

# 2. Use o Makefile para instalar e configurar o ambiente
make setup
```

### Usando o Docker Compose

```bash
# 1. Clone o repositório
git clone https://github.com/NeuronioAzul/car-dealership.git
cd car-dealership

# 2. Certifique-se de que o Docker e o Docker Compose estão instalados
docker --version
docker-compose --version

# 3. Inicie todos os serviços
COMPOSE_BAKE=true docker-compose build --pull --no-cache

# 4. Aguarde inicialização completa
docker-compose logs -f

# 5. Execute migration do banco (necessário)
php shared/database/migrate.php

# 6. Execute seeding do banco (recomendado)
php shared/database/seed.php

# 7. Verifique se todos os serviços estão funcionando
curl http://localhost:8000/api/v1/auth/health
```

A resposta do health check deve ser parecida com:

```json
{
  "success": true,
  "service": "auth-service",
  "status": "healthy",
  "timestamp": "2025-07-12 21:23:21"
}
```

### Verificação da Instalação

Acesse os seguintes URLs para verificar se tudo está funcionando:

- **API Gateway:** <http://localhost:8000/api/v1/auth/health>
- **Documentação Swagger:** <http://localhost:8089>
- **phpMyAdmin:** <http://localhost:8090> (root/rootpassword123)
- **RabbitMQ Management:** <http://localhost:15672> (admin/admin123)

## Uso da API

### Autenticação

Todos os endpoints protegidos requerem autenticação JWT. Primeiro, registre um usuário e faça login:

```bash
# Registrar usuário
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
  "name": "Maria Silva",
  "email": "maria@email.com",
  "password": "senha123",
  "phone": "11999999999",
  "birth_date": "1981-05-28",
  "role": "customer",
  "address": {
    "street": "Rua xyz",
    "number": "28",
    "neighborhood": "Cesamo",
    "city": "string",
    "state": "SP",
    "zip_code": "03618-010"
  },
  "accept_terms": true,
  "accept_privacy": true,
  "accept_communications": true
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

### Executar Testes nos Microsserviços

```bash
cd <serviço>/
./vendor/bin/phpunit
```

### Testes Específicos

```bash
# Apenas testes unitários
./vendor/bin/phpunit --testsuite Unit

# Apenas testes de feature
./vendor/bin/phpunit --testsuite Feature

# Apenas testes de integração
./vendor/bin/phpunit --testsuite Integration

# Testes com relatório de cobertura
./vendor/bin/phpunit --coverage-html coverage-report
```

### Estrutura de Testes

- **Unit Tests:** Testam entidades e serviços isoladamente
- **Feature Tests:** Testam fluxos completos end-to-end
- **Integration Tests:** Testam comunicação entre serviços

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

- **RabbitMQ Management:** <http://localhost:15672>
- **phpMyAdmin:** <http://localhost:8090>
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
<!-- 
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
``` -->

<!-- ## Solução de Problemas

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
``` -->

<!-- ### Logs Detalhados

```bash
# Habilitar debug
export APP_DEBUG=true
docker-compose restart

# Ver logs em tempo real
docker-compose logs -f --tail=100
``` -->

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
git clone https://github.com/NeuronioAzul/car-dealership.git
cd car-dealership

# Instalar dependências de teste
cd tests/
composer install

# Executar testes antes de contribuir
./run_tests.sh
```

### Estrutura do Projeto

```text
car-dealership/
│
├── api-gateway/            # Configuração do Kong API Gateway
│
├── admin-service/          # Microserviço administrativo
├── auth-service/           # Microserviço de autenticação
├── customer-service/       # Microserviço de clientes
├── payment-service/        # Microserviço de pagamentos
├── reservation-service/    # Microserviço de reservas
├── sales-service/          # Microserviço de vendas
├── vehicle-service/        # Microserviço de veículos
│
├── saga-orchestrator/      # Orquestrador de transações SAGA
│
├── docs/                   # Documentação do projeto
├── docker-compose.yml      # Orquestração dos serviços com Docker
├── shared/                 # Recursos compartilhados entre serviços
└── tests/                  # Testes automatizados do sistema

```

## Licença

MIT License (MIT)

Este projeto é desenvolvido para Fase final da Pós Graduação FIAP de Software Archtecture.

---

**Versão:** 1.0.0  
**Contato:** Mauro Rocha Tavares


incluir documentação do kong Deck
https://github.com/kong/deck/?tab=readme-ov-file#documentation
