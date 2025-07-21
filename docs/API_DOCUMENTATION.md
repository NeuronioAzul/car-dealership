# API Documentation - Car Dealership System

## Visão Geral

O sistema de concessionária de veículos é uma aplicação baseada em microserviços que permite a gestão completa de vendas de veículos, desde o cadastro de clientes até a finalização da venda com geração de documentos.

## Arquitetura

### Microserviços

1. **Auth Service** (porta 8081) - Autenticação e autorização
2. **Customer Service** (porta 8082) - Gestão de clientes
3. **Vehicle Service** (porta 8083) - Gestão de veículos
4. **Reservation Service** (porta 8084) - Sistema de reservas
5. **Payment Service** (porta 8085) - Processamento de pagamentos
6. **Sales Service** (porta 8086) - Gestão de vendas
7. **Admin Service** (porta 8087) - Painel administrativo
8. **SAGA Orchestrator** (porta 8088) - Orquestração de transações

### API Gateway

Todas as requisições passam pelo Kong API Gateway na porta 8000, que fornece:

- Roteamento para microserviços
- Autenticação JWT
- Rate limiting
- CORS
- Logs e métricas

## Autenticação

O sistema utiliza JWT (JSON Web Tokens) para autenticação. Existem dois tipos de usuários:

- **customer**: Clientes da concessionária
- **admin**: Administradores do sistema

### Obter Token

```text
POST /api/v1/auth/login
Content-Type: application/json

{
  "email": "cliente@email.com",
  "password": "senha123"
}
```

```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
-H "Content-Type: application/json" \
-d '{
  "email": "cliente@email.com",
  "password": "senha123"
}'
```

**Resposta:**

```json
{
  "success": true,
  "data": {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "expires_in": 3600,
    "user": {
      "id": "uuid",
      "email": "cliente@email.com",
      "role": "customer"
    }
  }
}
```

### Usar Token

Inclua o token no header Authorization:

```text
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

## Endpoints da API

### 1. Autenticação (Auth Service)

#### Registrar Usuário

```bash
POST /api/v1/auth/register
Content-Type: application/json

{
  "name": "João Silva",
  "email": "joao@email.com",
  "password": "senha123",
  "cpf": "12345678901",
  "phone": "11999999999",
  "address": {
    "street": "Rua das Flores, 123",
    "city": "São Paulo",
    "state": "SP",
    "zip_code": "01234-567"
  }
}
```

#### Login

```bash
POST /api/v1/auth/login
Content-Type: application/json

{
  "email": "joao@email.com",
  "password": "senha123"
}
```

#### Validar Token

```bash
POST /api/v1/auth/validate
Authorization: Bearer {token}
```

#### Refresh Token

```bash
POST /api/v1/auth/refresh
Authorization: Bearer {refresh_token}
```

### 2. Gestão de Clientes (Customer Service)

#### Obter Perfil

```bash
GET /api/v1/customer/profile
Authorization: Bearer {token}
```

#### Atualizar Perfil

```bash
PUT /api/v1/customer/profile
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "João Silva Santos",
  "phone": "11888888888",
  "address": {
    "street": "Rua Nova, 456",
    "city": "São Paulo",
    "state": "SP",
    "zip_code": "01234-567"
  }
}
```

#### Excluir Conta

```bash
DELETE /api/v1/customer/profile
Authorization: Bearer {token}
```

### 3. Gestão de Veículos (Vehicle Service)

#### Listar Veículos

```bash
GET /api/v1/vehicles
```

**Parâmetros opcionais:**

- `available_only=true` - Apenas veículos disponíveis

#### Buscar Veículos

```bash
GET /api/v1/vehicles/search?brand=Toyota&model=Corolla&year_from=2020&year_to=2023&price_from=50000&price_to=100000
```

**Parâmetros disponíveis:**

- `brand` - Marca do veículo
- `model` - Modelo do veículo
- `color` - Cor do veículo
- `year_from` / `year_to` - Faixa de ano
- `price_from` / `price_to` - Faixa de preço
- `fuel_type` - Tipo de combustível
- `transmission_type` - Tipo de transmissão

#### Detalhes do Veículo

```bash
GET /api/v1/vehicles/{vehicle_id}
```

### 4. Sistema de Reservas (Reservation Service)

#### Criar Reserva

```bash
POST /api/v1/reservations
Authorization: Bearer {token}
Content-Type: application/json

{
  "vehicle_id": "uuid-do-veiculo"
}
```

#### Listar Reservas

```bash
GET /api/v1/reservations
Authorization: Bearer {token}
```

#### Detalhes da Reserva

```bash
GET /api/v1/reservations/{reservation_id}
Authorization: Bearer {token}
```

#### Cancelar Reserva

```bash
DELETE /api/v1/reservations/{reservation_id}
Authorization: Bearer {token}
```

#### Gerar Código de Pagamento

```bash
POST /api/v1/reservations/generate-payment-code
Authorization: Bearer {token}
Content-Type: application/json

{
  "reservation_id": "uuid-da-reserva"
}
```

### 5. Processamento de Pagamentos (Payment Service)

#### Criar Pagamento

```bash
POST /api/v1/payments/create
Authorization: Bearer {token}
Content-Type: application/json

{
  "reservation_id": "uuid-da-reserva",
  "vehicle_id": "uuid-do-veiculo",
  "payment_code": "CODIGO123",
  "amount": 75000.00
}
```

#### Processar Pagamento

```bash
POST /api/v1/payments
Authorization: Bearer {token}
Content-Type: application/json

