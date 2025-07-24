# Sistema de Concessionária de Veículos - Documentação Técnica Completa

**Versão:** 1.0.0  
**Data:** 13 de junho de 2025  
**Autor:** Mauro Rocha Tavares  
**Arquitetura:** Microserviços com Clean Architecture  
**Tecnologias:** PHP 8.4, MySQL 8, RabbitMQ 3, Kong API Gateway, Docker

---

## Sumário Executivo

O Sistema de Concessionária de Veículos representa uma solução tecnológica moderna e abrangente para gestão completa de concessionárias automotivas. Desenvolvido utilizando arquitetura de microserviços com Clean Architecture, o sistema oferece escalabilidade, manutenibilidade e robustez necessárias para operações comerciais críticas.

### Visão Geral da Solução

Este sistema foi concebido para atender às necessidades complexas de uma concessionária moderna, desde a gestão de catálogo de veículos até o processamento completo de vendas com geração automática de documentação legal. A arquitetura distribuída permite que cada componente evolua independentemente, garantindo flexibilidade para futuras expansões e integrações.

A implementação segue rigorosamente os princípios de Clean Architecture, separando claramente as responsabilidades entre camadas de domínio, aplicação, infraestrutura e apresentação. Esta abordagem garante que a lógica de negócio permaneça isolada de detalhes técnicos, facilitando testes, manutenção e evolução do sistema.

### Principais Características Técnicas

**Arquitetura de Microserviços:** O sistema é composto por 8 microserviços independentes, cada um responsável por um domínio específico do negócio. Esta separação permite escalabilidade horizontal, deployment independente e isolamento de falhas.

**Clean Architecture:** Cada microserviço implementa Clean Architecture com separação clara entre camadas de Domain, Application, Infrastructure e Presentation, garantindo baixo acoplamento e alta coesão.

**Padrão SAGA:** Implementação completa do padrão SAGA para coordenação de transações distribuídas, garantindo consistência eventual e capacidade de compensação automática em caso de falhas.

**API Gateway:** Kong API Gateway fornece ponto único de entrada, implementando autenticação JWT, rate limiting, CORS e roteamento inteligente para todos os microserviços.

**Containerização:** Todos os componentes são containerizados com Docker, facilitando deployment, escalabilidade e gestão de dependências.

**Observabilidade:** Sistema preparado para monitoramento com logs estruturados, métricas de performance e health checks abrangentes.

## Arquitetura do Sistema

### Visão Geral Arquitetural

A arquitetura do sistema segue o padrão de microserviços distribuídos, onde cada serviço possui responsabilidades bem definidas e comunica-se através de APIs REST e mensageria assíncrona. Esta abordagem oferece benefícios significativos em termos de escalabilidade, manutenibilidade e resiliência.

O design arquitetural prioriza a separação de responsabilidades, com cada microserviço gerenciando seu próprio banco de dados e lógica de negócio. A comunicação entre serviços é realizada através de contratos bem definidos, garantindo baixo acoplamento e alta coesão.

### Componentes Principais

**Kong API Gateway** atua como ponto único de entrada para todas as requisições externas, implementando funcionalidades transversais como autenticação, autorização, rate limiting e roteamento. Esta centralização simplifica a gestão de segurança e políticas de acesso.

**Microserviços de Negócio** implementam a lógica específica de cada domínio, mantendo independência operacional e tecnológica. Cada serviço pode ser desenvolvido, testado, deployado e escalado independentemente.

**RabbitMQ** fornece infraestrutura de mensageria para comunicação assíncrona entre serviços, implementando padrões de publish/subscribe e garantindo entrega confiável de mensagens.

**MySQL** serve como sistema de persistência com bancos de dados separados para cada microserviço, garantindo isolamento de dados e permitindo otimizações específicas por domínio.

### Padrões Arquiteturais Implementados

**Clean Architecture:** Cada microserviço segue os princípios de Clean Architecture com camadas bem definidas:

- **Domain Layer:** Contém entidades de negócio, value objects e regras de domínio puras, independentes de frameworks e tecnologias externas.
- **Application Layer:** Implementa casos de uso e orquestra operações de domínio, definindo contratos para repositórios e serviços externos.
- **Infrastructure Layer:** Fornece implementações concretas para persistência, comunicação externa e frameworks técnicos.
- **Presentation Layer:** Gerencia interface com usuários através de controllers REST e middleware de autenticação.

**SAGA Pattern:** Implementado para coordenação de transações distribuídas, garantindo consistência eventual através de:

- **Orquestração Centralizada:** SAGA Orchestrator coordena sequência de operações entre múltiplos serviços.
- **Compensação Automática:** Mecanismo de rollback automático em caso de falhas em qualquer etapa da transação.
- **Idempotência:** Todas as operações são idempotentes, permitindo retry seguro em caso de falhas temporárias.
- **Monitoramento:** Acompanhamento detalhado do progresso de transações complexas.

**Event-Driven Architecture:** Comunicação assíncrona através de eventos para:

- **Desacoplamento:** Serviços comunicam-se através de eventos sem conhecimento direto uns dos outros.
- **Escalabilidade:** Processamento assíncrono permite melhor utilização de recursos.
- **Auditoria:** Eventos fornecem trilha de auditoria completa de todas as operações.
- **Integração:** Facilita integração com sistemas externos através de eventos padronizados.

## Microserviços Detalhados

### Auth Service - Autenticação e Autorização

O Auth Service é responsável por toda a gestão de identidade e acesso do sistema, implementando autenticação robusta baseada em JWT e controle de acesso baseado em roles.

**Responsabilidades Principais:**
- Registro e validação de novos usuários
- Autenticação via email/senha com hash bcrypt
- Geração e validação de tokens JWT
- Gestão de refresh tokens para segurança aprimorada
- Controle de acesso baseado em roles (RBAC)
- Auditoria de tentativas de login

**Arquitetura Interna:**

*Domain Layer:*
- **User Entity:** Representa usuários do sistema com validações de negócio para email, CPF, telefone e endereço
- **Address Value Object:** Encapsula dados de endereço com validações específicas
- **Role Enum:** Define roles disponíveis (customer, admin) com permissões associadas

*Application Layer:*
- **LoginUseCase:** Orquestra processo de autenticação com validação de credenciais e geração de tokens
- **RegisterUseCase:** Coordena registro de novos usuários com validações e verificação de unicidade
- **JWTService:** Gerencia ciclo de vida completo de tokens JWT incluindo geração, validação e refresh

*Infrastructure Layer:*
- **UserRepository:** Implementa persistência de usuários com queries otimizadas
- **DatabaseConfig:** Configuração de conexão com banco MySQL específico
- **EventPublisher:** Publica eventos de autenticação para outros serviços

*Presentation Layer:*
- **AuthController:** Endpoints REST para todas as operações de autenticação
- **Router:** Configuração de rotas com middleware de validação

**Endpoints Principais:**
- `POST /auth/register` - Registro de novos usuários
- `POST /auth/login` - Autenticação e geração de tokens
- `POST /auth/refresh` - Renovação de access tokens
- `GET /auth/validate` - Validação de tokens
- `GET /auth/health` - Health check do serviço

**Segurança Implementada:**
- Hash de senhas com bcrypt e salt aleatório
- Tokens JWT com expiração configurável
- Refresh tokens com rotação automática
- Rate limiting específico para tentativas de login
- Validação rigorosa de dados de entrada
- Logs de auditoria para todas as operações

