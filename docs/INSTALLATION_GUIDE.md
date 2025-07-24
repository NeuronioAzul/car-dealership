# Sistema de Concessionária de Veículos - Guia de Instalação

---

## Visão Geral

Este documento fornece instruções detalhadas para instalação e configuração do Sistema de Concessionária de Veículos, uma aplicação completa desenvolvida com arquitetura de microserviços em PHP 8.4. O sistema implementa Clean Architecture, padrão SAGA para transações distribuídas, e utiliza Kong como API Gateway para roteamento e autenticação.

O sistema é composto por 8 microserviços independentes que trabalham em conjunto para fornecer uma solução completa de gestão de concessionária, incluindo autenticação de usuários, catálogo de veículos, sistema de reservas, processamento de pagamentos, gestão de vendas, painel administrativo e orquestração de transações distribuídas.

## Arquitetura do Sistema

### Componentes Principais

O sistema utiliza uma arquitetura de microserviços containerizada com Docker, onde cada serviço possui sua própria responsabilidade e banco de dados. A comunicação entre serviços é realizada através de APIs REST e mensageria assíncrona com RabbitMQ. O Kong API Gateway atua como ponto único de entrada, fornecendo roteamento, autenticação JWT, rate limiting e CORS.

### Microserviços Implementados

**Auth Service (Porta 8081):** Responsável pela autenticação e autorização de usuários, gerenciamento de tokens JWT, registro de novos usuários e controle de acesso baseado em roles. Utiliza bcrypt para hash de senhas e implementa refresh tokens para segurança aprimorada.

**Customer Service (Porta 8082):** Gerencia perfis de clientes, permitindo visualização, atualização e exclusão de dados pessoais. Implementa soft delete para preservar histórico e validação rigorosa de dados como CPF e telefone.

**Vehicle Service (Porta 8083):** Mantém o catálogo de veículos com funcionalidades de listagem, busca avançada com múltiplos filtros, controle de status (disponível, reservado, vendido) e gestão de estoque. Suporta paginação e ordenação flexível.

**Reservation Service (Porta 8084):** Implementa sistema de reservas com expiração automática de 24 horas, limite de 3 reservas ativas por cliente, geração de códigos de pagamento únicos e integração com o sistema de pagamentos.

**Payment Service (Porta 8085):** Processa pagamentos através de gateway fictício configurável, suporta múltiplos métodos de pagamento (cartão de crédito, débito, PIX, transferência), implementa retry automático e logging detalhado de transações.

**Sales Service (Porta 8086):** Gerencia vendas finalizadas, gera automaticamente documentos PDF (contrato de compra/venda e nota fiscal), mantém histórico de vendas por cliente e fornece download seguro de documentos.

**Admin Service (Porta 8087):** Fornece painel administrativo com dashboard em tempo real, relatórios detalhados de vendas, clientes e veículos, estatísticas de performance e acesso a dados consolidados de todos os microserviços.

**SAGA Orchestrator (Porta 8088):** Coordena transações distribuídas usando padrão SAGA, implementa compensação automática em caso de falhas, monitora progresso de transações complexas e garante consistência eventual entre serviços.

### Infraestrutura de Apoio

**Kong API Gateway (Porta 8000):** Ponto único de entrada para todas as APIs, implementa autenticação JWT, rate limiting (100 requisições por minuto), CORS para todas as origens, limitação de tamanho de requisição (10MB) e roteamento inteligente.

**MySQL 8:** Sistema de gerenciamento de banco de dados relacional com bancos separados para cada microserviço, garantindo isolamento de dados e escalabilidade independente. Configurado com charset utf8mb4 para suporte completo a Unicode.

**RabbitMQ 3 (Porta 5672/15672):** Message broker para comunicação assíncrona entre serviços, implementa padrão publish/subscribe, filas duráveis para garantir entrega de mensagens e interface de gerenciamento web.

**phpMyAdmin (Porta 8090):** Interface web para administração dos bancos de dados MySQL, facilitando visualização de dados, execução de queries e monitoramento de performance.

**Swagger UI (Porta 8089):** Documentação interativa da API com interface para teste de endpoints, exemplos de requisições e respostas, e especificação OpenAPI 3.0 completa.

## Pré-requisitos do Sistema

### Requisitos de Hardware

Para desenvolvimento e testes locais, recomenda-se um sistema com pelo menos 8GB de RAM, 4 núcleos de CPU e 20GB de espaço livre em disco. Para ambiente de produção, os requisitos podem variar dependendo do volume de transações esperado, mas recomenda-se pelo menos 16GB de RAM e 8 núcleos de CPU.

