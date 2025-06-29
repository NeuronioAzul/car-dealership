# Iniciando o Makefile com o conteúdo a seguir
# Este Makefile inicia um ambiente de desenvolvimento e é usado no linux
# para construir o projeto

.PHONY: start-all build clean run

.DEFAULT_GOAL := help

## —— Ambiente de desenvolvimento 🏗️
start-all: build composer-inst-all-conteiners shared-install tests-install migrate-sql seeder-sql ## Cria o ambiente de desenvolvimento
clean-all: clean-all-composer destroy-docker prune-docker ## Limpa o ambiente de desenvolvimento

## —— Docker 🐳
build: ## Constroi o ambiente de desenvolvimento
	@echo "Building the project..."
	COMPOSE_BAKE=true docker-compose up --no-deps --force-recreate --build -d

## —— Composer 📦 ee outras Dependencias 
composer-inst-all-conteiners: ## Instala as dependências do composer em todos os containers
	@echo "Installing composer dependencies in all containers..."
	@docker-compose exec -T admin-service bash -c "composer install --no-interaction --optimize-autoloader --quiet" \
	|| (echo '❌ Erro ao rodar composer install no admin-service!' && exit 1)
	@docker-compose exec -T auth-service bash -c "composer install --no-interaction --optimize-autoloader --quiet" \
	|| (echo '❌ Erro ao rodar composer install no auth-service!' && exit 1)
	@docker-compose exec -T customer-service bash -c "composer install --no-interaction --optimize-autoloader --quiet" \
	|| (echo '❌ Erro ao rodar composer install no customer-service!' && exit 1)
	@docker-compose exec -T payment-service bash -c "composer install --no-interaction --optimize-autoloader --quiet" \
	|| (echo '❌ Erro ao rodar composer install no payment-service!' && exit 1)
	@docker-compose exec -T reservation-service bash -c "composer install --no-interaction --optimize-autoloader --quiet" \
	|| (echo '❌ Erro ao rodar composer install no reservation-service!' && exit 1)
	@docker-compose exec -T saga-orchestrator bash -c "composer install --no-interaction --optimize-autoloader --quiet" \
	|| (echo '❌ Erro ao rodar composer install no saga-orchestrator!' && exit 1)
	@docker-compose exec -T sales-service bash -c "composer install --no-interaction --optimize-autoloader --quiet" \
	|| (echo '❌ Erro ao rodar composer install no sales-service!' && exit 1)
	@docker-compose exec -T vehicle-service bash -c "composer install --no-interaction --optimize-autoloader --quiet" \
	|| (echo '❌ Erro ao rodar composer install no vehicle-service!' && exit 1)

shared-install: ## Instala as dependências de projeto na pasta shared através do container admin-service
	@echo "Installing shared dependencies..."
	@docker-compose exec -T admin-service bash -c "cd shared && composer install --no-interaction --optimize-autoloader --quiet" \
	|| (echo '❌ Erro ao rodar composer install no shared!' && exit 1)

tests-install: ## Instala as dependências de projeto na pasta tests através do container admin-service
	@echo "Installing test dependencies..."
	@docker-compose exec -T admin-service bash -c "cd tests && composer install --no-interaction --optimize-autoloader --quiet" \
	|| (echo '❌ Erro ao rodar composer install no tests!' && exit 1)

## —— Banco de Dados 🎲
migrate-sql: ## Efetua a migração de todos os bancos de dados através do container admin-service
	@echo "Running SQL migration..."
	@docker-compose exec -T  admin-service php shared/database/migrate.php

seeder-sql: ## Executa o seeder de todos os bancos de dados através do container admin-service
	@echo "Running SQL seeder..."
	@docker-compose exec -T  admin-service php shared/database/seed.php