### Vehicle Service - Gestão de Catálogo

O Vehicle Service gerencia todo o catálogo de veículos da concessionária, oferecendo funcionalidades avançadas de busca, filtros e controle de status.

**Responsabilidades Principais:**
- Manutenção do catálogo completo de veículos
- Busca avançada com múltiplos filtros
- Controle de status (disponível, reservado, vendido)
- Gestão de características e especificações técnicas
- Suporte a imagens e documentação de veículos
- Relatórios de estoque e análises

**Arquitetura Interna:**

*Domain Layer:*
- **Vehicle Entity:** Representa veículos com todas as especificações técnicas e comerciais
- **VehicleStatus Enum:** Define estados possíveis (available, reserved, sold)
- **VehicleSpecifications Value Object:** Encapsula especificações técnicas detalhadas

*Application Layer:*
- **ListVehiclesUseCase:** Implementa listagem com paginação e filtros avançados
- **SearchVehiclesUseCase:** Busca textual inteligente em múltiplos campos
- **GetVehicleDetailsUseCase:** Recupera informações completas de veículo específico
- **UpdateVehicleStatusUseCase:** Gerencia transições de status com validações

*Infrastructure Layer:*
- **VehicleRepository:** Queries otimizadas para busca e filtros complexos
- **SearchEngine:** Implementação de busca textual com relevância
- **ImageStorage:** Gestão de imagens e documentos associados

*Presentation Layer:*
- **VehicleController:** Endpoints REST com suporte a filtros avançados
- **FilterValidator:** Validação de parâmetros de busca e filtros

**Funcionalidades de Busca:**
- Busca textual em marca, modelo e descrição
- Filtros por faixa de preço e ano
- Filtros por características técnicas (combustível, transmissão, cor)
- Ordenação por múltiplos critérios
- Paginação otimizada para grandes catálogos
- Cache inteligente para consultas frequentes

**Gestão de Status:**
- Transições automáticas baseadas em eventos
- Validações de negócio para mudanças de status
- Histórico completo de alterações
- Integração com sistema de reservas e vendas

### Customer Service - Gestão de Clientes

O Customer Service é responsável pela gestão completa de perfis de clientes, oferecendo funcionalidades de visualização, atualização e manutenção de dados pessoais.

**Responsabilidades Principais:**
- Gestão de perfis completos de clientes
- Validação e atualização de dados pessoais
- Histórico de interações e transações
- Preferências e configurações personalizadas
- Soft delete para preservação de histórico
- Integração com sistema de vendas e reservas

**Arquitetura Interna:**

*Domain Layer:*
- **Customer Entity:** Representa clientes com validações específicas de CPF, telefone e endereço
- **CustomerPreferences Value Object:** Encapsula preferências e configurações
- **ContactInfo Value Object:** Gerencia informações de contato com validações

*Application Layer:*
- **GetCustomerProfileUseCase:** Recupera perfil completo do cliente autenticado
- **UpdateCustomerProfileUseCase:** Atualiza dados com validações e verificações
- **DeleteCustomerAccountUseCase:** Implementa soft delete preservando histórico

*Infrastructure Layer:*
- **CustomerRepository:** Persistência otimizada com queries específicas
- **ValidationService:** Validações avançadas de CPF, telefone e endereço
- **AuditLogger:** Registro de todas as alterações de perfil

*Presentation Layer:*
- **CustomerController:** Endpoints REST com autenticação obrigatória
- **AuthMiddleware:** Validação de tokens e permissões

**Validações Implementadas:**
- CPF com verificação de dígitos verificadores
- Telefone com validação de formato brasileiro
- Email com verificação de formato e domínio
- Endereço com validação de CEP
- Unicidade de CPF e email no sistema

### Reservation Service - Sistema de Reservas

O Reservation Service implementa um sistema sofisticado de reservas com expiração automática, controle de limites e integração com pagamentos.

**Responsabilidades Principais:**
- Criação e gestão de reservas de veículos
- Expiração automática após 24 horas
- Controle de limite (máximo 3 reservas ativas por cliente)
- Geração de códigos únicos para pagamento
- Cancelamento manual e automático
- Integração com sistema de pagamentos

**Arquitetura Interna:**

*Domain Layer:*
- **Reservation Entity:** Representa reservas com lógica de expiração e validações
- **ReservationStatus Enum:** Estados possíveis (active, expired, cancelled, completed)
- **PaymentCode Value Object:** Códigos únicos para processamento de pagamentos

*Application Layer:*
- **CreateReservationUseCase:** Criação com validações de disponibilidade e limites
- **CancelReservationUseCase:** Cancelamento com liberação automática do veículo
- **GeneratePaymentCodeUseCase:** Geração de códigos únicos para pagamento
- **ListCustomerReservationsUseCase:** Listagem com filtros e paginação

*Infrastructure Layer:*
- **ReservationRepository:** Queries otimizadas para consultas temporais
- **ExpirationScheduler:** Serviço de expiração automática baseado em cron
- **PaymentCodeGenerator:** Geração de códigos únicos e seguros

*Presentation Layer:*
- **ReservationController:** Endpoints REST com autenticação obrigatória
- **ReservationValidator:** Validações específicas de regras de negócio

**Regras de Negócio:**
- Máximo 3 reservas ativas por cliente
- Expiração automática em 24 horas
- Verificação de disponibilidade do veículo
- Códigos de pagamento únicos e temporários
- Liberação automática em caso de expiração
- Notificações de expiração próxima

### Payment Service - Processamento de Pagamentos

O Payment Service implementa processamento completo de pagamentos através de gateway fictício configurável, suportando múltiplos métodos de pagamento.

**Responsabilidades Principais:**
- Processamento de pagamentos via gateway fictício
- Suporte a múltiplos métodos (cartão, PIX, transferência)
- Validação de dados de pagamento
- Retry automático para falhas temporárias
- Histórico completo de transações
- Integração com sistema de vendas

**Arquitetura Interna:**

*Domain Layer:*
- **Payment Entity:** Representa pagamentos com estados e transições
- **PaymentMethod Enum:** Métodos suportados (credit_card, debit_card, pix, bank_transfer)
- **PaymentStatus Enum:** Estados possíveis (pending, processing, completed, failed)

*Application Layer:*
- **ProcessPaymentUseCase:** Orquestra processamento completo com validações
- **CreatePaymentUseCase:** Criação de registros de pagamento
- **GetPaymentStatusUseCase:** Consulta de status e detalhes
- **FakePaymentGatewayService:** Simulação de gateway com configurações realistas

*Infrastructure Layer:*
- **PaymentRepository:** Persistência com queries otimizadas para consultas
- **GatewayClient:** Cliente para comunicação com gateway externo
- **RetryService:** Implementação de retry com backoff exponencial

*Presentation Layer:*
- **PaymentController:** Endpoints REST com validação rigorosa
- **PaymentValidator:** Validações específicas por método de pagamento

**Gateway Fictício:**
- Taxa de sucesso configurável (85% padrão)
- Simulação de tempo de processamento realista
- Diferentes tipos de erro para testes
- Suporte a estornos e cancelamentos
- Logs detalhados para auditoria

### Sales Service - Gestão de Vendas