### Requisitos de Software

**Docker Engine 20.10+:** Necessário para containerização de todos os serviços. O Docker deve estar instalado e configurado para executar sem sudo (adicionar usuário ao grupo docker). Verificar instalação com `docker --version`.

**Docker Compose 2.0+:** Ferramenta para orquestração de múltiplos containers. Incluído nas versões mais recentes do Docker Desktop. Verificar instalação com `docker-compose --version`.

**Git:** Sistema de controle de versão para clonagem do repositório. Verificar instalação com `git --version`.

**PHP 8.4+ (Opcional):** Necessário apenas para execução de testes locais fora do container. Deve incluir extensões: pdo_mysql, curl, json, mbstring, openssl.

**Composer (Opcional):** Gerenciador de dependências PHP, necessário apenas para desenvolvimento local. Verificar instalação com `composer --version`.

### Portas Utilizadas

O sistema utiliza as seguintes portas que devem estar disponíveis no sistema host:

- **8000:** Kong API Gateway (ponto de entrada principal)
- **8081-8088:** Microserviços individuais (acesso direto para debug)
- **3306:** MySQL (acesso externo para administração)
- **5672:** RabbitMQ AMQP
- **15672:** RabbitMQ Management UI
- **8089:** Swagger UI (documentação da API)
- **8090:** phpMyAdmin (administração do banco)

## Instalação Passo a Passo

### Passo 1: Preparação do Ambiente

Primeiro, certifique-se de que todos os pré-requisitos estão instalados e funcionando corretamente. Crie um diretório para o projeto em um local apropriado do sistema de arquivos, preferencialmente com permissões adequadas para o usuário atual.

```bash
# Verificar instalação do Docker
docker --version
docker-compose --version

# Verificar se o Docker está rodando
docker info

# Criar diretório do projeto
mkdir -p ~/car-dealership
cd ~/car-dealership
```

### Passo 2: Obtenção do Código Fonte

Clone o repositório do projeto ou copie todos os arquivos fornecidos para o diretório criado. A estrutura de diretórios deve ser preservada exatamente como fornecida, pois os caminhos relativos são importantes para o funcionamento correto do sistema.

```bash
# Se usando Git (substitua pela URL real do repositório)
git clone https://github.com/NeuronioAzul/car-dealership.git .
```

### Passo 3: Configuração de Variáveis de Ambiente

Cada microserviço possui um arquivo `.env` com configurações específicas. As configurações padrão são adequadas para desenvolvimento local, mas podem ser ajustadas conforme necessário. As principais variáveis incluem configurações de banco de dados, RabbitMQ, JWT e URLs de serviços.

```bash
# Verificar arquivos .env em cada serviço
ls -la */\.env

# Exemplo de configuração do Auth Service
cat auth-service/.env
```

As configurações padrão utilizam:

- **Banco de dados:** MySQL na porta 3306 com usuário `root` e senha `rootpassword`
- **RabbitMQ:** Porta 5672 com usuário `guest` e senha `guest`
- **JWT:** Chave secreta configurada para desenvolvimento (deve ser alterada em produção)
- **URLs:** Configuradas para comunicação entre containers Docker

### Passo 4: Construção e Inicialização dos Serviços

O Docker Compose irá construir todas as imagens necessárias e inicializar os serviços na ordem correta, respeitando as dependências entre eles. Este processo pode levar alguns minutos na primeira execução, pois todas as imagens base serão baixadas e as dependências PHP instaladas.

```bash
# Construir e iniciar todos os serviços
docker-compose up -d

# Verificar status dos containers
docker-compose ps

# Acompanhar logs de inicialização
docker-compose logs -f
```

Durante a inicialização, o sistema irá:

1. Baixar imagens base (PHP 8.4-apache, MySQL 8, RabbitMQ 3, Kong)
2. Instalar dependências PHP via Composer em cada microserviço
3. Configurar bancos de dados e criar schemas necessários
4. Inicializar RabbitMQ com filas e exchanges
5. Configurar Kong com rotas e plugins
6. Aguardar que todos os serviços estejam prontos

### Passo 5: Verificação da Instalação

Após a inicialização, verifique se todos os serviços estão respondendo corretamente. Cada serviço possui um endpoint de health check que pode ser usado para verificação de status.

