# Testes do Sistema de Concessionária - PHP

Este diretório contém todos os testes para validar o sistema de concessionária de veículos desenvolvido em PHP 8.4 com Clean Architecture.

## Estrutura de Testes

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
│   └── VehicleServiceIntegrationTest.php
├── Feature/                  # Testes de feature (end-to-end)
│   └── CompletePurchaseFlowTest.php
├── Scripts/                  # Scripts auxiliares
│   └── DatabaseSeeder.php
├── composer.json            # Dependências PHP
├── phpunit.xml             # Configuração PHPUnit
├── bootstrap.php           # Bootstrap dos testes
└── run_tests.sh           # Script principal
```

## Executar Todos os Testes

Para executar a suíte completa de testes:

```bash
./tests/run_tests.sh
```

### Opções disponíveis

```bash
./tests/run_tests.sh --help
```

- `--unit`: Executar apenas testes unitários
- `--integration`: Executar apenas testes de integração  
- `--feature`: Executar apenas testes de feature
- `--coverage`: Gerar relatório de cobertura
- `--stop-services`: Parar serviços após os testes

## Testes por Categoria

### 1. Testes Unitários

Testam classes isoladamente sem dependências externas:

```bash
./tests/run_tests.sh --unit
```

**Classes testadas:**

- **User Entity**: Criação, validação, soft delete
- **Vehicle Entity**: Status, transições, validações
- **SagaTransaction**: Estados, compensação, contexto
- **JWT Service**: Geração, validação, refresh de tokens

### 2. Testes de Integração

Testam comunicação entre componentes via HTTP:

```bash
./tests/run_tests.sh --integration
```

**Serviços testados:**

- **Auth Service**: Registro, login, validação JWT
- **Vehicle Service**: Listagem, busca, filtros
- **Customer Service**: Gestão de perfil
- **Reservation Service**: Sistema de reservas
- **Payment Service**: Processamento de pagamentos
- **Sales Service**: Geração de vendas e documentos

### 3. Testes de Feature

Testam fluxos completos end-to-end:

```bash
./tests/run_tests.sh --feature
```

**Fluxos testados:**

- **Compra Completa**: Registro → Login → Busca → Reserva → Pagamento → Venda
- **Orquestração SAGA**: Transações distribuídas com compensação

## Configuração do Ambiente

### Pré-requisitos

- PHP 8.4+
- Composer
- Docker e Docker Compose
- Extensões PHP: pdo_mysql, curl, json

### Instalar Dependências

```bash
cd tests/
composer install
```

### Dependências PHP

- **PHPUnit 10**: Framework de testes
- **Guzzle HTTP**: Cliente HTTP para testes de integração
- **Faker**: Geração de dados de teste
- **DotEnv**: Gerenciamento de variáveis de ambiente

### Serviços Necessários

Os testes assumem que o sistema está rodando:

```bash
docker-compose up -d
```

## Seeding do Banco

Para popular o banco com dados de teste:

```bash
php tests/Scripts/DatabaseSeeder.php
```

**Dados criados:**

- 1 usuário administrador
- 5 clientes de exemplo  
- 100+ veículos realistas
- Dados distribuídos entre os bancos dos microserviços

## Estrutura dos Testes

### Testes Unitários

```php
<?php
namespace Tests\Unit\Auth;

use PHPUnit\Framework\TestCase;
use App\Domain\Entities\User;

class UserEntityTest extends TestCase
{
    public function testUserCreation(): void
    {
        $user = new User(/* ... */);
        $this->assertInstanceOf(User::class, $user);
        // Mais asserções...
    }
}
```

### Testes de Integração

```php
<?php
namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

class AuthServiceIntegrationTest extends TestCase
{
    private Client $httpClient;
    
    public function testUserRegistration(): void
    {
        $response = $this->httpClient->post('/auth/register', [
            'json' => $userData
        ]);
        
        $this->assertEquals(201, $response->getStatusCode());
        // Mais asserções...
    }
}
```

### Testes de Feature

```php
<?php
namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