O Sales Service gerencia vendas finalizadas e gera automaticamente documentação legal em PDF, incluindo contratos e notas fiscais.

**Responsabilidades Principais:**
- Criação automática de vendas após pagamento aprovado
- Geração de contratos de compra/venda em PDF
- Geração de notas fiscais em PDF
- Histórico completo de vendas por cliente
- Download seguro de documentos
- Integração com sistema fiscal

**Arquitetura Interna:**

*Domain Layer:*
- **Sale Entity:** Representa vendas com dados completos de transação
- **SaleStatus Enum:** Estados possíveis (pending, completed, cancelled)
- **SaleDocument Value Object:** Metadados de documentos gerados

*Application Layer:*
- **CreateSaleUseCase:** Criação automática após confirmação de pagamento
- **GetSaleDetailsUseCase:** Recupera detalhes completos de venda
- **ListCustomerSalesUseCase:** Histórico de vendas com filtros
- **PDFGeneratorService:** Geração de documentos PDF com templates profissionais

*Infrastructure Layer:*
- **SaleRepository:** Persistência com relacionamentos complexos
- **PDFEngine:** Engine de geração de PDF com templates customizáveis
- **DocumentStorage:** Armazenamento seguro de documentos gerados

*Presentation Layer:*
- **SaleController:** Endpoints REST com controle de acesso rigoroso
- **DocumentController:** Download seguro de PDFs com autenticação

**Geração de Documentos:**
- Templates profissionais para contratos e notas fiscais
- Dados completos de cliente, veículo e transação
- Assinatura digital e timestamps
- Armazenamento seguro com controle de acesso
- Versionamento de templates
- Compliance com regulamentações fiscais

### Admin Service - Painel Administrativo

O Admin Service fornece painel administrativo completo com dashboard em tempo real, relatórios detalhados e acesso a dados consolidados.

**Responsabilidades Principais:**
- Dashboard com estatísticas em tempo real
- Relatórios detalhados de vendas, clientes e veículos
- Análises de performance e tendências
- Gestão de usuários e permissões
- Monitoramento de sistema
- Exportação de dados para análise

**Arquitetura Interna:**

*Domain Layer:*
- **DashboardMetrics Value Object:** Métricas consolidadas do sistema
- **ReportFilter Value Object:** Filtros para geração de relatórios
- **AdminUser Entity:** Usuários administrativos com permissões especiais

*Application Layer:*
- **DashboardService:** Consolidação de métricas de múltiplos serviços
- **ReportService:** Geração de relatórios com filtros avançados
- **UserManagementService:** Gestão de usuários e permissões

*Infrastructure Layer:*
- **MultiDatabaseConfig:** Conexões com bancos de todos os microserviços
- **MetricsAggregator:** Agregação de dados de múltiplas fontes
- **ReportGenerator:** Geração de relatórios em múltiplos formatos

*Presentation Layer:*
- **AdminController:** Endpoints REST exclusivos para administradores
- **AdminAuthMiddleware:** Validação de permissões administrativas

**Funcionalidades do Dashboard:**
- Estatísticas de usuários ativos e novos registros
- Métricas de veículos por status e marca
- Taxa de conversão de reservas em vendas
- Performance de pagamentos e taxa de aprovação
- Receita total e breakdown mensal
- Alertas para métricas críticas

### SAGA Orchestrator - Coordenação de Transações

O SAGA Orchestrator implementa o padrão SAGA para coordenação de transações distribuídas complexas, garantindo consistência eventual e capacidade de compensação.

**Responsabilidades Principais:**
- Coordenação de transações distribuídas
- Implementação de padrão SAGA com compensação
- Monitoramento de progresso de transações
- Retry automático para falhas temporárias
- Logging detalhado para auditoria
- Garantia de consistência eventual

**Arquitetura Interna:**

*Domain Layer:*
- **SagaTransaction Entity:** Representa transações distribuídas com estados
- **SagaStep Value Object:** Passos individuais com resultado e compensação
- **TransactionStatus Enum:** Estados possíveis (started, running, completed, failed, compensated)

*Application Layer:*
- **VehiclePurchaseSaga:** Implementação específica para compra de veículos
- **StartVehiclePurchaseUseCase:** Inicialização de transações de compra
- **SagaProcessorService:** Processamento contínuo de transações pendentes
- **MicroserviceClient:** Cliente HTTP para comunicação com outros serviços

*Infrastructure Layer:*
- **SagaTransactionRepository:** Persistência de estado de transações
- **EventConsumer:** Consumo de eventos para progressão de SAGAs
- **CompensationEngine:** Engine de compensação automática

*Presentation Layer:*
- **SagaController:** Endpoints para inicialização e monitoramento
- **TransactionMonitor:** Interface para acompanhamento de progresso

**Fluxo de Compra SAGA:**
1. **create_reservation:** Criação de reserva do veículo
2. **generate_payment_code:** Geração de código único para pagamento
3. **process_payment:** Processamento do pagamento via gateway
4. **create_sale:** Criação da venda e geração de documentos
5. **update_vehicle_status:** Atualização do status do veículo para vendido

**Compensação Automática:**
- Cancelamento de reservas em caso de falha
- Estorno de pagamentos quando necessário
- Cancelamento de vendas em caso de erro
- Restauração de status de veículos
- Logging completo de ações de compensação

## Infraestrutura e Tecnologias

### Kong API Gateway

O Kong API Gateway serve como ponto único de entrada para todas as requisições externas, implementando funcionalidades transversais essenciais para segurança e performance.

**Funcionalidades Implementadas:**

*Autenticação JWT:*
- Validação automática de tokens JWT em rotas protegidas
- Extração de claims de usuário para autorização
- Suporte a múltiplos issuers e algoritmos
- Configuração flexível de expiração e refresh

*Rate Limiting:*
- Limite de 100 requisições por minuto por IP
- Limite de 1000 requisições por hora por IP
- Headers informativos sobre limites restantes
- Configuração diferenciada por endpoint

*CORS (Cross-Origin Resource Sharing):*
- Suporte completo a requisições cross-origin
- Configuração flexível de origens permitidas
- Headers de CORS apropriados para desenvolvimento e produção
- Suporte a métodos HTTP complexos

*Request/Response Transformation:*
- Limitação de tamanho de requisição (10MB)
- Validação de Content-Type
- Headers de segurança automáticos
- Compressão de respostas

*Roteamento Inteligente:*
- Roteamento baseado em path e método HTTP
- Load balancing entre instâncias de serviços
- Health checks automáticos de upstream services
- Failover automático em caso de falhas

**Configuração de Rotas:**

```yaml
services:
  - name: auth-service
    url: http://auth-service:80
    routes:
      - name: auth-routes
        paths: ["/api/v1/auth"]
        
  - name: vehicle-service
    url: http://vehicle-service:80
    routes:
      - name: vehicle-routes
        paths: ["/api/v1/vehicles"]
```

**Plugins Configurados:**
- **JWT Plugin:** Validação automática de tokens
- **Rate Limiting Plugin:** Proteção contra abuso
- **CORS Plugin:** Suporte a aplicações web
- **Request Size Limiting:** Proteção contra payloads grandes
- **Response Transformer:** Headers de segurança

### MySQL 8 - Sistema de Persistência

