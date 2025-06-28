

# Sistema de Concessionária de Veículos - Manual de Uso da API

**Versão:** 1.0.0  
**Data:** 13 de junho de 2025  
**Autor:** Mauro Rocha Tavares  
**Base URL:** http://localhost:8000/api/v1  
**Documentação Interativa:** http://localhost:8089

---

## Introdução à API

O Sistema de Concessionária de Veículos oferece uma API REST completa e moderna, desenvolvida seguindo os princípios de Clean Architecture e padrões de microserviços. A API fornece acesso programático a todas as funcionalidades do sistema, desde autenticação de usuários até processamento completo de vendas de veículos.

A API é projetada para ser intuitiva, consistente e robusta, oferecendo endpoints bem documentados com exemplos práticos de uso. Todos os endpoints seguem convenções REST padrão e retornam respostas em formato JSON estruturado. O sistema implementa autenticação JWT para segurança, rate limiting para proteção contra abuso, e validação rigorosa de dados de entrada.

### Características Principais da API

**Arquitetura RESTful:** Todos os endpoints seguem princípios REST com uso apropriado de métodos HTTP (GET, POST, PUT, DELETE), códigos de status HTTP padronizados e URLs semânticas que refletem a hierarquia de recursos.

**Autenticação JWT:** Sistema de autenticação baseado em tokens JWT (JSON Web Tokens) com suporte a refresh tokens, expiração configurável e validação rigorosa. Os tokens incluem informações de usuário e permissões, permitindo controle de acesso granular.

**Rate Limiting:** Proteção contra abuso com limitação de 100 requisições por minuto por IP, com headers informativos sobre limites restantes e tempo de reset. Implementado através do Kong API Gateway.

**Validação de Dados:** Validação abrangente de todos os dados de entrada com mensagens de erro descritivas, verificação de tipos de dados, validação de formatos (CPF, email, telefone) e sanitização de entrada.

**Paginação Inteligente:** Suporte a paginação em endpoints de listagem com parâmetros flexíveis (page, limit, offset), metadados de paginação nas respostas e otimização de performance para grandes conjuntos de dados.

**Filtros Avançados:** Capacidade de filtrar resultados por múltiplos critérios, busca textual, filtros por faixa de valores (preço, ano) e combinação de filtros para consultas complexas.

**CORS Habilitado:** Suporte completo a Cross-Origin Resource Sharing (CORS) para integração com aplicações frontend de diferentes domínios, com configuração flexível de origens permitidas.

**Documentação Interativa:** Interface Swagger UI completa para exploração e teste da API, com exemplos de requisições e respostas, esquemas de dados detalhados e possibilidade de executar chamadas diretamente na interface.

## Autenticação e Autorização

### Fluxo de Autenticação

O sistema implementa autenticação baseada em JWT com dois tipos de tokens: access token (curta duração) e refresh token (longa duração). Este modelo garante segurança adequada enquanto mantém boa experiência do usuário.

**Registro de Usuário:**
O primeiro passo é registrar um novo usuário no sistema. O endpoint de registro valida todos os dados fornecidos, verifica unicidade do email e CPF, e cria o usuário com senha criptografada usando bcrypt.

```http
POST /auth/register
Content-Type: application/json

{
  "name": "João Silva Santos",
  "email": "joao.silva@email.com",
  "password": "minhasenha123",
  "cpf": "12345678901",
  "phone": "11999887766",
  "address": {
    "street": "Rua das Flores, 123",
    "city": "São Paulo",
    "state": "SP",
    "zip_code": "01234-567"
  }
}
```

**Resposta de Sucesso (201 Created):**
```json
{
  "success": true,
  "message": "Usuário registrado com sucesso",
  "data": {
    "user": {
      "id": "uuid-do-usuario",
      "name": "João Silva Santos",
      "email": "joao.silva@email.com",
      "role": "customer",
      "created_at": "2025-06-13T10:30:00Z"
    }
  }
}
```