```bash
# Verificar health check via API Gateway
curl http://localhost:8000/api/v1/auth/health
curl http://localhost:8000/api/v1/vehicles/health
curl http://localhost:8000/api/v1/customer/health
curl http://localhost:8000/api/v1/reservations/health
curl http://localhost:8000/api/v1/payments/health
curl http://localhost:8000/api/v1/sales/health
curl http://localhost:8000/api/v1/admin/health
curl http://localhost:8000/api/v1/saga/health

# Verificar serviços diretamente (para debug)
curl http://localhost:8081/api/v1/auth/health  # Auth Service
curl http://localhost:8082/api/v1/customer/health  # Customer Service
curl http://localhost:8083/api/v1/vehicles/health  # Vehicle Service
curl http://localhost:8084/api/v1/reservations/health  # Reservation Service
curl http://localhost:8085/api/v1/payments/health  # Payment Service
curl http://localhost:8086/api/v1/sales/health  # Sales Service
curl http://localhost:8087/api/v1/admin/health  # Admin Service
curl http://localhost:8088/api/v1/saga/health  # SAGA Orchestrator
```

Todos os health checks devem retornar status 200 com resposta JSON indicando que o serviço está saudável.

### Passo 6: Inicialização do Banco de Dados

O sistema inclui scripts para criação automática dos bancos de dados e tabelas necessárias. Adicionalmente, há um script de seeding para popular o banco com dados de exemplo, incluindo usuário administrador, clientes de teste e catálogo de veículos.

```bash
# Executar seeding do banco de dados
cd tests/
php Scripts/DatabaseSeeder.php

# Ou executar via Docker se PHP não estiver instalado localmente
docker-compose exec auth-service php /var/www/html/../tests/Scripts/DatabaseSeeder.php
```

O script de seeding criará:

- 1 usuário administrador (<admin@concessionaria.com> / admin123)
- 5 clientes de exemplo com dados realistas
- Mais de 100 veículos de diversas marcas e modelos
- Dados distribuídos corretamente entre os bancos dos microserviços

### Passo 7: Verificação da Documentação da API

Acesse a documentação interativa da API através do Swagger UI para verificar se todos os endpoints estão disponíveis e funcionando corretamente. A documentação inclui exemplos de requisições, esquemas de dados e permite testar os endpoints diretamente na interface.

```bash
# Acessar Swagger UI
open http://localhost:8089

# Ou verificar se está respondendo
curl http://localhost:8089
```

A documentação deve mostrar todos os endpoints organizados por microserviço, com exemplos de uso e esquemas de dados detalhados.

## Configuração Avançada

### Configuração de Produção

Para ambiente de produção, várias configurações devem ser ajustadas para garantir segurança e performance adequadas. As principais alterações incluem:

**Segurança:**

- Alterar todas as senhas padrão (banco de dados, RabbitMQ, JWT secret)
- Configurar HTTPS com certificados SSL válidos
- Implementar firewall para restringir acesso às portas internas
- Configurar backup automático dos bancos de dados
- Implementar rotação de logs

**Performance:**

- Ajustar configurações de pool de conexões do banco
- Configurar cache Redis para sessões e dados frequentes
- Implementar CDN para assets estáticos
- Configurar load balancer para múltiplas instâncias
- Otimizar configurações do PHP (opcache, memory_limit)

**Monitoramento:**

- Implementar logging centralizado (ELK Stack)
- Configurar métricas de performance (Prometheus/Grafana)
- Implementar alertas para falhas e performance
- Configurar health checks avançados
- Implementar tracing distribuído

### Configuração de Desenvolvimento

Para ambiente de desenvolvimento, algumas configurações podem ser ajustadas para facilitar o desenvolvimento e debug:

**Debug:**

- Habilitar logs detalhados em todos os serviços
- Configurar Xdebug para debug remoto
- Implementar hot reload para desenvolvimento
- Configurar IDE para debug de containers
- Habilitar CORS para desenvolvimento frontend

**Testes:**

- Configurar banco de dados separado para testes
- Implementar fixtures para dados de teste
- Configurar CI/CD pipeline
- Implementar testes automatizados
- Configurar coverage de código

### Configuração de Escalabilidade

O sistema foi projetado para ser escalável horizontalmente. Para implementar escalabilidade:

**Microserviços:**

- Cada serviço pode ser escalado independentemente
- Implementar service discovery (Consul/Eureka)
- Configurar load balancing entre instâncias
- Implementar circuit breakers
- Configurar timeout e retry policies

**Banco de Dados:**

- Implementar read replicas para consultas
- Configurar sharding para dados grandes
- Implementar cache distribuído
- Configurar backup e recovery
- Monitorar performance de queries

**Infraestrutura:**