O MySQL 8 serve como sistema de gerenciamento de banco de dados relacional, com bancos separados para cada microserviço garantindo isolamento e escalabilidade.

**Configuração de Bancos:**

*Bancos por Microserviço:*
- **auth_db:** Usuários, roles e tokens
- **customer_db:** Perfis de clientes e preferências
- **vehicle_db:** Catálogo de veículos e especificações
- **reservation_db:** Reservas e códigos de pagamento
- **payment_db:** Transações e histórico de pagamentos
- **sales_db:** Vendas e documentos gerados
- **admin_db:** Métricas e configurações administrativas
- **saga_db:** Estado de transações distribuídas

*Características Técnicas:*
- **Charset:** utf8mb4 para suporte completo a Unicode
- **Engine:** InnoDB para transações ACID
- **Isolation Level:** READ COMMITTED para performance otimizada
- **Connection Pooling:** Configurado para alta concorrência
- **Indexação:** Índices otimizados para consultas frequentes

*Otimizações Implementadas:*
- Índices compostos para consultas complexas
- Particionamento de tabelas grandes por data
- Query cache configurado para consultas repetitivas
- Slow query log para identificação de gargalos
- Backup automático com retenção configurável

**Esquemas de Dados:**

*Auth Service Schema:*
```sql
CREATE TABLE users (
    id VARCHAR(36) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    cpf VARCHAR(11) UNIQUE NOT NULL,
    phone VARCHAR(20),
    role ENUM('customer', 'admin') DEFAULT 'customer',
    address JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_cpf (cpf),
    INDEX idx_role (role)
);
```

*Vehicle Service Schema:*
```sql
CREATE TABLE vehicles (
    id VARCHAR(36) PRIMARY KEY,
    brand VARCHAR(100) NOT NULL,
    model VARCHAR(100) NOT NULL,
    manufacturing_year INT NOT NULL,
    model_year INT NOT NULL,
    color VARCHAR(50),
    mileage INT DEFAULT 0,
    fuel_type ENUM('gasoline', 'ethanol', 'flex', 'diesel', 'hybrid', 'electric'),
    transmission_type ENUM('manual', 'automatic', 'cvt'),
    price DECIMAL(10,2) NOT NULL,
    status ENUM('available', 'reserved', 'sold') DEFAULT 'available',
    description TEXT,
    features JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_brand_model (brand, model),
    INDEX idx_price (price),
    INDEX idx_year (manufacturing_year, model_year),
    INDEX idx_status (status),
    FULLTEXT idx_search (brand, model, description)
);
```

### RabbitMQ 3 - Message Broker

O RabbitMQ fornece infraestrutura robusta de mensageria para comunicação assíncrona entre microserviços, implementando padrões de publish/subscribe.

**Configuração de Exchanges e Filas:**

*Exchanges Configurados:*
- **auth.events:** Eventos de autenticação e autorização
- **vehicle.events:** Eventos de alteração de status de veículos
- **reservation.events:** Eventos de criação e cancelamento de reservas
- **payment.events:** Eventos de processamento de pagamentos
- **sales.events:** Eventos de criação de vendas
- **saga.events:** Eventos de coordenação SAGA

*Filas por Serviço:*
- **auth.user_registered:** Notificações de novos usuários
- **vehicle.status_changed:** Alterações de status de veículos
- **reservation.created:** Novas reservas criadas
- **reservation.cancelled:** Reservas canceladas
- **payment.completed:** Pagamentos aprovados
- **payment.failed:** Pagamentos recusados
- **sales.created:** Vendas finalizadas
- **saga.step_completed:** Passos SAGA concluídos

**Padrões de Mensageria:**

*Event Sourcing:*
- Todos os eventos são persistidos para auditoria
- Replay de eventos para reconstrução de estado
- Versionamento de eventos para evolução
- Timestamps e metadados completos

*Publish/Subscribe:*
- Desacoplamento entre produtores e consumidores
- Múltiplos consumidores por evento
- Routing baseado em patterns
- Dead letter queues para mensagens falhadas

*Garantias de Entrega:*
- Acknowledgments manuais para garantir processamento
- Durabilidade de filas e mensagens
- Retry automático com backoff exponencial
- Dead letter queues para mensagens não processáveis

**Configuração de Durabilidade:**
```json
{
  "exchanges": [
    {
      "name": "auth.events",
      "type": "topic",
      "durable": true,
      "auto_delete": false
    }
  ],
  "queues": [
    {
      "name": "auth.user_registered",
      "durable": true,
      "auto_delete": false,
      "arguments": {
        "x-message-ttl": 86400000,
        "x-dead-letter-exchange": "dlx.auth"
      }
    }
  ]
}
```

### Docker e Containerização

Todos os componentes do sistema são containerizados usando Docker, facilitando deployment, escalabilidade e gestão de dependências.

**Estratégia de Containerização:**

*Imagens Base:*
- **PHP Services:** php:8.4-apache com extensões necessárias
- **MySQL:** mysql:8.0 com configurações otimizadas
- **RabbitMQ:** rabbitmq:3-management com plugins habilitados
- **Kong:** kong:3.0 com configuração declarativa
- **Nginx:** nginx:alpine para documentação

*Multi-stage Builds:*
- Separação entre ambiente de build e runtime
- Otimização de tamanho de imagens finais
- Cache de dependências para builds mais rápidos
- Segurança aprimorada com usuários não-root

**Dockerfile Exemplo (Auth Service):**
```dockerfile
FROM php:8.4-apache

# Instalar extensões PHP necessárias
RUN docker-php-ext-install pdo_mysql mysqli

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar Apache
RUN a2enmod rewrite headers
COPY apache.conf /etc/apache2/sites-available/000-default.conf

# Copiar código da aplicação
WORKDIR /var/www/html
COPY . .

# Instalar dependências
RUN composer install --no-dev --optimize-autoloader

# Configurar permissões
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

EXPOSE 80
```

**Docker Compose Orchestration:**
```yaml
version: '3.8'

services:
  auth-service:
    build: ./auth-service
    environment:
      - DB_HOST=mysql
      - RABBITMQ_HOST=rabbitmq
    depends_on:
      - mysql
      - rabbitmq
    networks:
      - car-dealership

  mysql:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: rootpassword
    volumes:
      - mysql_data:/var/lib/mysql
      - ./shared/database/init:/docker-entrypoint-initdb.d
    networks:
      - car-dealership

networks:
  car-dealership:
    driver: bridge

volumes:
  mysql_data:
```

## Segurança e Autenticação

### Implementação de JWT

O sistema implementa autenticação robusta baseada em JSON Web Tokens (JWT) com suporte a refresh tokens e validação rigorosa.

**Estrutura de Tokens:**

*Access Token:*
- **Algoritmo:** HS256 (HMAC SHA-256)
- **Expiração:** 1 hora (configurável)
- **Claims:** user_id, email, role, iat, exp
- **Uso:** Autenticação em requisições API

*Refresh Token:*
- **Algoritmo:** HS256 (HMAC SHA-256)
- **Expiração:** 30 dias (configurável)
- **Claims:** user_id, token_type, iat, exp
- **Uso:** Renovação de access tokens

**Payload de Access Token:**
```json
{
  "user_id": "uuid-do-usuario",
  "email": "usuario@email.com",
  "role": "customer",
  "iat": 1623589800,
  "exp": 1623593400
}
```

