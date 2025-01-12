FROM php:8.1-fpm-buster

# Atualizar e instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev zip git libicu-dev gnupg lsb-release curl \
    libssl-dev

# Instalar a extensão Redis do PECL
RUN pecl install redis && \
    docker-php-ext-enable redis

# Instalar e configurar o New Relic
RUN curl -o /tmp/newrelic.tar.gz https://download.newrelic.com/php_agent/release/newrelic-php5-11.4.0.17-linux.tar.gz >> /tmp/build.log 2>&1 && \
    tar -xzvf /tmp/newrelic.tar.gz -C /tmp && \
    NEWRELIC_DIR=$(ls -d /tmp/newrelic-php5* | head -n 1) && \
    echo "Diretório do New Relic: $NEWRELIC_DIR" && \
    chmod +x $NEWRELIC_DIR/newrelic-install && \
    NR_INSTALL_SILENT=1 $NEWRELIC_DIR/newrelic-install install

# Configurar e instalar extensões do PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd pdo pdo_mysql intl opcache

# Definir diretório de trabalho
WORKDIR /var/www

# Copiar o Composer para o contêiner
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copiar os arquivos da aplicação
COPY . .

# Rodar o composer para instalar as dependências
RUN composer install --no-dev --optimize-autoloader

# Dar permissão para as pastas storage e cache
RUN chmod -R 777 /var/www/storage /var/www/bootstrap/cache

# Expor a porta 9000 para o PHP-FPM
EXPOSE 9000

# Definir o comando de inicialização do contêiner
CMD ["php-fpm"]
