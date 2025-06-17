# start a makefile with the following content
# This Makefile start a development environment and is used in linux 
# to build the project
.PHONY: all build clean run

all: build composer-inst-all-conteiners shared-install tests-install migrate-sql seeder-sql
clean-all: clean-all-composer clean-everything

build:
	@echo "Building the project..."
	COMPOSE_BAKE=true docker-compose up --no-deps --force-recreate --build -d

# instala o composer nas pastas de todos os containers
composer-inst-all-conteiners:
	@echo "Installing composer dependencies in all containers..."
	docker-compose exec -T admin-service bash -c "composer install --no-interaction --optimize-autoloader"
	docker-compose exec -T auth-service bash -c "composer install --no-interaction --optimize-autoloader"
	docker-compose exec -T customer-service bash -c "composer install --no-interaction --optimize-autoloader"
	docker-compose exec -T payment-service bash -c "composer install --no-interaction --optimize-autoloader"
	docker-compose exec -T reservation-service bash -c "composer install --no-interaction --optimize-autoloader"
	docker-compose exec -T saga-orchestrator bash -c "composer install --no-interaction --optimize-autoloader"
	docker-compose exec -T sales-service bash -c "composer install --no-interaction --optimize-autoloader"
	docker-compose exec -T vehicle-service bash -c "composer install --no-interaction --optimize-autoloader"

# Instala as dependências do projeto na pasta shared através do container admin-service
shared-install:
	@echo "Installing shared dependencies..."
	docker-compose exec -T admin-service bash -c "cd shared && composer install --no-interaction --optimize-autoloader"

tests-install:
	@echo "Installing test dependencies..."
	docker-compose exec -T admin-service bash -c "cd tests && composer install --no-interaction --optimize-autoloader"

# Efetua a migração de todos os bancos de dados através do container admin-service
migrate-sql:
	@echo "Running SQL migration..."
	docker-compose exec -T  admin-service php shared/database/migrate.php

seeder-sql:
	@echo "Running SQL seeder..."
	docker-compose exec -T  admin-service php shared/database/seed.php

clean-all-composer:
	@echo "Cleaning all composer lock files and vendor directories..."
	docker-compose exec -T admin-service bash -c "rm -rf vendor composer.lock"
	docker-compose exec -T auth-service bash -c "rm -rf vendor composer.lock"
	docker-compose exec -T customer-service bash -c "rm -rf vendor composer.lock"
	docker-compose exec -T payment-service bash -c "rm -rf vendor composer.lock"
	docker-compose exec -T reservation-service bash -c "rm -rf vendor composer.lock"
	docker-compose exec -T saga-orchestrator bash -c "rm -rf vendor composer.lock"
	docker-compose exec -T sales-service bash -c "rm -rf vendor composer.lock"
	docker-compose exec -T vehicle-service bash -c "rm -rf vendor composer.lock"
	docker-compose exec -T admin-service bash -c "cd shared && rm -rf vendor composer.lock"
	docker-compose exec -T admin-service bash -c "cd tests && rm -rf vendor composer.lock"

clean-everything:
	@echo "Cleaning everything..."
	docker-compose down --volumes --remove-orphans