**Login de Usuário:**
Após o registro, o usuário pode fazer login fornecendo email e senha. O sistema retorna access token e refresh token para autenticação subsequente.

```http
POST /auth/login
Content-Type: application/json

{
  "email": "joao.silva@email.com",
  "password": "minhasenha123"
}
```

**Resposta de Sucesso (200 OK):**
```json
{
  "success": true,
  "message": "Login realizado com sucesso",
  "data": {
    "access_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "refresh_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "token_type": "Bearer",
    "expires_in": 3600,
    "user": {
      "id": "uuid-do-usuario",
      "name": "João Silva Santos",
      "email": "joao.silva@email.com",
      "role": "customer"
    }
  }
}
```

### Uso de Tokens

**Incluindo Token nas Requisições:**
Para acessar endpoints protegidos, inclua o access token no header Authorization usando o formato Bearer:

```http
GET /customer/profile
Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

**Renovação de Token:**
Quando o access token expira, use o refresh token para obter um novo access token sem necessidade de novo login:

```http
POST /auth/refresh
Content-Type: application/json

{
  "refresh_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
}
```

**Validação de Token:**
Para verificar se um token é válido e obter informações do usuário:

```http
GET /auth/validate
Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

### Controle de Acesso

O sistema implementa controle de acesso baseado em roles (RBAC) com dois tipos principais de usuários:

**Customer (Cliente):** Pode visualizar veículos, criar reservas, processar pagamentos, visualizar suas próprias vendas e gerenciar seu perfil pessoal.

**Admin (Administrador):** Possui todas as permissões de cliente, além de acesso ao painel administrativo, relatórios completos, gestão de todos os usuários e veículos, e visualização de todas as transações do sistema.

## Gestão de Veículos

### Listagem de Veículos

O endpoint de listagem de veículos oferece funcionalidades avançadas de paginação, filtros e ordenação, permitindo consultas eficientes mesmo com grandes catálogos de veículos.

**Listagem Básica:**
```http
GET /vehicles
```

**Listagem com Paginação:**
```http
GET /vehicles?page=1&limit=20&offset=0
```

**Filtros Disponíveis:**
```http
GET /vehicles?brand=Toyota&model=Corolla&year_min=2020&year_max=2024&price_min=50000&price_max=100000&fuel_type=flex&transmission=automatic&color=branco&status=available
```

**Resposta de Sucesso (200 OK):**
```json
{
  "success": true,
  "data": {
    "vehicles": [
      {
        "id": "uuid-do-veiculo",
        "brand": "Toyota",
        "model": "Corolla",
        "manufacturing_year": 2023,
        "model_year": 2024,
        "color": "Branco",
        "mileage": 15000,
        "fuel_type": "Flex",
        "transmission_type": "Automático",
        "price": 85000,
        "status": "available",
        "description": "Toyota Corolla 2024 em excelente estado",
        "features": [
          "Ar condicionado",
          "Direção hidráulica",
          "Vidros elétricos",
          "Central multimídia"
        ],
        "created_at": "2025-06-13T10:00:00Z",
        "updated_at": "2025-06-13T10:00:00Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 20,
      "total": 150,
      "total_pages": 8,
      "has_next": true,
      "has_previous": false
    }
  }
}
```

### Busca Avançada de Veículos

O sistema oferece busca textual avançada que procura em múltiplos campos simultaneamente:

```http
GET /vehicles/search?q=toyota+corolla+2023&sort=price_asc&limit=10
```

**Parâmetros de Busca:**
- **q:** Termo de busca (procura em marca, modelo, descrição)
- **sort:** Ordenação (price_asc, price_desc, year_asc, year_desc, mileage_asc, mileage_desc)
- **limit:** Número máximo de resultados
- **available_only:** true/false para mostrar apenas veículos disponíveis

### Detalhes de Veículo Específico

Para obter informações completas de um veículo específico:

```http
GET /vehicles/{vehicle_id}
```