**Fluxo de Autenticação:**

1. **Login:** Cliente envia credenciais (email/senha)
2. **Validação:** Servidor valida credenciais e gera tokens
3. **Resposta:** Servidor retorna access token e refresh token
4. **Requisições:** Cliente inclui access token no header Authorization
5. **Validação:** Kong/Serviços validam token em cada requisição
6. **Renovação:** Cliente usa refresh token para obter novo access token

**Validação de Tokens:**
- Verificação de assinatura com chave secreta
- Validação de expiração (exp claim)
- Verificação de issuer e audience
- Blacklist de tokens revogados
- Rate limiting para tentativas de validação

### Controle de Acesso (RBAC)

O sistema implementa controle de acesso baseado em roles (Role-Based Access Control) com dois níveis principais de permissões.

**Roles Implementados:**

*Customer (Cliente):*
- Visualizar catálogo de veículos
- Criar e gerenciar reservas próprias
- Processar pagamentos
- Visualizar histórico de compras próprias
- Gerenciar perfil pessoal
- Download de documentos próprios

*Admin (Administrador):*
- Todas as permissões de cliente
- Acesso ao painel administrativo
- Visualizar todos os usuários e transações
- Gerar relatórios completos
- Gerenciar catálogo de veículos
- Configurações do sistema

**Implementação de Middleware:**
```php
class AuthMiddleware
{
    public function handle($request, $next, $requiredRole = null)
    {
        $token = $this->extractToken($request);
        
        if (!$token) {
            return $this->unauthorizedResponse();
        }
        
        $payload = $this->validateToken($token);
        
        if (!$payload) {
            return $this->unauthorizedResponse();
        }
        
        if ($requiredRole && $payload['role'] !== $requiredRole) {
            return $this->forbiddenResponse();
        }
        
        $request->user = $payload;
        return $next($request);
    }
}
```

**Proteção de Endpoints:**
- Endpoints públicos: Catálogo de veículos, health checks
- Endpoints de cliente: Reservas, pagamentos, perfil
- Endpoints administrativos: Dashboard, relatórios, gestão

### Validação e Sanitização

O sistema implementa validação rigorosa de todos os dados de entrada com sanitização apropriada para prevenir ataques.

**Validações Implementadas:**

*Dados Pessoais:*
- **CPF:** Validação de dígitos verificadores e formato
- **Email:** Validação de formato RFC 5322 e verificação de domínio
- **Telefone:** Validação de formato brasileiro (DDD + número)
- **CEP:** Validação de formato e existência

*Dados Financeiros:*
- **Cartão de Crédito:** Validação de algoritmo de Luhn
- **Valores Monetários:** Validação de formato e limites
- **Códigos de Pagamento:** Validação de formato e unicidade

*Dados de Entrada:*
- **SQL Injection:** Prepared statements em todas as queries
- **XSS:** Sanitização de HTML e JavaScript
- **CSRF:** Tokens CSRF em formulários sensíveis
- **Input Length:** Limitação de tamanho de campos

**Exemplo de Validação:**
```php
class UserValidator
{
    public function validateRegistration($data)
    {
        $errors = [];
        
        // Validar email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email inválido';
        }
        
        // Validar CPF
        if (!$this->validateCPF($data['cpf'])) {
            $errors['cpf'] = 'CPF inválido';
        }
        
        // Validar senha
        if (strlen($data['password']) < 8) {
            $errors['password'] = 'Senha deve ter pelo menos 8 caracteres';
        }
        
        return $errors;
    }
    
    private function validateCPF($cpf)
    {
        // Implementação de validação de CPF
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        
        if (strlen($cpf) !== 11) {
            return false;
        }
        
        // Verificar dígitos verificadores
        // ... lógica de validação
        
        return true;
    }
}
```

## Testes e Qualidade

### Estratégia de Testes

O sistema implementa estratégia abrangente de testes com cobertura de múltiplas camadas e cenários, garantindo qualidade e confiabilidade.

**Pirâmide de Testes:**

*Testes Unitários (Base da Pirâmide):*
- **Cobertura:** Entidades de domínio, value objects, serviços de aplicação
- **Framework:** PHPUnit 10 com mocks e stubs
- **Isolamento:** Testes completamente isolados sem dependências externas
- **Performance:** Execução rápida (< 50ms por teste)
- **Cobertura Meta:** 100% das entidades e 90% dos serviços

*Testes de Integração (Meio da Pirâmide):*
- **Cobertura:** Comunicação entre componentes via HTTP
- **Framework:** PHPUnit com Guzzle HTTP Client
- **Escopo:** Endpoints REST, validações, autenticação
- **Ambiente:** Containers Docker para isolamento
- **Cobertura Meta:** 80% dos endpoints

*Testes de Feature/E2E (Topo da Pirâmide):*
- **Cobertura:** Fluxos completos de negócio
- **Framework:** PHPUnit com cenários realistas
- **Escopo:** Jornadas completas do usuário
- **Validação:** Integração entre todos os serviços
- **Cobertura Meta:** 100% dos fluxos críticos

**Estrutura de Testes:**
```
tests/
├── Unit/                     # Testes unitários
│   ├── Auth/
│   │   ├── UserEntityTest.php
│   │   └── JWTServiceTest.php
│   ├── Vehicle/
│   │   └── VehicleEntityTest.php
│   └── Saga/
│       └── SagaTransactionTest.php
├── Integration/              # Testes de integração
│   ├── AuthServiceIntegrationTest.php
│   ├── VehicleServiceIntegrationTest.php
│   ├── APIGatewayIntegrationTest.php
│   └── PDFGenerationTest.php
├── Feature/                  # Testes de feature
│   └── CompletePurchaseFlowTest.php
└── Scripts/                  # Scripts auxiliares
    └── DatabaseSeeder.php
```

### Testes Unitários

Os testes unitários focam em validar a lógica de negócio isoladamente, sem dependências externas.

**Exemplo de Teste de Entidade:**
```php
class UserEntityTest extends TestCase
{
    public function testUserCreationWithValidData(): void
    {
        $userData = [
            'id' => 'uuid-test',
            'name' => 'João Silva',
            'email' => 'joao@email.com',
            'password' => 'senha123',
            'cpf' => '12345678901',
            'phone' => '11999887766',
            'role' => 'customer'
        ];
        
        $user = new User($userData);
        
        $this->assertEquals('João Silva', $user->getName());
        $this->assertEquals('joao@email.com', $user->getEmail());
        $this->assertEquals('customer', $user->getRole());
        $this->assertTrue($user->verifyPassword('senha123'));
    }
    
    public function testUserPasswordHashing(): void
    {
        $user = new User([
            'id' => 'uuid-test',
            'name' => 'Test User',
            'email' => 'test@email.com',
            'password' => 'plaintext',
            'cpf' => '12345678901',
            'role' => 'customer'
        ]);
        
        // Senha deve ser hasheada
        $this->assertNotEquals('plaintext', $user->getPassword());
        $this->assertTrue(password_verify('plaintext', $user->getPassword()));
    }
    
    public function testUserSoftDelete(): void
    {
        $user = new User([/* dados */]);
        
        $this->assertFalse($user->isDeleted());
        
        $user->softDelete();
        
        $this->assertTrue($user->isDeleted());
        $this->assertNotNull($user->getDeletedAt());
    }
}
```

