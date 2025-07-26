# Tratamento de Erros - Vehicle Service

## Mensagens de Erro Amigáveis

O sistema agora traduz automaticamente erros técnicos de banco de dados para mensagens amigáveis ao usuário.

## Exemplos de Tradução

### Antes (Técnico)
```json
{
  "error": true,
  "message": "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'ABC1232DEF5679501' for key 'vehicles.chassis_number'",
  "code": 500
}
```

### Depois (Amigável)
```json
{
  "error": true,
  "message": "Este número de chassi já está sendo usado por outro veículo",
  "code": 422
}
```

## Tipos de Erros Tratados

### 1. Erros de Duplicação (422)
- **Chassi duplicado**: "Este número de chassi já está sendo usado por outro veículo"
- **Placa duplicada**: "Esta placa já está sendo usada por outro veículo"
- **RENAVAM duplicado**: "Este RENAVAM já está sendo usado por outro veículo"
- **Genérico**: "Já existe um veículo com estes dados. Verifique chassi, placa e RENAVAM"

### 2. Erros de Referência (422)
- **Foreign Key**: "Erro de referência: dados relacionados não encontrados"

### 3. Erros de Validação (422)
- **Check Constraint**: "Dados inválidos: valores não atendem às regras do sistema"
- **Not Null**: "Campos obrigatórios não podem estar vazios"

### 4. Erros de Infraestrutura
- **Conexão (503)**: "Erro de conexão com o banco de dados. Tente novamente em alguns instantes"
- **Timeout (408)**: "Operação demorou muito para ser executada. Tente novamente"
- **Permissão (500)**: "Erro de permissão no banco de dados. Entre em contato com o suporte"
- **Sintaxe (500)**: "Erro interno de processamento. Entre em contato com o suporte"

### 5. Erro Genérico (500)
- **Outros**: "Erro interno ao processar os dados. Tente novamente ou entre em contato com o suporte"

## Códigos de Status HTTP

- **400**: Dados de entrada inválidos
- **408**: Timeout de operação
- **422**: Erro de validação ou regra de negócio
- **500**: Erro interno do servidor
- **503**: Serviço temporariamente indisponível

## Implementação

O tratamento é centralizado na classe `DatabaseErrorHandler` e aplicado automaticamente em todos os Use Cases:

- `CreateVehicleUseCase`
- `UpdateVehicleUseCase` 
- `PatchVehicleUseCase`

## Benefícios

✅ **Experiência do usuário melhorada**
✅ **Segurança**: Não exposição de detalhes técnicos
✅ **Manutenibilidade**: Tratamento centralizado
✅ **Consistência**: Mensagens padronizadas em todo sistema
✅ **Debugging**: Logs internos mantém detalhes técnicos