**Resposta de Sucesso (200 OK):**
```json
{
  "success": true,
  "data": {
    "vehicle": {
      "id": "uuid-do-veiculo",
      "brand": "Toyota",
      "model": "Corolla",
      "manufacturing_year": 2023,
      "model_year": 2024,
      "color": "Branco",
      "mileage": 15000,
      "fuel_type": "Flex",
      "transmission_type": "Automático",
      "price": 85000,
      "status": "available",
      "description": "Toyota Corolla 2024 em excelente estado de conservação. Veículo revisado, com manual e chave reserva.",
      "features": [
        "Ar condicionado digital",
        "Direção hidráulica",
        "Vidros elétricos",
        "Travas elétricas",
        "Central multimídia",
        "Câmera de ré",
        "Sensor de estacionamento"
      ],
      "images": [
        "https://example.com/images/vehicle1_front.jpg",
        "https://example.com/images/vehicle1_side.jpg"
      ],
      "created_at": "2025-06-13T10:00:00Z",
      "updated_at": "2025-06-13T10:00:00Z"
    }
  }
}
```

## Sistema de Reservas

### Criação de Reservas

O sistema de reservas permite que clientes reservem veículos por até 24 horas, garantindo tempo suficiente para finalizar a compra. Cada cliente pode ter no máximo 3 reservas ativas simultaneamente.

**Criar Nova Reserva:**
```http
POST /reservations
Authorization: Bearer {access_token}
Content-Type: application/json

{
  "vehicle_id": "uuid-do-veiculo"
}
```

**Resposta de Sucesso (201 Created):**
```json
{
  "success": true,
  "message": "Reserva criada com sucesso",
  "data": {
    "reservation": {
      "id": "uuid-da-reserva",
      "vehicle_id": "uuid-do-veiculo",
      "customer_id": "uuid-do-cliente",
      "status": "active",
      "expires_at": "2025-06-14T10:30:00Z",
      "created_at": "2025-06-13T10:30:00Z",
      "vehicle": {
        "brand": "Toyota",
        "model": "Corolla",
        "year": 2024,
        "price": 85000
      }
    }
  }
}
```

### Listagem de Reservas do Cliente

Para visualizar todas as reservas do cliente autenticado:

```http
GET /reservations
Authorization: Bearer {access_token}
```

**Parâmetros Opcionais:**
- **status:** active, expired, cancelled
- **page:** Número da página
- **limit:** Itens por página

**Resposta de Sucesso (200 OK):**
```json
{
  "success": true,
  "data": {
    "reservations": [
      {
        "id": "uuid-da-reserva",
        "vehicle_id": "uuid-do-veiculo",
        "status": "active",
        "expires_at": "2025-06-14T10:30:00Z",
        "created_at": "2025-06-13T10:30:00Z",
        "vehicle": {
          "brand": "Toyota",
          "model": "Corolla",
          "year": 2024,
          "price": 85000,
          "color": "Branco"
        }
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 10,
      "total": 5,
      "total_pages": 1
    }
  }
}
```

### Cancelamento de Reservas

Para cancelar uma reserva ativa:

```http
DELETE /reservations/{reservation_id}
Authorization: Bearer {access_token}
```

**Resposta de Sucesso (200 OK):**
```json
{
  "success": true,
  "message": "Reserva cancelada com sucesso",
  "data": {
    "reservation": {
      "id": "uuid-da-reserva",
      "status": "cancelled",
      "cancelled_at": "2025-06-13T11:00:00Z"
    }
  }
}
```

### Geração de Código de Pagamento

Para prosseguir com a compra, é necessário gerar um código de pagamento único:

```http
POST /reservations/generate-payment-code
Authorization: Bearer {access_token}
Content-Type: application/json

{
  "reservation_id": "uuid-da-reserva"
}
```

**Resposta de Sucesso (200 OK):**
```json
{
  "success": true,
  "message": "Código de pagamento gerado com sucesso",
  "data": {
    "payment_code": "PAY-2025061310300001",
    "amount": 85000,
    "expires_at": "2025-06-14T10:30:00Z",
    "reservation": {
      "id": "uuid-da-reserva",
      "vehicle": {
        "brand": "Toyota",
        "model": "Corolla",
        "year": 2024
      }
    }
  }
}
```

