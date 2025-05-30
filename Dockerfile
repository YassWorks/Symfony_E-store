# Dockerfile
FROM php:8.2-fpm

# Install system dependencies, MySQL client (for scripts), PostgreSQL client + headers
RUN apt-get update && apt-get install -y \
    git unzip zip curl libicu-dev libonig-dev libzip-dev libxml2-dev \
    default-mysql-client postgresql-client libpq-dev \
  && docker-php-ext-install \
       intl \
       pdo \
       pdo_mysql \
       pdo_pgsql \
       pgsql \
       zip \
       opcache \
  && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash \
  && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

WORKDIR /var/www/html

# Copy application code
COPY . /var/www/html

# Copy & make entrypoint executable
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh \
  && chown -R www-data:www-data /var/www/html

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