{
  "payment_code": "CODIGO123",
  "method": "credit_card"
}
```

**Métodos disponíveis:**

- `credit_card` - Cartão de crédito
- `debit_card` - Cartão de débito
- `pix` - PIX
- `bank_transfer` - Transferência bancária

#### Status do Pagamento

```bash
GET /api/v1/payments/{payment_code}
```

#### Histórico de Pagamentos

```bash
GET /api/v1/payments/my-payments
Authorization: Bearer {token}
```

### 6. Gestão de Vendas (Sales Service)

#### Criar Venda

```bash
POST /api/v1/sales
Authorization: Bearer {token}
Content-Type: application/json

{
  "vehicle_id": "uuid-do-veiculo",
  "reservation_id": "uuid-da-reserva",
  "payment_id": "uuid-do-pagamento",
  "sale_price": 75000.00,
  "customer_data": {
    "name": "João Silva",
    "cpf": "12345678901",
    "email": "joao@email.com",
    "phone": "11999999999",
    "address": "Rua das Flores, 123 - São Paulo/SP"
  },
  "vehicle_data": {
    "brand": "Toyota",
    "model": "Corolla",
    "year": 2022,
    "color": "Branco",
    "license_plate_end": "1234"
  }
}
```

#### Listar Vendas

```bash
GET /api/v1/sales
Authorization: Bearer {token}
```

#### Detalhes da Venda

```bash
GET /api/v1/sales/{sale_id}
Authorization: Bearer {token}
```

#### Download de Documentos

```bash
GET /api/v1/sales/{sale_id}/contract
Authorization: Bearer {token}
```

```bash
GET /api/v1/sales/{sale_id}/invoice
Authorization: Bearer {token}
```

### 7. Painel Administrativo (Admin Service)

#### Dashboard

```bash
GET /api/v1/admin/dashboard
Authorization: Bearer {admin_token}
```

#### Relatório de Vendas

```bash
GET /api/v1/admin/reports/sales?start_date=2024-01-01&end_date=2024-12-31&status=completed
Authorization: Bearer {admin_token}
```

#### Relatório de Clientes

```bash
GET /api/v1/admin/reports/customers?start_date=2024-01-01&end_date=2024-12-31
Authorization: Bearer {admin_token}
```

#### Relatório de Veículos

```bash
GET /api/v1/admin/reports/vehicles?status=available&brand=Toyota
Authorization: Bearer {admin_token}
```

### 8. SAGA Orchestrator

#### Iniciar Compra de Veículo

```bash
POST /api/v1/saga/purchase
Authorization: Bearer {token}
Content-Type: application/json

{
  "vehicle_id": "uuid-do-veiculo",
  "customer_data": {
    "name": "João Silva",
    "cpf": "12345678901",
    "email": "joao@email.com",
    "phone": "11999999999",
    "address": "Rua das Flores, 123 - São Paulo/SP"
  }
}
```

#### Status da Transação

```bash
GET /api/v1/saga/transactions/{transaction_id}
Authorization: Bearer {token}
```

## Fluxo de Compra Completo

1. **Registro/Login do Cliente**

   ```bash
   POST /api/v1/auth/register
   POST /api/v1/auth/login
   ```

2. **Buscar Veículos**

   ```bash
   GET /api/v1/vehicles/search?brand=Toyota
   GET /api/v1/vehicles/{vehicle_id}
   ```

3. **Iniciar Compra via SAGA**

   ```bash
   POST /api/v1/saga/purchase
   ```

4. **Acompanhar Progresso**

   ```bash
   GET /api/v1/saga/transactions/{transaction_id}
   ```

5. **Verificar Venda Finalizada**

   ```bash
   GET /api/v1/sales
   GET /api/v1/sales/{sale_id}/contract
   ```

## Códigos de Status HTTP

- `200` - Sucesso
- `201` - Criado com sucesso
- `400` - Erro na requisição
- `401` - Não autorizado
- `403` - Acesso negado
- `404` - Não encontrado
- `409` - Conflito (ex: veículo já reservado)
- `500` - Erro interno do servidor

## Rate Limiting

- 100 requisições por minuto
- 1000 requisições por hora

## Formato de Resposta

### Sucesso

```json
{
  "success": true,
  "data": {
    // dados da resposta
  }
}
```

### Erro

```json
{
  "error": true,
  "message": "Descrição do erro",
  "code": 400
}
```

## Health Checks

Todos os serviços possuem endpoint de health check:

```bash
GET /api/v1/health
```

## Ambiente de Desenvolvimento

### URLs dos Serviços

- API Gateway: <http://localhost:8000>
- Auth Service: <http://localhost:8081>
- Customer Service: <http://localhost:8082>
- Vehicle Service: <http://localhost:8083>
- Reservation Service: <http://localhost:8084>
- Payment Service: <http://localhost:8085>
- Sales Service: <http://localhost:8086>
- Admin Service: <http://localhost:8087>
- SAGA Orchestrator: <http://localhost:8088>

### Ferramentas de Apoio

- Swagger UI: <http://localhost:8089>
- phpMyAdmin: <http://localhost:8090>
- RabbitMQ Management: <http://localhost:15672>

### Credenciais Padrão

- MySQL: root/root
- RabbitMQ: admin/admin123
