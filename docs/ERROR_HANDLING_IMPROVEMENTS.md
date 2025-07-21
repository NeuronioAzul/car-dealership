# Melhorias nas Mensagens de Erro

## Antes das mudanças:
```json
{
    "error": "Internal Server Error",
    "message": "Token inválido: Expired token",
    "trace": "#0 /var/www/html/src/Presentation/Middleware/AuthMiddleware.php(26): App\\Application\\Services\\JWTService->validateToken('eyJ0eXAiOiJKV1Q...')\n#1 /var/www/html/src/Presentation/Middleware/AuthMiddleware.php(48): App\\Presentation\\Middleware\\AuthMiddleware->authenticate()\n#2 /var/www/html/src/Infrastructure/Http/Router.php(104): App\\Presentation\\Middleware\\AuthMiddleware->requireAdmin()\n#3 /var/www/html/src/Infrastructure/Http/Router.php(59): App\\Infrastructure\\Http\\Router->checkMiddleware('POST /create')\n#4 /var/www/html/public/index.php(37): App\\Infrastructure\\Http\\Router->handleRequest()\n#5 {main}"
}
```

## Depois das mudanças:

### Para token expirado:
```json
{
    "error": true,
    "message": "Token expirado. Faça login novamente para continuar.",
    "code": 401,
    "type": "authentication_error",
    "action": "redirect_to_login"
}
```

### Para token inválido:
```json
{
    "error": true,
    "message": "Token inválido. Faça login novamente para continuar.",
    "code": 401,
    "type": "authentication_error",
    "action": "redirect_to_login"
}
```

### Para usuário sem permissão de admin:
```json
{
    "error": true,
    "message": "Acesso negado. Apenas administradores podem acessar este recurso.",
    "code": 403,
    "type": "authorization_error",
    "action": "insufficient_permissions"
}
```

### Para token não fornecido:
```json
{
    "error": true,
    "message": "Token de autenticação não fornecido",
    "code": 401,
    "type": "authentication_error",
    "action": "redirect_to_login"
}
```

## O que foi melhorado:

1. **AuthMiddleware.php**: 
   - Agora captura exceções do JWTService e converte em mensagens amigáveis
   - Trata diferentes tipos de erro (token expirado, inválido, etc.)
   - Remove traces desnecessários

2. **Router.php**:
   - Adicionado método `handleAuthError()` específico para erros de autenticação
   - Adiciona campos `type` e `action` para ajudar o frontend a tomar ações apropriadas
   - Para middleware com `try/catch` para capturar erros de auth

3. **VehicleController.php**:
   - Melhorado o tratamento de erros no método `createVehicle()`
   - Adiciona contexto adicional para erros de autenticação/autorização

## Benefícios:

- ✅ Mensagens mais amigáveis para o usuário final
- ✅ Sem traces de stack expostos em produção
- ✅ Campos adicionais (`type`, `action`) para o frontend tomar ações apropriadas
- ✅ Códigos HTTP corretos (401 para auth, 403 para autorização)
- ✅ Tratamento centralizado de erros de autenticação
