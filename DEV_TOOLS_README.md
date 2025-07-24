# Car Dealership - Ferramentas de Desenvolvimento

Este arquivo `composer.json` na raiz do monorepo é dedicado exclusivamente às ferramentas de desenvolvimento compartilhadas entre todos os microsserviços.

## 🛠️ Ferramentas Disponíveis

### PHP CS Fixer
Ferramenta para formatação automática de código PHP seguindo os padrões PSR-12.

### Symfony VarDumper
Ferramenta para debug que está disponível em todos os microsserviços quando o autoload é incluído.

## 📋 Scripts Disponíveis

```bash
# Instalar ferramentas de desenvolvimento
composer install

# Verificar problemas de formatação (dry-run)
composer cs-check

# Corrigir formatação automaticamente
composer cs-fix

# Atualizar ferramentas
composer update
```

## 🔧 Como usar o var-dumper nos microsserviços

Adicione no início dos seus arquivos PHP dos microsserviços:

```php
<?php
// Carregar o var-dumper da raiz
require_once __DIR__ . '/../../vendor/autoload.php';

// Agora você pode usar dd(), dump(), etc.
dd($variable);
```

## 📂 Estrutura

- Cada microsserviço mantém seu próprio `composer.json` com suas dependências específicas
- As ferramentas de desenvolvimento ficam centralizadas na raiz
- Os namespaces dos microsserviços permanecem como `App\`
- O PHP CS Fixer roda em todos os microsserviços de uma vez

## ⚙️ Configuração do PHP CS Fixer

O arquivo `.php-cs-fixer.php` está configurado para:
- Aplicar regras PSR-12
- Usar recursos do PHP 8.4
- Formatar código em todos os microsserviços
- Manter consistência entre todos os serviços