**Exemplo de Teste de Serviço:**
```php
class JWTServiceTest extends TestCase
{
    private JWTService $jwtService;
    
    protected function setUp(): void
    {
        $this->jwtService = new JWTService('test-secret-key');
    }
    
    public function testTokenGeneration(): void
    {
        $payload = [
            'user_id' => 'uuid-test',
            'email' => 'test@email.com',
            'role' => 'customer'
        ];
        
        $token = $this->jwtService->generateAccessToken($payload);
        
        $this->assertIsString($token);
        $this->assertNotEmpty($token);
        
        // Token deve ter 3 partes separadas por pontos
        $parts = explode('.', $token);
        $this->assertCount(3, $parts);
    }
    
    public function testTokenValidation(): void
    {
        $payload = ['user_id' => 'uuid-test', 'role' => 'customer'];
        $token = $this->jwtService->generateAccessToken($payload);
        
        $decodedPayload = $this->jwtService->validateToken($token);
        
        $this->assertIsArray($decodedPayload);
        $this->assertEquals('uuid-test', $decodedPayload['user_id']);
        $this->assertEquals('customer', $decodedPayload['role']);
    }
    
    public function testExpiredTokenValidation(): void
    {
        // Criar token com expiração no passado
        $expiredToken = $this->jwtService->generateAccessToken(
            ['user_id' => 'test'],
            -3600 // Expirado há 1 hora
        );
        
        $result = $this->jwtService->validateToken($expiredToken);
        
        $this->assertNull($result);
    }
}
```

### Testes de Integração

Os testes de integração validam a comunicação entre componentes através de APIs REST.

**Exemplo de Teste de API:**
```php
class AuthServiceIntegrationTest extends TestCase
{
    private Client $httpClient;
    
    protected function setUp(): void
    {
        $this->httpClient = new Client([
            'base_uri' => TEST_BASE_URL,
            'timeout' => 30
        ]);
    }
    
    public function testUserRegistrationFlow(): void
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test_' . time() . '@email.com',
            'password' => 'senha123',
            'cpf' => '12345678901',
            'phone' => '11999887766',
            'address' => [
                'street' => 'Rua Teste, 123',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01234-567'
            ]
        ];
        
        $response = $this->httpClient->post('/auth/register', [
            'json' => $userData
        ]);
        
        $this->assertEquals(201, $response->getStatusCode());
        
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertTrue($body['success']);
        $this->assertArrayHasKey('user', $body['data']);
        $this->assertEquals($userData['email'], $body['data']['user']['email']);
    }
    
    public function testLoginFlow(): void
    {
        // Primeiro registrar usuário
        $this->registerTestUser();
        
        $loginData = [
            'email' => $this->testUser['email'],
            'password' => $this->testUser['password']
        ];
        
        $response = $this->httpClient->post('/auth/login', [
            'json' => $loginData
        ]);
        
        $this->assertEquals(200, $response->getStatusCode());
        
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertTrue($body['success']);
        $this->assertArrayHasKey('access_token', $body['data']);
        $this->assertArrayHasKey('refresh_token', $body['data']);
    }
    
    public function testProtectedEndpointAccess(): void
    {
        $token = $this->getAuthToken();
        
        $response = $this->httpClient->get('/customer/profile', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token
            ]
        ]);
        
        $this->assertEquals(200, $response->getStatusCode());
    }
    
    public function testUnauthorizedAccess(): void
    {
        $this->expectException(RequestException::class);
        
        $response = $this->httpClient->get('/customer/profile');
        
        $this->assertEquals(401, $response->getStatusCode());
    }
}
```

### Testes de Feature

Os testes de feature validam fluxos completos de negócio end-to-end.

**Exemplo de Teste de Fluxo Completo:**
```php
class CompletePurchaseFlowTest extends TestCase
{
    public function testCompleteVehiclePurchaseFlow(): void
    {
        // 1. Registrar usuário
        $this->registerUser();
        
        // 2. Fazer login
        $this->loginUser();
        
        // 3. Buscar veículo disponível
        $vehicleId = $this->findAvailableVehicle();
        
        // 4. Criar reserva
        $reservationId = $this->createReservation($vehicleId);
        
        // 5. Gerar código de pagamento
        $paymentCode = $this->generatePaymentCode($reservationId);
        
        // 6. Processar pagamento
        $paymentId = $this->processPayment($paymentCode);
        
        // 7. Verificar criação da venda
        $saleId = $this->verifySaleCreation($vehicleId);
        
        // 8. Verificar documentos gerados
        $this->verifyDocuments($saleId);
        
        // 9. Verificar status do veículo
        $this->verifyVehicleStatus($vehicleId, 'sold');
    }
    
    public function testSagaOrchestrationFlow(): void
    {
        $this->registerUser();
        $this->loginUser();
        
        $vehicleId = $this->findAvailableVehicle();
        
        // Iniciar transação SAGA
        $response = $this->httpClient->post('/saga/purchase', [
            'headers' => ['Authorization' => 'Bearer ' . $this->authToken],
            'json' => [
                'vehicle_id' => $vehicleId,
                'customer_data' => $this->getCustomerData(),
                'payment_data' => $this->getPaymentData()
            ]
        ]);
        
        $this->assertEquals(201, $response->getStatusCode());
        
        $body = json_decode($response->getBody()->getContents(), true);
        $transactionId = $body['data']['transaction_id'];
        
        // Monitorar execução da SAGA
        $this->monitorSagaExecution($transactionId);
    }
    
    private function monitorSagaExecution(string $transactionId): void
    {
        $maxAttempts = 30;
        $attempt = 0;
        
        while ($attempt < $maxAttempts) {
            sleep(2);
            $attempt++;
            
            $response = $this->httpClient->get("/saga/transactions/$transactionId", [
                'headers' => ['Authorization' => 'Bearer ' . $this->authToken]
            ]);
            
            $body = json_decode($response->getBody()->getContents(), true);
            $status = $body['data']['transaction']['status'];
            
            if ($status === 'completed') {
                $this->assertEquals('completed', $status);
                return;
            } elseif ($status === 'failed') {
                $this->fail('SAGA falhou: ' . $body['data']['transaction']['failure_reason']);
            }
        }
        
        $this->fail('Timeout na execução da SAGA');
    }
}
```

### Automação e CI/CD

O sistema inclui automação completa para execução de testes e validação de qualidade.

**Script de Execução de Testes:**
```bash
#!/bin/bash
# tests/run_tests.sh

# Verificar dependências
check_dependencies() {
    command -v php >/dev/null 2>&1 || { echo "PHP não encontrado"; exit 1; }
    command -v composer >/dev/null 2>&1 || { echo "Composer não encontrado"; exit 1; }
    command -v docker >/dev/null 2>&1 || { echo "Docker não encontrado"; exit 1; }
}

# Instalar dependências
install_dependencies() {
    cd tests/
    composer install --no-dev --optimize-autoloader
}

# Iniciar serviços
start_services() {
    cd ../
    docker-compose up -d
    sleep 30  # Aguardar inicialização
}

# Executar seeding
run_seeding() {
    php tests/Scripts/DatabaseSeeder.php
}

# Executar testes unitários
run_unit_tests() {
    ./vendor/bin/phpunit --testsuite=Unit --colors=always
}

# Executar testes de integração
run_integration_tests() {
    ./vendor/bin/phpunit --testsuite=Integration --colors=always
}

# Executar testes de feature
run_feature_tests() {
    ./vendor/bin/phpunit --testsuite=Feature --colors=always
}

# Gerar relatório de cobertura
generate_coverage() {
    ./vendor/bin/phpunit --coverage-html coverage --coverage-text
}

# Função principal
main() {
    echo "🚀 Iniciando execução de testes"
    
    check_dependencies
    install_dependencies
    start_services
    run_seeding
    
    echo "🧪 Executando testes unitários..."
    run_unit_tests
    
    echo "🔗 Executando testes de integração..."
    run_integration_tests
    
    echo "🎯 Executando testes de feature..."
    run_feature_tests
    
    echo "📊 Gerando relatório de cobertura..."
    generate_coverage
    
    echo "✅ Todos os testes concluídos!"
}

main "$@"
```

