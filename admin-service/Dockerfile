FROM php:8.4-apache

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libpq-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip sockets

#instala o mysql-client 
RUN apt-get install -y default-mysql-client

# Instalar o deck para a sincronização do Kong yml com o postgres
RUN curl -sL https://github.com/kong/deck/releases/download/v1.49.1/deck_1.49.1_linux_amd64.tar.gz -o deck.tar.gz
RUN tar -xf deck.tar.gz -C /tmp
RUN cp /tmp/deck /usr/local/bin/

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar Apache
RUN a2enmod rewrite headers
COPY apache.conf /etc/apache2/sites-available/000-default.conf

# Definir diretório de trabalho
WORKDIR /var/www/html

# Copiar arquivos do projeto
COPY . /var/www/html/

# Instalar dependências PHP
RUN composer install --optimize-autoloader

# Criar arquivo .env se não existir
RUN if [ ! -f .env ]; then cp .env.example .env 2>/dev/null || echo "APP_ENV=production" > .env; fi

# Configurar permissões
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expor porta
EXPOSE 80

# Comando de inicialização
CMD ["apache2-foreground"]

