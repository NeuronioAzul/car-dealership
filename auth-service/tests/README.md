# Testes do Auth Service

Este diretório contém testes automatizados para o serviço de autenticação.

## Estrutura dos Testes

- **Feature/**: Testes de funcionalidade completa (end-to-end)
- **Unit/**: Testes unitários de classes específicas
- **Integration/**: Testes de integração

## Configuração

### Pré-requisitos

1. PHP 8.1+ instalado
2. Composer instalado
3. Auth-service rodando em `http://localhost:8081/api/v1/auth`

### Instalação das Dependências

```bash
composer install
```

### Preparação do Ambiente

1. Certifique-se de que o auth-service está rodando:
   ```bash
   docker compose up -d auth-service
   ```

2. Execute as migrations do banco:
   ```bash
   ./run_migrations.sh
   ```

## Executando os Testes

### Todos os Testes
```bash
./vendor/bin/phpunit --colors --testdox
```

### Apenas Testes de Feature
```bash
./vendor/bin/phpunit tests/Feature --colors --testdox
```

### Apenas Testes de Logout
```bash
./vendor/bin/phpunit tests/Feature/LogoutTest.php --colors --testdox
# ou use o script:
./run_logout_tests.sh
```

### Teste Específico
```bash
./vendor/bin/phpunit tests/Feature/LogoutTest.php::testCompleteLogoutFlow --colors --testdox
```

## Testes Disponíveis

### AuthServiceHealthTest
- ✅ Verifica se o serviço está rodando
- ✅ Testa login com credenciais válidas
- ✅ Testa login com credenciais inválidas

### LogoutTest
- ✅ **testCompleteLogoutFlow**: Fluxo completo de login → validação → logout → invalidação
- ✅ **testLogoutWithAlreadyInvalidatedToken**: Logout com token já invalidado
- ✅ **testLogoutWithoutToken**: Logout sem fornecer token
- ✅ **testLogoutWithInvalidToken**: Logout com token inválido
- ✅ **testLogoutWithMalformedToken**: Logout com token malformado
- ✅ **testInvalidatedTokenCannotBeUsedElsewhere**: Token invalidado não funciona em outras operações
- ✅ **testMultipleTokensFromSameUser**: Múltiplos tokens do mesmo usuário

## Cenários de Teste

### Fluxo Normal de Logout
1. Login com credenciais válidas
2. Recebimento de token JWT
3. Validação do token (deve ser aceito)
4. Logout com o token
5. Nova tentativa de validação (deve falhar)

### Casos de Erro
- Logout sem token
- Logout com token inválido
- Logout com token malformado
- Logout com token já invalidado

### Casos Avançados
- Múltiplos tokens do mesmo usuário
- Invalidação seletiva de tokens
- Verificação de não interferência entre tokens

## Debugging

### Verificar Logs
```bash
# Logs do auth-service
docker compose logs auth-service

# Logs do banco de dados
docker compose logs auth-db
```

### Verificar Blacklist
```bash
# Conectar ao banco e verificar tokens invalidados
docker compose exec auth-db mysql -u root -pauth_password auth_db -e "SELECT * FROM token_blacklist;"
```

### Teste Manual via cURL
```bash
# Login
curl -X POST http://localhost:8081/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"admin123"}'

# Logout (substitua TOKEN pelo token recebido)
curl -X POST http://localhost:8081/api/v1/auth/logout \
  -H "Authorization: Bearer TOKEN"
```

## Configuração de CI/CD

Para integração com pipelines de CI/CD, adicione os seguintes passos:

```yaml
- name: Install PHP dependencies
  run: composer install

- name: Wait for auth service
  run: |
    timeout 60 bash -c 'until curl -f http://localhost:8081/api/v1/auth/health; do sleep 2; done'

- name: Run tests
  run: ./vendor/bin/phpunit --colors
```

## Troubleshooting

### Auth-service não está rodando
```bash
docker compose up -d auth-service
```

### Erro de conexão com banco
```bash
docker compose up -d auth-db
./run_migrations.sh
```

### Dependências não instaladas
```bash
composer install
```

### Problemas de permissão
```bash
chmod +x run_logout_tests.sh
chmod +x run_migrations.sh
```