class CompletePurchaseFlowTest extends TestCase
{
    public function testCompleteVehiclePurchaseFlow(): void
    {
        // 1. Registrar usuário
        $this->registerUser();
        
        // 2. Fazer login
        $this->loginUser();
        
        // 3. Buscar veículo
        $vehicleId = $this->findAvailableVehicle();
        
        // 4-8. Fluxo completo...
    }
}
```

## Cenários de Teste

### Autenticação (Auth Service)

- ✅ Registro com dados válidos
- ✅ Registro com dados inválidos (validação)
- ✅ Login com credenciais corretas
- ✅ Login com credenciais incorretas
- ✅ Validação de token JWT
- ✅ Refresh de token
- ✅ Prevenção de email duplicado

### Gestão de Veículos (Vehicle Service)

- ✅ Listagem com paginação
- ✅ Filtro por disponibilidade
- ✅ Busca avançada (marca, modelo, preço, ano)
- ✅ Detalhes de veículo específico
- ✅ Veículo inexistente (404)
- ✅ Filtros inválidos (400)

### Sistema de Reservas (Reservation Service)

- ✅ Criação de reserva válida
- ✅ Reserva de veículo indisponível
- ✅ Limite de reservas por cliente
- ✅ Expiração automática (24h)
- ✅ Cancelamento de reserva
- ✅ Geração de código de pagamento

### Processamento de Pagamentos (Payment Service)

- ✅ Pagamento com cartão válido
- ✅ Pagamento com dados inválidos
- ✅ Simulação de aprovação/recusa
- ✅ Diferentes métodos de pagamento
- ✅ Consulta de status
- ✅ Histórico do cliente

### Gestão de Vendas (Sales Service)

- ✅ Criação automática após pagamento
- ✅ Geração de documentos PDF
- ✅ Download de contrato
- ✅ Download de nota fiscal
- ✅ Histórico de vendas
- ✅ Acesso seguro (autenticação)

### Padrões SAGA (Orchestrator)

- ✅ Fluxo de sucesso completo
- ✅ Falha em passo intermediário
- ✅ Compensação automática
- ✅ Idempotência de operações
- ✅ Monitoramento de progresso
- ✅ Timeout handling

## Métricas de Qualidade

### Cobertura de Código

```bash
./tests/run_tests.sh --coverage
```

**Metas:**

- Entidades de domínio: 100%
- Serviços de aplicação: > 90%
- Controllers: > 80%
- Cobertura geral: > 85%

### Performance

- **Testes unitários**: < 50ms cada
- **Testes de integração**: < 500ms cada
- **Testes de feature**: < 30s cada
- **Suíte completa**: < 5 minutos

### Confiabilidade

- **Taxa de sucesso**: > 99%
- **Flaky tests**: 0%
- **Determinismo**: 100%

## Relatórios

### Relatório de Testes

Gerado automaticamente em:

```
tests/test_report_YYYYMMDD_HHMMSS.md
```

### Relatório de Cobertura

Gerado em HTML:

```
tests/coverage/index.html
```

### Logs de Execução

Saída colorida com timestamps:

```
[2024-01-15 10:30:15] INFO: Executando testes unitários...
[2024-01-15 10:30:16] ✅ UserEntityTest::testUserCreation
[2024-01-15 10:30:16] ✅ UserEntityTest::testPasswordHashing
```

## Troubleshooting

### Problemas Comuns

1. **Dependências PHP faltando**

   ```bash
   cd tests/
   composer install
   ```

2. **Serviços não respondem**

   ```bash
   docker-compose down
   docker-compose up -d
   sleep 30
   ```

3. **Banco sem dados**

   ```bash
   php tests/Scripts/DatabaseSeeder.php
   ```

4. **Falhas de conexão**
   - Verificar variáveis de ambiente em `phpunit.xml`
   - Confirmar portas dos serviços

### Debug de Testes

```bash
# Executar teste específico
./vendor/bin/phpunit tests/Unit/Auth/UserEntityTest.php

# Executar com verbose
./vendor/bin/phpunit --verbose

# Parar no primeiro erro
./vendor/bin/phpunit --stop-on-failure
```

### Logs dos Serviços

```bash
# Auth Service
docker-compose logs auth-service

# Vehicle Service  
docker-compose logs vehicle-service

# SAGA Orchestrator
docker-compose logs saga-orchestrator
```

## Extensões Futuras

### Testes de Performance

- Load testing com múltiplos usuários
- Stress testing dos endpoints
- Análise de bottlenecks
- Métricas de throughput

### Testes de Segurança

- Validação de autenticação
- Teste de autorização
- Injection attacks
- Rate limiting

### Testes de Contrato

- Pact testing entre microserviços
- Schema validation
- API versioning
- Backward compatibility

### Automação CI/CD

- GitHub Actions
- Pipeline de testes
- Deploy automático
- Quality gates
