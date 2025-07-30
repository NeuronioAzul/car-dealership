#!/bin/bash

# Script para gerar badge de cobertura de testes
# Uso: ./generate-coverage-badge.sh [service-name]

set -e

SERVICE_NAME=${1:-"auth-service"}
PROJECT_DIR="/home/mauro/personal_projects/car-dealership"
SERVICE_DIR="$PROJECT_DIR/$SERVICE_NAME"
COVERAGE_DIR="$SERVICE_DIR/coverage"
BADGE_DIR="$SERVICE_DIR/badges"

# Verificar se o serviço existe
if [ ! -d "$SERVICE_DIR" ]; then
    echo "❌ Serviço '$SERVICE_NAME' não encontrado em $SERVICE_DIR"
    exit 1
fi

echo "🔍 Gerando cobertura de testes para $SERVICE_NAME..."

# Criar diretórios necessários
mkdir -p "$COVERAGE_DIR"
mkdir -p "$BADGE_DIR"

# Entrar no diretório do projeto
cd "$PROJECT_DIR"

# Verificar se o container está rodando
CONTAINER_NAME="car_dealership_auth"
if ! docker ps | grep -q "$CONTAINER_NAME"; then
    echo "❌ Container $CONTAINER_NAME não está rodando. Execute: docker-compose up -d"
    exit 1
fi

# Executar testes com cobertura no container
echo "🧪 Executando testes com cobertura no container..."
docker exec "$CONTAINER_NAME" ./vendor/bin/phpunit \
    --coverage-clover=coverage/clover.xml \
    --coverage-html=coverage/html \
    --coverage-text=coverage/coverage.txt

# Verificar se o arquivo de cobertura foi gerado
if [ ! -f "$COVERAGE_DIR/clover.xml" ]; then
    echo "❌ Arquivo de cobertura não gerado: $COVERAGE_DIR/clover.xml"
    exit 1
fi

# Extrair porcentagem de cobertura do arquivo clover.xml
COVERAGE_PERCENT=$(docker exec "$CONTAINER_NAME" php -r "
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

echo "📊 Cobertura de código: $COVERAGE_PERCENT%"

# Determinar cor do badge baseado na porcentagem
if (( $(echo "$COVERAGE_PERCENT >= 90" | bc -l) )); then
    COLOR="brightgreen"
elif (( $(echo "$COVERAGE_PERCENT >= 80" | bc -l) )); then
    COLOR="green"
elif (( $(echo "$COVERAGE_PERCENT >= 70" | bc -l) )); then
    COLOR="yellow"
elif (( $(echo "$COVERAGE_PERCENT >= 60" | bc -l) )); then
    COLOR="orange"
else
    COLOR="red"
fi

# Extrair informações dos testes também
TEST_OUTPUT=$(docker exec "$CONTAINER_NAME" ./vendor/bin/phpunit --testdox 2>&1)
TOTAL_TESTS=$(echo "$TEST_OUTPUT" | grep -E "Tests: [0-9]+" | sed -E 's/.*Tests: ([0-9]+).*/\1/' || echo "0")
TOTAL_ASSERTIONS=$(echo "$TEST_OUTPUT" | grep -E "Assertions: [0-9]+" | sed -E 's/.*Assertions: ([0-9]+).*/\1/' || echo "0")

# Verificar se houve falhas nos testes
if echo "$TEST_OUTPUT" | grep -q "FAILURES\|ERRORS"; then
    TEST_STATUS="failing"
    TEST_COLOR="red"
else
    TEST_STATUS="passing"
    TEST_COLOR="brightgreen"
fi

# Gerar URLs dos badges usando shields.io
COVERAGE_BADGE_URL="https://img.shields.io/badge/coverage-$COVERAGE_PERCENT%25-$COLOR?style=for-the-badge&logo=php"
TEST_BADGE_URL="https://img.shields.io/badge/tests-$TOTAL_TESTS%20$TEST_STATUS-$TEST_COLOR?style=for-the-badge&logo=php"
ASSERTIONS_BADGE_URL="https://img.shields.io/badge/assertions-$TOTAL_ASSERTIONS-blue?style=for-the-badge&logo=checkmarx"

# Criar arquivo JSON com informações dos badges
cat > "$BADGE_DIR/coverage-badges.json" << EOF
{
    "service": "$SERVICE_NAME",
    "coverage": {
        "percentage": "$COVERAGE_PERCENT",
        "color": "$COLOR",
        "badge_url": "$COVERAGE_BADGE_URL"
    },
    "tests": {
        "total": "$TOTAL_TESTS",
        "status": "$TEST_STATUS",
        "assertions": "$TOTAL_ASSERTIONS",
        "test_badge_url": "$TEST_BADGE_URL",
        "assertions_badge_url": "$ASSERTIONS_BADGE_URL"
    },
    "generated_at": "$(date -u +"%Y-%m-%dT%H:%M:%SZ")"
}
EOF

# Criar arquivo Markdown com os badges
cat > "$BADGE_DIR/badges.md" << EOF
[![Coverage](${COVERAGE_BADGE_URL})](./coverage/html/index.html)
[![Tests](${TEST_BADGE_URL})](./tests/)
[![Assertions](${ASSERTIONS_BADGE_URL})](./tests/)
EOF

echo "✅ Badges gerados com sucesso:"
echo "   - Cobertura: $COVERAGE_PERCENT% ($COLOR)"
echo "   - Testes: $TOTAL_TESTS $TEST_STATUS ($TEST_COLOR)"
echo "   - Assertions: $TOTAL_ASSERTIONS"
echo "   - Arquivo JSON: $BADGE_DIR/coverage-badges.json"
echo "   - Arquivo Markdown: $BADGE_DIR/badges.md"
echo ""
echo "🔗 Badges para README.md:"
echo "[![Coverage](${COVERAGE_BADGE_URL})](auth-service/coverage/html/index.html)"
echo "[![Tests](${TEST_BADGE_URL})](auth-service/tests/)"
echo "[![Assertions](${ASSERTIONS_BADGE_URL})](auth-service/tests/)"