## Processamento de Pagamentos

### Métodos de Pagamento Suportados

O sistema suporta múltiplos métodos de pagamento através de um gateway fictício configurável:

- **Cartão de Crédito:** Visa, Mastercard, American Express
- **Cartão de Débito:** Visa Débito, Mastercard Débito
- **PIX:** Pagamento instantâneo
- **Transferência Bancária:** TED/DOC

### Processamento de Pagamento

Para processar um pagamento usando código gerado anteriormente:

**Pagamento com Cartão de Crédito:**
```http
POST /payments
Authorization: Bearer {access_token}
Content-Type: application/json

{
  "payment_code": "PAY-2025061310300001",
  "payment_method": "credit_card",
  "card_data": {
    "number": "4111111111111111",
    "holder_name": "JOAO SILVA SANTOS",
    "expiry_month": "12",
    "expiry_year": "2025",
    "cvv": "123"
  }
}
```

**Pagamento via PIX:**
```http
POST /payments
Authorization: Bearer {access_token}
Content-Type: application/json

{
  "payment_code": "PAY-2025061310300001",
  "payment_method": "pix",
  "pix_data": {
    "pix_key": "joao.silva@email.com"
  }
}
```

**Resposta de Sucesso (200 OK):**
```json
{
  "success": true,
  "message": "Pagamento processado com sucesso",
  "data": {
    "payment": {
      "id": "uuid-do-pagamento",
      "payment_code": "PAY-2025061310300001",
      "amount": 85000,
      "payment_method": "credit_card",
      "status": "completed",
      "transaction_id": "TXN-2025061310300001",
      "processed_at": "2025-06-13T10:35:00Z",
      "gateway_response": {
        "authorization_code": "AUTH123456",
        "gateway_transaction_id": "GTW789012"
      }
    }
  }
}
```

### Consulta de Status de Pagamento

Para verificar o status de um pagamento:

```http
GET /payments/{payment_code}
Authorization: Bearer {access_token}
```

**Resposta de Sucesso (200 OK):**
```json
{
  "success": true,
  "data": {
    "payment": {
      "id": "uuid-do-pagamento",
      "payment_code": "PAY-2025061310300001",
      "amount": 85000,
      "payment_method": "credit_card",
      "status": "completed",
      "created_at": "2025-06-13T10:30:00Z",
      "processed_at": "2025-06-13T10:35:00Z"
    }
  }
}
```

### Histórico de Pagamentos

Para visualizar histórico de pagamentos do cliente:

```http
GET /payments/my-payments
Authorization: Bearer {access_token}
```

**Parâmetros Opcionais:**
- **status:** pending, processing, completed, failed
- **payment_method:** credit_card, debit_card, pix, bank_transfer
- **date_from:** Data inicial (YYYY-MM-DD)
- **date_to:** Data final (YYYY-MM-DD)

## Gestão de Vendas

### Listagem de Vendas do Cliente

Após pagamento aprovado, uma venda é criada automaticamente. Para visualizar vendas:

```http
GET /sales
Authorization: Bearer {access_token}
```

**Resposta de Sucesso (200 OK):**
```json
{
  "success": true,
  "data": {
    "sales": [
      {
        "id": "uuid-da-venda",
        "vehicle": {
          "brand": "Toyota",
          "model": "Corolla",
          "year": 2024,
          "color": "Branco"
        },
        "total_amount": 85000,
        "status": "completed",
        "sale_date": "2025-06-13T10:35:00Z",
        "payment": {
          "method": "credit_card",
          "status": "completed"
        },
        "documents": {
          "contract_available": true,
          "invoice_available": true
        }
      }
    ]
  }
}
```

### Detalhes de Venda Específica

Para obter detalhes completos de uma venda:

```http
GET /sales/{sale_id}
Authorization: Bearer {access_token}
```

**Resposta de Sucesso (200 OK):**
```json
{
  "success": true,
  "data": {
    "sale": {
      "id": "uuid-da-venda",
      "vehicle": {
        "id": "uuid-do-veiculo",
        "brand": "Toyota",
        "model": "Corolla",
        "manufacturing_year": 2023,
        "model_year": 2024,
        "color": "Branco",
        "mileage": 15000,
        "fuel_type": "Flex",
        "transmission_type": "Automático",
        "price": 85000
      },
      "customer": {
        "name": "João Silva Santos",
        "cpf": "12345678901",
        "email": "joao.silva@email.com",
        "phone": "11999887766",
        "address": "Rua das Flores, 123 - São Paulo/SP"
      },
      "payment": {
        "method": "credit_card",
        "amount": 85000,
        "status": "completed",
        "transaction_id": "TXN-2025061310300001"
      },
      "total_amount": 85000,
      "status": "completed",
      "sale_date": "2025-06-13T10:35:00Z",
      "documents": {
        "contract_generated": true,
        "invoice_generated": true,
        "contract_url": "/sales/uuid-da-venda/contract",
        "invoice_url": "/sales/uuid-da-venda/invoice"
      }
    }
  }
}
```

### Download de Documentos

O sistema gera automaticamente contrato de compra/venda e nota fiscal em PDF:

**Download do Contrato:**
```http
GET /sales/{sale_id}/contract
Authorization: Bearer {access_token}
```

**Download da Nota Fiscal:**
```http
GET /sales/{sale_id}/invoice
Authorization: Bearer {access_token}
```

**Resposta:** Arquivo PDF com headers apropriados:
```
Content-Type: application/pdf
Content-Disposition: attachment; filename="contrato_venda_uuid-da-venda.pdf"
```

## Gestão de Perfil do Cliente

### Visualização do Perfil

Para visualizar dados do perfil do cliente autenticado:

```http
GET /customer/profile
Authorization: Bearer {access_token}
```

**Resposta de Sucesso (200 OK):**
```json
{
  "success": true,
  "data": {
    "customer": {
      "id": "uuid-do-cliente",
      "name": "João Silva Santos",
      "email": "joao.silva@email.com",
      "cpf": "12345678901",
      "phone": "11999887766",
      "address": {
        "street": "Rua das Flores, 123",
        "city": "São Paulo",
        "state": "SP",
        "zip_code": "01234-567"
      },
      "created_at": "2025-06-13T10:00:00Z",
      "updated_at": "2025-06-13T10:00:00Z"
    }
  }
}
```

### Atualização do Perfil

Para atualizar dados do perfil:

```http
PUT /customer/profile
Authorization: Bearer {access_token}
Content-Type: application/json

{
  "name": "João Silva Santos Junior",
  "phone": "11888776655",
  "address": {
    "street": "Rua das Palmeiras, 456",
    "city": "São Paulo",
    "state": "SP",
    "zip_code": "01234-890"
  }
}
```

**Resposta de Sucesso (200 OK):**
```json
{
  "success": true,
  "message": "Perfil atualizado com sucesso",
  "data": {
    "customer": {
      "id": "uuid-do-cliente",
      "name": "João Silva Santos Junior",
      "email": "joao.silva@email.com",
      "cpf": "12345678901",
      "phone": "11888776655",
      "address": {
        "street": "Rua das Palmeiras, 456",
        "city": "São Paulo",
        "state": "SP",
        "zip_code": "01234-890"
      },
      "updated_at": "2025-06-13T11:00:00Z"
    }
  }
}
```

### Exclusão de Conta

Para excluir conta do cliente (soft delete):

```http
DELETE /customer/profile
Authorization: Bearer {access_token}
```

**Resposta de Sucesso (200 OK):**
```json
{
  "success": true,
  "message": "Conta excluída com sucesso"
}
```

## Painel Administrativo

### Dashboard Administrativo

Acesso exclusivo para usuários com role "admin":

