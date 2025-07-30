# Como Usar o PATCH para Veículos

## Diferenças entre PUT e PATCH

### PUT (Atualização Completa)
- Substitui **todos** os campos do recurso
- Requer envio de **todos** os dados do veículo
- Usado quando você tem todos os dados e quer substituir completamente

### PATCH (Atualização Parcial) - **NOVO!**
- Atualiza **apenas** os campos fornecidos
- Campos não fornecidos permanecem inalterados
- Ideal para atualizações específicas

## Exemplos de Uso do PATCH

### 1. Atualizar apenas o preço
```bash
curl -X PATCH http://localhost:8000/api/v1/vehicles/update/123e4567-e89b-12d3-a456-426614174000 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "price": 45000.00
  }'
```

### 2. Atualizar status e quilometragem
```bash
curl -X PATCH http://localhost:8000/api/v1/vehicles/update/123e4567-e89b-12d3-a456-426614174000 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "maintenance",
    "mileage": 15000
  }'
```

### 3. Atualizar múltiplos campos
```bash
curl -X PATCH http://localhost:8000/api/v1/vehicles/update/123e4567-e89b-12d3-a456-426614174000 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "price": 48000.00,
    "description": "Veículo em excelente estado",
    "features": ["ar-condicionado", "direção hidráulica", "vidros elétricos"],
    "status": "available"
  }'
```

## Validações e Regras de Negócio

### Validações Implementadas:
- ✅ Apenas campos válidos são aceitos
- ✅ Validação de tipos de dados
- ✅ Verificação de ranges (ano, portas, assentos, etc.)
- ✅ Validação de enums (status, combustível, transmissão)
- ✅ Formato de placa, chassi, RENAVAM

### Regras de Negócio:
- ❌ Não pode alterar status de veículo vendido
- ❌ Chassi deve ser único
- ❌ Placa deve ser única
- ❌ Preço de venda > preço de compra
- ❌ Campos obrigatórios não podem ser zerados

## Resposta de Sucesso

```json
{
  "success": true,
  "message": "Veículo atualizado parcialmente com sucesso",
  "data": {
    "id": "123e4567-e89b-12d3-a456-426614174000",
    "brand": "Toyota",
    "model": "Corolla",
    "price": 45000.00,
    // ... outros campos
  },
  "updated_by": "admin_user_id",
  "fields_updated": ["price", "mileage"]
}
```

## Códigos de Erro

- **400**: Nenhum campo fornecido ou dados inválidos
- **401**: Token de autenticação inválido
- **403**: Apenas administradores podem atualizar
- **404**: Veículo não encontrado
- **422**: Falha na validação ou regras de negócio
- **500**: Erro interno do servidor
