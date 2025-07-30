# 🧪 Guia de Implementação de Badges de Cobertura

Este documento explica como implementar badges de cobertura de testes nos microserviços do projeto.

## ✅ Configuração Atual

### Auth Service
- ✅ Xdebug instalado e configurado
- ✅ PHPUnit configurado para cobertura
- ✅ Badges implementados no README
- ✅ Script de regeneração automática

## 🔧 Como Implementar em Outros Serviços

### 1. Instalar Xdebug no Dockerfile

Adicione ao Dockerfile do serviço:

```dockerfile
# Instalar Xdebug para cobertura de código
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Configurar Xdebug para cobertura
RUN echo "xdebug.mode=coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
```

### 2. Configurar PHPUnit

Atualize o `phpunit.xml`:

```xml
<coverage>
    <report>
        <clover outputFile="coverage/clover.xml"/>
        <html outputDirectory="coverage/html"/>
        <text outputFile="coverage/coverage.txt" showUncoveredFiles="false"/>
    </report>
    <include>
        <directory suffix=".php">src</directory>
    </include>
    <exclude>
        <directory>vendor</directory>
        <directory>tests</directory>
        <file>public/index.php</file>
    </exclude>
</coverage>
```

### 3. Rebuild e Restart do Container

```bash
# Rebuild do serviço
docker-compose build [nome-do-servico]

# Restart do container
docker-compose up -d --no-deps [nome-do-servico]
```

### 4. Executar Testes com Cobertura

```bash
# Dentro do container
docker exec [container-name] ./vendor/bin/phpunit \
    --coverage-clover=coverage/clover.xml \
    --coverage-html=coverage/html \
    --coverage-text=coverage/coverage.txt
```

### 5. Extrair Dados de Cobertura

```bash
# Extrair porcentagem de cobertura
COVERAGE_PERCENT=$(docker exec [container-name] php -r "
\$xml = simplexml_load_file('coverage/clover.xml');
\$metrics = \$xml->project->metrics;
\$lines = (float)\$metrics['coveredstatements'];
\$total = (float)\$metrics['statements'];
if (\$total > 0) {
    \$percentage = round((\$lines / \$total) * 100, 1);
    echo \$percentage;
} else {
    echo '0';
}
")
```

### 6. Gerar URLs dos Badges

```bash
# Determinar cor baseada na cobertura
if (( $(echo "$COVERAGE_PERCENT >= 80" | bc -l) )); then
    COLOR="brightgreen"
elif (( $(echo "$COVERAGE_PERCENT >= 60" | bc -l) )); then
    COLOR="green"
elif (( $(echo "$COVERAGE_PERCENT >= 40" | bc -l) )); then
    COLOR="yellow"
elif (( $(echo "$COVERAGE_PERCENT >= 20" | bc -l) )); then
    COLOR="orange"
else
    COLOR="red"
fi

# URLs dos badges
COVERAGE_BADGE="https://img.shields.io/badge/coverage-${COVERAGE_PERCENT}%25-${COLOR}?style=for-the-badge&logo=php"
```

### 7. Adicionar ao README

```markdown
### [Nome do Serviço]

[![Coverage](COVERAGE_BADGE_URL)](service-name/coverage/html/index.html)
[![Tests](TEST_BADGE_URL)](service-name/tests/)
[![Assertions](ASSERTIONS_BADGE_URL)](service-name/tests/)
```

## 📋 Scripts Disponíveis

### Auth Service
- `./scripts/generate-coverage-badge.sh auth-service` - Gerar badges completos
- `./scripts/regenerate-auth-badges.sh` - Regenerar badges e atualizar README

## 🎨 Customização de Cores

| Cobertura | Cor | Hex |
|-----------|-----|-----|
| ≥ 80% | Verde brilhante | `brightgreen` |
| ≥ 60% | Verde | `green` |
| ≥ 40% | Amarelo | `yellow` |
| ≥ 20% | Laranja | `orange` |
| < 20% | Vermelho | `red` |

## 🔗 Links Úteis

- [Shields.io](https://shields.io/) - Gerador de badges
- [PHPUnit Coverage](https://phpunit.readthedocs.io/en/10.5/code-coverage.html) - Documentação oficial
- [Xdebug](https://xdebug.org/docs/code_coverage) - Configuração de cobertura

## 🚀 Próximos Passos

1. Implementar badges nos demais serviços:
   - [ ] Customer Service
   - [ ] Vehicle Service
   - [ ] Reservation Service
   - [ ] Payment Service
   - [ ] Sales Service
   - [ ] Admin Service
   - [ ] SAGA Orchestrator

2. Automatizar com GitHub Actions (futuro)
3. Integrar com ferramentas de CI/CD