```http
GET /admin/dashboard
Authorization: Bearer {admin_access_token}
```

**Resposta de Sucesso (200 OK):**
```json
{
  "success": true,
  "data": {
    "dashboard": {
      "users": {
        "total": 1250,
        "active": 1180,
        "new_this_month": 85,
        "by_role": {
          "customers": 1200,
          "admins": 50
        }
      },
      "vehicles": {
        "total": 450,
        "available": 320,
        "reserved": 25,
        "sold": 105,
        "by_brand": {
          "Toyota": 85,
          "Honda": 70,
          "Volkswagen": 65
        },
        "average_price": 75000
      },
      "reservations": {
        "active": 25,
        "expired_today": 3,
        "conversion_rate": 0.78
      },
      "payments": {
        "total_processed": 8500000,
        "success_rate": 0.92,
        "this_month": 1200000
      },
      "sales": {
        "total": 105,
        "this_month": 12,
        "total_revenue": 8925000,
        "monthly_breakdown": [
          {"month": "2025-01", "sales": 8, "revenue": 680000},
          {"month": "2025-02", "sales": 12, "revenue": 1020000},
          {"month": "2025-03", "sales": 15, "revenue": 1275000}
        ]
      }
    }
  }
}
```

### Relatórios Administrativos

**Relatório de Vendas:**
```http
GET /admin/reports/sales?date_from=2025-01-01&date_to=2025-06-13&group_by=month
Authorization: Bearer {admin_access_token}
```

**Relatório de Clientes:**
```http
GET /admin/reports/customers?registration_date_from=2025-01-01&include_purchases=true
Authorization: Bearer {admin_access_token}
```

**Relatório de Veículos:**
```http
GET /admin/reports/vehicles?status=all&group_by=brand
Authorization: Bearer {admin_access_token}
```

## Orquestração SAGA

### Iniciar Transação de Compra

O SAGA Orchestrator coordena transações distribuídas complexas:

```http
POST /saga/purchase
Authorization: Bearer {access_token}
Content-Type: application/json

{
  "vehicle_id": "uuid-do-veiculo",
  "customer_data": {
    "name": "João Silva Santos",
    "cpf": "12345678901",
    "email": "joao.silva@email.com",
    "phone": "11999887766",
    "address": "Rua das Flores, 123 - São Paulo/SP"
  },
  "payment_data": {
    "method": "credit_card",
    "card_data": {
      "number": "4111111111111111",
      "holder_name": "JOAO SILVA SANTOS",
      "expiry_month": "12",
      "expiry_year": "2025",
      "cvv": "123"
    }
  }
}
```

**Resposta de Sucesso (201 Created):**
```json
{
  "success": true,
  "message": "Transação SAGA iniciada",
  "data": {
    "transaction_id": "SAGA-2025061310300001",
    "status": "started",
    "steps": [
      "create_reservation",
      "generate_payment_code",
      "process_payment",
      "create_sale",
      "update_vehicle_status"
    ],
    "current_step": "create_reservation",
    "estimated_completion": "2025-06-13T10:35:00Z"
  }
}
```

### Monitoramento de Transação SAGA

Para acompanhar o progresso de uma transação:

```http
GET /saga/transactions/{transaction_id}
Authorization: Bearer {access_token}
```

