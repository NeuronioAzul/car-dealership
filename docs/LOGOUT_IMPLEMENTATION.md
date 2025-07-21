# Sistema de Logout Real com Invalidação de Token

## Implementação

O sistema agora implementa um logout real que invalida o token JWT, impedindo seu uso posterior até sua expiração natural.

## Componentes Implementados

### 1. **TokenBlacklistRepository**
- Gerencia a tabela `token_blacklist` no banco de dados
- Armazena hashes dos tokens invalidados
- Implementa limpeza automática de tokens expirados

### 2. **TokenBlacklistService**
- Camada de aplicação para gerenciar tokens revogados
- Converte tokens em hashes seguros antes de armazenar
- Verifica se tokens estão na blacklist

### 3. **JWTService (Atualizado)**
- Integrado com `TokenBlacklistService`
- Verifica blacklist durante validação de tokens
- Métodos para revogar e verificar tokens

### 4. **LogoutUseCase**
- Implementa a lógica de negócio para logout
- Valida token antes de revogar
- Segue princípios de Clean Architecture

### 5. **AuthController (Atualizado)**
- Método `logout()` real que invalida tokens
- Tratamento de erros amigável
- Integração com LogoutUseCase

## Banco de Dados

### Tabela `token_blacklist`
```sql
CREATE TABLE token_blacklist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token_hash (token_hash),
    INDEX idx_expires_at (expires_at)
);
```

## Como Usar

### 1. Executar Migration
```bash
cd /home/mauro/personal_projects/car-dealership/auth-service
./run_migrations.sh
```

### 2. Endpoint de Logout
```http
POST /logout
Authorization: Bearer {token}
```

**Resposta de Sucesso:**
```json
{
    "success": true,
    "message": "Logout realizado com sucesso. Token invalidado."
}
```

**Resposta de Erro:**
```json
{
    "error": true,
    "message": "Token já foi invalidado anteriormente."
}
```

### 3. Validação de Token (atualizada)
```http
POST /validate
Authorization: Bearer {token}
```

**Token válido:**
```json
{
    "success": true,
    "data": {
        "valid": true,
        "user_id": "123",
        "email": "user@example.com",
        "role": "customer",
        "expires_at": 1642780800
    }
}
```

**Token invalidado:**
```json
{
    "error": true,
    "message": "Token foi invalidado. Faça login novamente.",
    "valid": false
}
```

## Fluxo de Funcionamento

1. **Login:** Usuário recebe token JWT válido
2. **Uso:** Token é usado para autenticação em requests
3. **Logout:** Token é adicionado à blacklist via hash SHA-256
4. **Próximos requests:** JWTService verifica blacklist antes de validar
5. **Limpeza:** Tokens expirados são removidos automaticamente da blacklist

## Benefícios

- ✅ **Segurança:** Tokens invalidados não podem ser reutilizados
- ✅ **Performance:** Apenas hash do token é armazenado
- ✅ **Escalabilidade:** Limpeza automática de registros expirados
- ✅ **Clean Architecture:** Separação clara de responsabilidades
- ✅ **Compatibilidade:** Não quebra funcionalidades existentes

## Considerações Importantes

1. **Tamanho da Blacklist:** Em produção, monitore o crescimento da tabela
2. **Limpeza Automática:** Configure eventos automáticos para limpeza
3. **Sincronização:** Em ambiente distribuído, considere cache compartilhado
4. **Backup:** A blacklist é crítica para segurança, inclua em backups

## Testes

Para testar o logout:

1. Faça login e obtenha um token
2. Use o token para acessar um endpoint protegido (deve funcionar)
3. Faça logout com o token
4. Tente usar o mesmo token novamente (deve ser rejeitado)

## Scripts Úteis

- `./run_migrations.sh` - Executa migrations
- Limpeza manual: `DELETE FROM token_blacklist WHERE expires_at <= NOW()`