**Configuração de CI/CD (GitHub Actions):**
```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: rootpassword
        options: >-
          --health-cmd="mysqladmin ping"
          --health-interval=10s
          --health-timeout=5s
          --health-retries=3
      
      rabbitmq:
        image: rabbitmq:3-management
        env:
          RABBITMQ_DEFAULT_USER: guest
          RABBITMQ_DEFAULT_PASS: guest
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.4'
        extensions: pdo_mysql, curl, json
    
    - name: Install dependencies
      run: |
        cd tests/
        composer install
    
    - name: Run tests
      run: |
        ./tests/run_tests.sh
    
    - name: Upload coverage
      uses: codecov/codecov-action@v3
      with:
        file: ./tests/coverage/clover.xml
```

## Performance e Escalabilidade

### Otimizações de Performance

O sistema implementa múltiplas estratégias de otimização para garantir performance adequada mesmo com alto volume de transações.

**Otimizações de Banco de Dados:**

*Indexação Estratégica:*
- Índices compostos para consultas complexas de veículos
- Índices de texto completo para busca textual
- Índices parciais para consultas filtradas por status
- Índices de data para consultas temporais

*Query Optimization:*
- Prepared statements para prevenção de SQL injection e cache de queries
- Paginação eficiente com LIMIT/OFFSET otimizado
- Joins otimizados com índices apropriados
- Agregações com GROUP BY otimizado

*Connection Pooling:*
- Pool de conexões configurado para alta concorrência
- Timeout apropriado para conexões idle
- Retry automático para falhas temporárias
- Monitoramento de conexões ativas

**Exemplo de Query Otimizada:**
```sql
-- Busca de veículos com múltiplos filtros
SELECT v.*, 
       MATCH(v.brand, v.model, v.description) AGAINST(? IN NATURAL LANGUAGE MODE) as relevance
FROM vehicles v
WHERE v.status = 'available'
  AND v.price BETWEEN ? AND ?
  AND v.manufacturing_year >= ?
  AND (? IS NULL OR v.brand = ?)
  AND (? IS NULL OR v.fuel_type = ?)
ORDER BY relevance DESC, v.price ASC
LIMIT ? OFFSET ?;
```

**Otimizações de Aplicação:**

*Caching Strategy:*
- Cache de resultados de busca frequentes
- Cache de dados de configuração
- Cache de templates de documentos PDF
- Cache de validações de CPF/CNPJ

*Lazy Loading:*
- Carregamento sob demanda de relacionamentos
- Paginação de resultados grandes
- Streaming de documentos PDF grandes
- Carregamento assíncrono de imagens

*Resource Management:*
- Pool de conexões HTTP para comunicação entre serviços
- Timeout configurável para operações externas
- Cleanup automático de recursos temporários
- Garbage collection otimizado para PHP

### Estratégias de Escalabilidade

O sistema foi projetado para escalar horizontalmente conforme a demanda cresce.

**Escalabilidade Horizontal:**

*Microserviços Independentes:*
- Cada serviço pode ser escalado independentemente
- Load balancing automático entre instâncias
- Service discovery para localização dinâmica
- Health checks para remoção de instâncias falhadas

*Database Scaling:*
- Read replicas para distribuição de consultas
- Sharding por domínio de dados
- Particionamento temporal para dados históricos
- Cache distribuído para dados frequentes

*Message Queue Scaling:*
- Clustering RabbitMQ para alta disponibilidade
- Particionamento de filas por domínio
- Load balancing de consumidores
- Dead letter queues para tratamento de falhas

**Configuração de Load Balancing:**
```yaml
# Kong load balancing configuration
services:
  - name: auth-service
    url: http://auth-service-cluster
    load_balancing:
      algorithm: round-robin
      health_checks:
        active:
          http_path: /health
          healthy:
            interval: 10
            successes: 3
          unhealthy:
            interval: 10
            http_failures: 3
```

**Auto-scaling com Kubernetes:**
```yaml
apiVersion: autoscaling/v2
kind: HorizontalPodAutoscaler
metadata:
  name: auth-service-hpa
spec:
  scaleTargetRef:
    apiVersion: apps/v1
    kind: Deployment
    name: auth-service
  minReplicas: 2
  maxReplicas: 10
  metrics:
  - type: Resource
    resource:
      name: cpu
      target:
        type: Utilization
        averageUtilization: 70
  - type: Resource
    resource:
      name: memory
      target:
        type: Utilization
        averageUtilization: 80
```

### Monitoramento e Observabilidade

O sistema inclui instrumentação completa para monitoramento de performance e detecção de problemas.

**Métricas de Performance:**

*Application Metrics:*
- Tempo de resposta por endpoint
- Taxa de sucesso/erro por operação
- Throughput de requisições por segundo
- Latência de comunicação entre serviços

*Infrastructure Metrics:*
- Utilização de CPU e memória
- Conexões de banco de dados ativas
- Tamanho de filas de mensagens
- Espaço em disco utilizado

*Business Metrics:*
- Taxa de conversão de reservas em vendas
- Tempo médio de processamento de pagamentos
- Volume de vendas por período
- Satisfação do cliente (tempo de resposta)

**Logging Estruturado:**
```php
class StructuredLogger
{
    public function logRequest($request, $response, $duration)
    {
        $logData = [
            'timestamp' => date('c'),
            'level' => 'INFO',
            'service' => 'auth-service',
            'operation' => 'user_login',
            'request' => [
                'method' => $request->getMethod(),
                'path' => $request->getPath(),
                'user_agent' => $request->getHeader('User-Agent'),
                'ip' => $request->getClientIp()
            ],
            'response' => [
                'status_code' => $response->getStatusCode(),
                'duration_ms' => $duration
            ],
            'user_id' => $request->user['id'] ?? null
        ];
        
        error_log(json_encode($logData));
    }
}
```

**Health Checks Avançados:**
```php
class HealthCheckService
{
    public function getHealthStatus()
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'rabbitmq' => $this->checkRabbitMQ(),
            'external_services' => $this->checkExternalServices(),
            'disk_space' => $this->checkDiskSpace(),
            'memory_usage' => $this->checkMemoryUsage()
        ];
        
        $overallStatus = $this->calculateOverallStatus($checks);
        
        return [
            'status' => $overallStatus,
            'timestamp' => date('c'),
            'checks' => $checks,
            'version' => '1.0.0'
        ];
    }
    
    private function checkDatabase()
    {
        try {
            $pdo = new PDO(/* connection string */);
            $stmt = $pdo->query('SELECT 1');
            
            return [
                'status' => 'healthy',
                'response_time_ms' => $this->measureResponseTime()
            ];
        } catch (Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage()
            ];
        }
    }
}
```