**Resposta de Sucesso (200 OK):**
```json
{
  "success": true,
  "data": {
    "transaction": {
      "id": "SAGA-2025061310300001",
      "status": "completed",
      "started_at": "2025-06-13T10:30:00Z",
      "completed_at": "2025-06-13T10:34:30Z",
      "steps": [
        {
          "name": "create_reservation",
          "status": "completed",
          "started_at": "2025-06-13T10:30:00Z",
          "completed_at": "2025-06-13T10:30:15Z",
          "result": {
            "reservation_id": "uuid-da-reserva"
          }
        },
        {
          "name": "generate_payment_code",
          "status": "completed",
          "started_at": "2025-06-13T10:30:15Z",
          "completed_at": "2025-06-13T10:30:30Z",
          "result": {
            "payment_code": "PAY-2025061310300001"
          }
        },
        {
          "name": "process_payment",
          "status": "completed",
          "started_at": "2025-06-13T10:30:30Z",
          "completed_at": "2025-06-13T10:33:00Z",
          "result": {
            "payment_id": "uuid-do-pagamento",
            "transaction_id": "TXN-2025061310300001"
          }
        },
        {
          "name": "create_sale",
          "status": "completed",
          "started_at": "2025-06-13T10:33:00Z",
          "completed_at": "2025-06-13T10:34:00Z",
          "result": {
            "sale_id": "uuid-da-venda"
          }
        },
        {
          "name": "update_vehicle_status",
          "status": "completed",
          "started_at": "2025-06-13T10:34:00Z",
          "completed_at": "2025-06-13T10:34:30Z",
          "result": {
            "vehicle_status": "sold"
          }
        }
      ],
      "final_result": {
        "sale_id": "uuid-da-venda",
        "payment_id": "uuid-do-pagamento",
        "total_amount": 85000
      }
    }
  }
}
```

## Tratamento de Erros

### Códigos de Status HTTP

A API utiliza códigos de status HTTP padrão para indicar sucesso ou falha das requisições:

- **200 OK:** Requisição bem-sucedida
- **201 Created:** Recurso criado com sucesso
- **400 Bad Request:** Dados de entrada inválidos
- **401 Unauthorized:** Token de autenticação ausente ou inválido
- **403 Forbidden:** Usuário não tem permissão para acessar o recurso
- **404 Not Found:** Recurso não encontrado
- **409 Conflict:** Conflito com estado atual do recurso
- **422 Unprocessable Entity:** Dados válidos mas não processáveis
- **429 Too Many Requests:** Rate limit excedido
- **500 Internal Server Error:** Erro interno do servidor

### Formato de Resposta de Erro

Todas as respostas de erro seguem formato consistente:

```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Os dados fornecidos são inválidos",
    "details": [
      {
        "field": "email",
        "message": "Email já está em uso"
      },
      {
        "field": "cpf",
        "message": "CPF deve ter 11 dígitos"
      }
    ]
  },
  "timestamp": "2025-06-13T10:30:00Z",
  "path": "/auth/register"
}
```

### Códigos de Erro Específicos

**AUTH_001:** Token JWT inválido ou expirado
**AUTH_002:** Credenciais de login incorretas
**AUTH_003:** Usuário não encontrado
**AUTH_004:** Email já está em uso
**AUTH_005:** CPF já está em uso

**VEHICLE_001:** Veículo não encontrado
**VEHICLE_002:** Veículo não está disponível
**VEHICLE_003:** Filtros de busca inválidos

**RESERVATION_001:** Reserva não encontrada
**RESERVATION_002:** Limite de reservas excedido
**RESERVATION_003:** Reserva já expirada
**RESERVATION_004:** Veículo já reservado

**PAYMENT_001:** Código de pagamento inválido
**PAYMENT_002:** Dados do cartão inválidos
**PAYMENT_003:** Pagamento recusado pelo gateway
**PAYMENT_004:** Pagamento já processado

**SALE_001:** Venda não encontrada
**SALE_002:** Documento não disponível
**SALE_003:** Acesso negado à venda

**SAGA_001:** Transação SAGA não encontrada
**SAGA_002:** Falha na execução do passo
**SAGA_003:** Compensação necessária
**SAGA_004:** Timeout na execução

## Rate Limiting

### Limites Implementados

O sistema implementa rate limiting para proteger contra abuso:

- **Limite Geral:** 100 requisições por minuto por IP
- **Limite por Hora:** 1000 requisições por hora por IP
- **Endpoints de Autenticação:** 10 tentativas por minuto por IP
- **Endpoints de Pagamento:** 5 tentativas por minuto por usuário

### Headers de Rate Limiting