- Utilizar orquestração com Kubernetes
- Implementar auto-scaling baseado em métricas
- Configurar persistent volumes
- Implementar service mesh (Istio)
- Configurar ingress controllers

## Solução de Problemas

### Problemas Comuns de Instalação

**Erro: "Port already in use"**
Verifique se as portas necessárias estão disponíveis. Use `netstat -tulpn | grep :8000` para verificar se a porta está em uso. Se necessário, pare outros serviços ou altere as portas no docker-compose.yml.

**Erro: "Cannot connect to Docker daemon"**
Certifique-se de que o Docker está rodando e que seu usuário tem permissões adequadas. Execute `sudo systemctl start docker` e adicione seu usuário ao grupo docker com `sudo usermod -aG docker $USER`.

**Erro: "Database connection failed"**
Aguarde alguns minutos para que o MySQL termine a inicialização. Verifique os logs com `docker-compose logs mysql`. Se o problema persistir, verifique as configurações de conexão nos arquivos .env.

**Erro: "RabbitMQ connection refused"**
Similar ao MySQL, o RabbitMQ pode levar alguns minutos para inicializar completamente. Verifique os logs com `docker-compose logs rabbitmq` e aguarde a mensagem "Server startup complete".

### Problemas de Performance

**Lentidão na inicialização:**
Na primeira execução, o Docker precisa baixar todas as imagens base e instalar dependências. Execuções subsequentes serão mais rápidas devido ao cache. Para acelerar, use `docker-compose build --parallel`.

**Alto uso de memória:**
O sistema completo pode usar 4-6GB de RAM. Se necessário, ajuste as configurações de memória no docker-compose.yml ou pare serviços não essenciais durante o desenvolvimento.

**Timeouts em requisições:**
Verifique se todos os serviços estão saudáveis com health checks. Aumente os timeouts nos arquivos de configuração se necessário. Verifique logs para identificar gargalos.

### Problemas de Conectividade

**Serviços não se comunicam:**
Verifique se todos os containers estão na mesma rede Docker. Use `docker network ls` e `docker network inspect` para verificar a configuração de rede.

**API Gateway não roteia corretamente:**
Verifique a configuração do Kong em `api-gateway/kong.yml`. Certifique-se de que todas as rotas estão configuradas corretamente e que os serviços upstream estão respondendo.

**Problemas de CORS:**
Verifique se o Kong está configurado com o plugin CORS habilitado. Para desenvolvimento, certifique-se de que todas as origens estão permitidas.

### Logs e Debug

**Visualizar logs de um serviço específico:**

```bash
docker-compose logs -f auth-service
docker-compose logs -f vehicle-service
```

**Acessar container para debug:**

```bash
docker-compose exec auth-service bash
docker-compose exec mysql mysql -u root -p
```

**Verificar configuração de rede:**

```bash
docker network inspect car-dealership_default
```

**Monitorar recursos:**

```bash
docker stats
```

### Recuperação de Falhas

**Reiniciar serviço específico:**

```bash
docker-compose restart auth-service
```

**Reconstruir serviço após alterações:**

```bash
docker-compose up -d --build auth-service
```

**Reset completo do ambiente:**

```bash
docker-compose down -v
docker-compose up -d
```

**Backup e restore do banco:**

```bash
# Backup
docker-compose exec mysql mysqldump -u root -p --all-databases > backup.sql

# Restore
docker-compose exec -T mysql mysql -u root -p < backup.sql
```

## Manutenção e Atualizações

### Atualizações de Segurança

Mantenha sempre as imagens Docker atualizadas com as últimas correções de segurança. Execute regularmente:

```bash
docker-compose pull
docker-compose up -d
```

### Backup Regular

Implemente rotina de backup regular dos bancos de dados e configurações:

```bash
#!/bin/bash
# Script de backup diário
DATE=$(date +%Y%m%d_%H%M%S)
docker-compose exec mysql mysqldump -u root -p --all-databases > backup_$DATE.sql
```

### Monitoramento de Logs

Configure rotação de logs para evitar uso excessivo de disco:

```bash
# Configurar logrotate para logs do Docker
sudo nano /etc/logrotate.d/docker
```

### Atualizações de Dependências

Mantenha as dependências PHP atualizadas executando periodicamente:

```bash
docker-compose exec auth-service composer update
docker-compose exec vehicle-service composer update
```

Este guia de instalação fornece uma base para implementação do Sistema de Concessionária de Veículos. Para questões específicas ou problemas não cobertos neste documento, entre em contato com a equipe de desenvolvimento.