## Deployment e DevOps

### Estratégias de Deployment

O sistema suporta múltiplas estratégias de deployment para diferentes ambientes e necessidades.

**Deployment Local (Desenvolvimento):**
```bash
# Clone do repositório
git clone https://github.com/NeuronioAzul/car-dealership.git
cd car-dealership

# Inicialização completa
docker-compose up -d

# Seeding do banco
php tests/Scripts/DatabaseSeeder.php

# Verificação de saúde
curl http://localhost:8000/api/v1/auth/health
```

**Deployment de Produção:**

*Preparação do Ambiente:*
- Servidor com Docker e Docker Compose
- Certificados SSL válidos
- Backup automático configurado
- Monitoramento implementado
- Firewall configurado

*Configuração de Produção:*
```bash
# Variáveis de ambiente de produção
export APP_ENV=production
export JWT_SECRET=<strong-secret-key>
export DB_PASSWORD=<secure-password>
export RABBITMQ_PASSWORD=<secure-password>

# Build de imagens otimizadas
docker-compose -f docker-compose.prod.yml build

# Deploy com zero downtime
docker-compose -f docker-compose.prod.yml up -d
```

**Blue-Green Deployment:**
```bash
#!/bin/bash
# deploy.sh - Blue-Green deployment script

CURRENT_ENV=$(docker-compose ps | grep "auth-service" | grep "Up" | wc -l)

if [ $CURRENT_ENV -gt 0 ]; then
    NEW_ENV="green"
    OLD_ENV="blue"
else
    NEW_ENV="blue"
    OLD_ENV="green"
fi

echo "Deploying to $NEW_ENV environment..."

# Deploy nova versão
docker-compose -f docker-compose.$NEW_ENV.yml up -d

# Health check
sleep 30
HEALTH_STATUS=$(curl -s http://localhost:8001/health | jq -r '.status')

if [ "$HEALTH_STATUS" = "healthy" ]; then
    echo "Health check passed. Switching traffic..."
    
    # Atualizar load balancer
    ./switch-traffic.sh $NEW_ENV
    
    # Parar ambiente antigo
    docker-compose -f docker-compose.$OLD_ENV.yml down
    
    echo "Deployment completed successfully!"
else
    echo "Health check failed. Rolling back..."
    docker-compose -f docker-compose.$NEW_ENV.yml down
    exit 1
fi
```

### Containerização Avançada

**Multi-stage Dockerfile Otimizado:**
```dockerfile
# Build stage
FROM php:8.4-cli as builder

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Runtime stage
FROM php:8.4-apache

RUN docker-php-ext-install pdo_mysql mysqli

# Configurar Apache
COPY apache.conf /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite headers

# Copiar aplicação
WORKDIR /var/www/html
COPY --from=builder /app/vendor ./vendor
COPY . .

# Configurar permissões
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/health || exit 1

EXPOSE 80
```

**Docker Compose para Produção:**
```yaml
version: '3.8'

services:
  auth-service:
    build: 
      context: ./auth-service
      target: runtime
    environment:
      - APP_ENV=production
      - DB_HOST=mysql-primary
      - DB_REPLICA_HOST=mysql-replica
    deploy:
      replicas: 3
      resources:
        limits:
          cpus: '0.5'
          memory: 512M
        reservations:
          cpus: '0.25'
          memory: 256M
      restart_policy:
        condition: on-failure
        delay: 5s
        max_attempts: 3
    healthcheck:
      test: ["CMD", "curl", "-f", "http://localhost/health"]
      interval: 30s
      timeout: 10s
      retries: 3
      start_period: 40s

  mysql-primary:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD_FILE: /run/secrets/mysql_root_password
    volumes:
      - mysql_data:/var/lib/mysql
      - ./mysql/conf.d:/etc/mysql/conf.d
    secrets:
      - mysql_root_password
    deploy:
      placement:
        constraints:
          - node.role == manager

secrets:
  mysql_root_password:
    external: true

volumes:
  mysql_data:
    driver: local
```

### Monitoramento em Produção

**Prometheus Metrics:**
```php
class PrometheusMetrics
{
    private $registry;
    private $requestCounter;
    private $requestDuration;
    
    public function __construct()
    {
        $this->registry = new CollectorRegistry(new InMemory());
        
        $this->requestCounter = $this->registry->getOrRegisterCounter(
            'http_requests_total',
            'Total HTTP requests',
            ['method', 'endpoint', 'status']
        );
        
        $this->requestDuration = $this->registry->getOrRegisterHistogram(
            'http_request_duration_seconds',
            'HTTP request duration',
            ['method', 'endpoint']
        );
    }
    
    public function recordRequest($method, $endpoint, $status, $duration)
    {
        $this->requestCounter->inc([$method, $endpoint, $status]);
        $this->requestDuration->observe($duration, [$method, $endpoint]);
    }
    
    public function getMetrics()
    {
        $renderer = new RenderTextFormat();
        return $renderer->render($this->registry->getMetricFamilySamples());
    }
}
```

**Grafana Dashboard Configuration:**
```json
{
  "dashboard": {
    "title": "Car Dealership System",
    "panels": [
      {
        "title": "Request Rate",
        "type": "graph",
        "targets": [
          {
            "expr": "rate(http_requests_total[5m])",
            "legendFormat": "{{method}} {{endpoint}}"
          }
        ]
      },
      {
        "title": "Response Time",
        "type": "graph",
        "targets": [
          {
            "expr": "histogram_quantile(0.95, rate(http_request_duration_seconds_bucket[5m]))",
            "legendFormat": "95th percentile"
          }
        ]
      },
      {
        "title": "Error Rate",
        "type": "singlestat",
        "targets": [
          {
            "expr": "rate(http_requests_total{status=~\"5..\"}[5m]) / rate(http_requests_total[5m])",
            "legendFormat": "Error Rate"
          }
        ]
      }
    ]
  }
}
```

**Alerting Rules:**
```yaml
groups:
  - name: car-dealership-alerts
    rules:
      - alert: HighErrorRate
        expr: rate(http_requests_total{status=~"5.."}[5m]) / rate(http_requests_total[5m]) > 0.05
        for: 5m
        labels:
          severity: critical
        annotations:
          summary: "High error rate detected"
          description: "Error rate is {{ $value | humanizePercentage }} for the last 5 minutes"
      
      - alert: HighResponseTime
        expr: histogram_quantile(0.95, rate(http_request_duration_seconds_bucket[5m])) > 1
        for: 5m
        labels:
          severity: warning
        annotations:
          summary: "High response time detected"
          description: "95th percentile response time is {{ $value }}s"
      
      - alert: DatabaseConnectionFailure
        expr: mysql_up == 0
        for: 1m
        labels:
          severity: critical
        annotations:
          summary: "Database connection failure"
          description: "MySQL database is not responding"
```

Este documento técnico fornece uma visão abrangente e detalhada do Sistema de Concessionária de Veículos, cobrindo todos os aspectos desde arquitetura até deployment em produção. A implementação segue as melhores práticas da indústria e está preparada para ambientes de produção críticos.