Todas as respostas incluem headers informativos sobre rate limiting:

```
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1623589800
```

### Resposta de Rate Limit Excedido

Quando o limite é excedido, a API retorna:

```http
HTTP/1.1 429 Too Many Requests
Content-Type: application/json

{
  "success": false,
  "error": {
    "code": "RATE_LIMIT_EXCEEDED",
    "message": "Muitas requisições. Tente novamente em 60 segundos.",
    "retry_after": 60
  }
}
```

## Exemplos de Integração

### Fluxo Completo de Compra

Exemplo de integração completa para compra de veículo:

```javascript
// 1. Autenticação
const loginResponse = await fetch('/api/v1/auth/login', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    email: 'cliente@email.com',
    password: 'senha123'
  })
});

const { access_token } = await loginResponse.json();

// 2. Buscar veículos
const vehiclesResponse = await fetch('/api/v1/vehicles?available_only=true', {
  headers: { 'Authorization': `Bearer ${access_token}` }
});

const vehicles = await vehiclesResponse.json();
const selectedVehicle = vehicles.data.vehicles[0];

// 3. Criar reserva
const reservationResponse = await fetch('/api/v1/reservations', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${access_token}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    vehicle_id: selectedVehicle.id
  })
});

const reservation = await reservationResponse.json();

// 4. Gerar código de pagamento
const paymentCodeResponse = await fetch('/api/v1/reservations/generate-payment-code', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${access_token}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    reservation_id: reservation.data.reservation.id
  })
});

const paymentCode = await paymentCodeResponse.json();

// 5. Processar pagamento
const paymentResponse = await fetch('/api/v1/payments', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${access_token}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    payment_code: paymentCode.data.payment_code,
    payment_method: 'credit_card',
    card_data: {
      number: '4111111111111111',
      holder_name: 'CLIENTE TESTE',
      expiry_month: '12',
      expiry_year: '2025',
      cvv: '123'
    }
  })
});

const payment = await paymentResponse.json();

// 6. Verificar venda criada
const salesResponse = await fetch('/api/v1/sales', {
  headers: { 'Authorization': `Bearer ${access_token}` }
});

const sales = await salesResponse.json();
console.log('Compra finalizada:', sales.data.sales[0]);
```

### Integração com SAGA

Exemplo de uso do SAGA Orchestrator:

```javascript
// Iniciar transação SAGA
const sagaResponse = await fetch('/api/v1/saga/purchase', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${access_token}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    vehicle_id: 'uuid-do-veiculo',
    customer_data: {
      name: 'Cliente Teste',
      cpf: '12345678901',
      email: 'cliente@email.com',
      phone: '11999887766',
      address: 'Rua Teste, 123 - São Paulo/SP'
    },
    payment_data: {
      method: 'credit_card',
      card_data: {
        number: '4111111111111111',
        holder_name: 'CLIENTE TESTE',
        expiry_month: '12',
        expiry_year: '2025',
        cvv: '123'
      }
    }
  })
});

const saga = await sagaResponse.json();
const transactionId = saga.data.transaction_id;

// Monitorar progresso
const monitorSaga = async () => {
  const statusResponse = await fetch(`/api/v1/saga/transactions/${transactionId}`, {
    headers: { 'Authorization': `Bearer ${access_token}` }
  });
  
  const status = await statusResponse.json();
  
  if (status.data.transaction.status === 'completed') {
    console.log('Transação concluída:', status.data.transaction.final_result);
  } else if (status.data.transaction.status === 'failed') {
    console.log('Transação falhou:', status.data.transaction.failure_reason);
  } else {
    console.log('Progresso:', status.data.transaction.current_step);
    setTimeout(monitorSaga, 2000); // Verificar novamente em 2 segundos
  }
};

monitorSaga();
```

Este manual fornece uma visão abrangente de como utilizar a API do Sistema de Concessionária de Veículos. Para informações mais detalhadas sobre endpoints específicos, consulte a documentação interativa Swagger UI disponível em http://localhost:8089.

