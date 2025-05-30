# Dockerfile
FROM php:8.2-fpm

# 1) System deps + PostgreSQL client & headers
RUN apt-get update && apt-get install -y \
    git unzip zip curl \
    libicu-dev libonig-dev libzip-dev libxml2-dev \
    default-mysql-client postgresql-client libpq-dev \
  && rm -rf /var/lib/apt/lists/* \
  && docker-php-ext-install \
       intl pdo pdo_mysql pdo_pgsql pgsql zip opcache

# 2) Composer & Symfony CLI
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN curl -sS https://get.symfony.com/cli/installer | bash \
  && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

WORKDIR /var/www/html

# 3) Copy app code
COPY . /var/www/html

# 4) Ensure www-data owns files
RUN chown -R www-data:www-data /var/www/html

# 5) Default command (overridden by docker-compose)
CMD ["php-fpm"]