## —— Limpeza 🧹
clean-all-composer: ## Limpa todos os arquivos composer.lock e diretórios vendor em todos os containers 
	@echo "Cleaning all composer lock files and vendor directories..."; \
	docker-compose ps | grep -q 'admin-service.*Up' && docker-compose exec -T admin-service bash -c "rm -rf vendor composer.lock" || (echo "🔻admin-service container is not running. Skipping clean." && exit 0); \
	docker-compose ps | grep -q 'auth-service.*Up' && docker-compose exec -T auth-service bash -c "rm -rf vendor composer.lock" || (echo "🔻auth-service container is not running. Skipping clean." && exit 0); \
	docker-compose ps | grep -q 'customer-service.*Up' && docker-compose exec -T customer-service bash -c "rm -rf vendor composer.lock" || (echo "🔻customer-service container is not running. Skipping clean." && exit 0); \
	docker-compose ps | grep -q 'payment-service.*Up' && docker-compose exec -T payment-service bash -c "rm -rf vendor composer.lock" || (echo "🔻payment-service container is not running. Skipping clean." && exit 0); \
	docker-compose ps | grep -q 'reservation-service.*Up' && docker-compose exec -T reservation-service bash -c "rm -rf vendor composer.lock" || (echo "🔻reservation-service container is not running. Skipping clean." && exit 0); \
	docker-compose ps | grep -q 'saga-orchestrator.*Up' && docker-compose exec -T saga-orchestrator bash -c "rm -rf vendor composer.lock" || (echo "🔻saga-orchestrator container is not running. Skipping clean." && exit 0); \
	docker-compose ps | grep -q 'sales-service.*Up' && docker-compose exec -T sales-service bash -c "rm -rf vendor composer.lock" || (echo "🔻sales-service container is not running. Skipping clean." && exit 0); \
	docker-compose ps | grep -q 'vehicle-service.*Up' && docker-compose exec -T vehicle-service bash -c "rm -rf vendor composer.lock" || (echo "🔻vehicle-service container is not running. Skipping clean." && exit 0); \
	docker-compose ps | grep -q 'admin-service.*Up' && docker-compose exec -T admin-service bash -c "cd shared && rm -rf vendor composer.lock" || (echo "🔻admin-service container is not running. Skipping clean." && exit 0); \
	docker-compose ps | grep -q 'admin-service.*Up' && docker-compose exec -T admin-service bash -c "cd tests && rm -rf vendor composer.lock" || (echo "🔻admin-service container is not running. Skipping clean." && exit 0);

destroy-docker: ## Destroy todos os containers, volumes e redes do docker
	@echo "Cleaning everything..."; \
		docker-compose down --volumes --remove-orphans

# preciso me certificar que não existe mais nada no docker, confirmar antes de executar
prune-docker: ## Limpa o sistema Docker, removendo containers, volumes e redes
	@echo "Pruning Docker system..."
	@printf "\033[33mAtenção: Isso irá remover todos os containers, volumes e redes do Docker!\033[0m\n"; \
	printf "\033[33mDeseja continuar? (s/N): \033[0m"; \
	read resposta; \
	if [ "$$resposta" != "s" ] && [ "$$resposta" != "S" ]; then \
		printf "\n\033[41;97m⚠️  Operação cancelada. ⚠️  \033[0m\n"; \
		exit 0; \
	else \
		printf "\n\033[32mContinuando com a limpeza do sistema Docker...\033[0m\n"; \
		printf "\n\033[32mRemovendo containers, volumes e redes...\033[0m\n"; \
		docker system prune -f --all --volumes; \
		docker system prune -f --volumes; \
		printf "\033[32mContainers, volumes e redes removidos com sucesso!\033[0m\n"; \
		printf "\033[33mLimpando o sistema Docker...\033[0m\n"; \
		printf "\033[33mIsso pode levar alguns minutos, aguarde...\033[0m\n"; \
		printf "\033[33mDeseja continuar? (s/N): \033[0m"; \
		read resposta2; \
		if [ "$$resposta2" != "s" ] && [ "$$resposta2" != "S" ]; then \
			printf "\n\033[41;97m⚠️  Operação cancelada. ⚠️  \033[0m\n"; \
			exit 0; \
		else \
			printf "\n\033[32mIniciando a limpeza do sistema Docker...\033[0m\n"; \
			printf "\033[32mIsso pode levar alguns minutos, aguarde...\033[0m\n"; \
			docker volume prune -f; \
			docker network prune -f; \
			docker image prune -f; \
			docker container prune -f; \
			printf "\033[32mTudo limpo!\033[0m\n"; \
		fi \
	fi

## —— Mensagens 📝
msg_success: ## Mensagem de sucesso
	@printf "\033[32mProjeto iniciado com sucesso!\033[0m\n"

msg_error: ## Mensagem de erro
	@printf "\033[31mOcorreu um erro!\033[0m\n"

## —— Ajuda 🆘
help: ## Mostra os comandos disponíveis:
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) \
	| awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-24s\033[0m %s\n", $$1, $$2}' \
	| sed -e 's/\[32m## /[33m/' && printf "\n"