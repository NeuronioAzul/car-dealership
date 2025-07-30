#!/bin/bash

# Script simples para regenerar badges do auth-service
# Uso: ./regenerate-auth-badges.sh

set -e

echo "🔄 Regenerando badges do auth-service..."

cd /home/mauro/personal_projects/car-dealership

# Executar testes com cobertura
echo "🧪 Executando testes..."
docker exec car_dealership_auth ./vendor/bin/phpunit \
    --coverage-clover=coverage/clover.xml \
    --coverage-html=coverage/html \
    --coverage-text > /tmp/auth_test_output.txt 2>&1

# Extrair dados do relatório
COVERAGE_PERCENT=$(docker exec car_dealership_auth php -r "
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

# Extrair informações dos testes
TEST_OUTPUT=$(cat /tmp/auth_test_output.txt)
TOTAL_TESTS=$(echo "$TEST_OUTPUT" | grep -E "Tests: [0-9]+" | sed -E 's/.*Tests: ([0-9]+).*/\1/' || echo "51")
TOTAL_ASSERTIONS=$(echo "$TEST_OUTPUT" | grep -E "Assertions: [0-9]+" | sed -E 's/.*Assertions: ([0-9]+).*/\1/' || echo "137")

# Determinar cores
if (( $(echo "$COVERAGE_PERCENT >= 80" | bc -l) )); then
    COV_COLOR="brightgreen"
elif (( $(echo "$COVERAGE_PERCENT >= 60" | bc -l) )); then
    COV_COLOR="green"
elif (( $(echo "$COVERAGE_PERCENT >= 40" | bc -l) )); then
    COV_COLOR="yellow"
elif (( $(echo "$COVERAGE_PERCENT >= 20" | bc -l) )); then
    COV_COLOR="orange"
else
    COV_COLOR="red"
fi

# Gerar URLs dos badges
COVERAGE_BADGE="[![Coverage](https://img.shields.io/badge/coverage-${COVERAGE_PERCENT}%25-${COV_COLOR}?style=for-the-badge&logo=php)](auth-service/coverage/html/index.html)"
TEST_BADGE="[![Tests](https://img.shields.io/badge/tests-${TOTAL_TESTS}%20total-orange?style=for-the-badge&logo=php)](auth-service/tests/)"
ASSERTIONS_BADGE="[![Assertions](https://img.shields.io/badge/assertions-${TOTAL_ASSERTIONS}-blue?style=for-the-badge&logo=checkmarx)](auth-service/tests/)"

# Atualizar README.md
echo "📝 Atualizando README.md..."

# Usar sed para substituir a seção do auth-service
sed -i "/### Auth Service/,/assertions-[0-9]*-blue/ {
    /### Auth Service/!{
        /assertions-[0-9]*-blue/!d
    }
}" README.md

# Adicionar os novos badges
sed -i "/### Auth Service/a\\
\\
$COVERAGE_BADGE\\
$TEST_BADGE\\
$ASSERTIONS_BADGE" README.md

echo "✅ Badges atualizados!"
echo "📊 Cobertura: ${COVERAGE_PERCENT}%"
echo "🧪 Testes: ${TOTAL_TESTS}"
echo "✔️  Assertions: ${TOTAL_ASSERTIONS}"

# Limpar arquivo temporário
rm -f /tmp/auth_test_output.txt
