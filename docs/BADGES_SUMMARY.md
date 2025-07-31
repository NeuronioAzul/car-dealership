## 📊 Sistema de Badges de Coverage - Resumo da Configuração

### ✅ O que está sendo rastreado pelo Git:

#### 📁 Arquivos Essenciais dos Badges:
- `auth-service/badges/coverage-badges.json` - Dados dos badges (24KB)
- `auth-service/badges/badges.md` - Badges em markdown
- `scripts/generate-coverage-badge.sh` - Script completo de geração
- `scripts/regenerate-auth-badges.sh` - Script simples de regeneração
- `docs/COVERAGE_BADGES_GUIDE.md` - Guia de implementação

#### 📄 Arquivos de Configuração:
- `.gitignore` - Configurado para ignorar arquivos grandes
- `README.md` - Atualizado com badges funcionais
- `auth-service/phpunit.xml` - Configurado para coverage

### 🚫 O que está sendo ignorado (.gitignore):

#### 📊 Relatórios de Coverage (grandes):
- `coverage/` - Diretórios de coverage HTML (vários MB)
- `coverage.xml` - Arquivos XML do Clover (centenas de KB)
- `coverage.txt` - Relatórios de texto
- `*.clover` - Arquivos clover diversos

#### 🔧 Arquivos Temporários:
- `.phpunit.result.cache` - Cache do PHPUnit
- `phpunit.xml.bak` - Backups de configuração

#### 📦 Dependências (padrão):
- `vendor/` - Dependências do Composer
- `composer.lock` - Lock files
- `.env` - Variáveis de ambiente

### 🎯 Tamanhos dos Arquivos:

```bash
# Arquivos rastreados (pequenos):
auth-service/badges/coverage-badges.json    # ~1KB
auth-service/badges/badges.md               # ~0.5KB
scripts/generate-coverage-badge.sh          # ~3KB
scripts/regenerate-auth-badges.sh           # ~1KB

# Arquivos ignorados (grandes):
auth-service/coverage/html/                  # ~2-5MB
auth-service/coverage.xml                   # ~50-200KB
auth-service/coverage/clover.xml             # ~50-200KB
```

### 🔄 Como Funciona:

1. **Desenvolvimento**: Você modifica código
2. **Regeneração**: Execute `./scripts/regenerate-auth-badges.sh`
3. **Commit**: Git salva apenas os badges (poucos KB)
4. **GitHub**: Badges mostram coverage atualizado no README

### 🎨 Badges Disponíveis:

[![Coverage](https://img.shields.io/badge/coverage-24.8%25-red?style=for-the-badge&logo=php)](auth-service/coverage/html/index.html)
[![Tests](https://img.shields.io/badge/tests-51%20total-orange?style=for-the-badge&logo=php)](auth-service/tests/)
[![Assertions](https://img.shields.io/badge/assertions-137-blue?style=for-the-badge&logo=checkmarx)](auth-service/tests/)

### 📈 Economia de Espaço:

- **Antes**: ~5MB de relatórios + badges
- **Depois**: ~5KB apenas badges
- **Economia**: ~99% menos dados no repositório

### 🔧 Para Outros Serviços:

Use o guia em `docs/COVERAGE_BADGES_GUIDE.md` para implementar badges nos outros microserviços.
