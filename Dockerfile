FROM node:20-alpine AS node-builder
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . ./
RUN npm run build

FROM php:8.2-apache
WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    libpq-dev \
    libicu-dev \
    unzip \
    git \
 && docker-php-ext-configure intl \
 && docker-php-ext-install pdo pdo_pgsql intl \
 && apt-get clean && rm -rf /var/lib/apt/lists/*

ENV APACHE_DOCUMENT_ROOT="/var/www/html/public"
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
 && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN a2enmod rewrite \
 && echo "FallbackResource /index.php" >> /etc/apache2/apache2.conf

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock symfony.lock ./
ENV APP_ENV=prod
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist --optimize-autoloader

COPY . .
COPY --from=node-builder /app/public/build ./public/build

RUN composer dump-env prod --empty

ENV DATABASE_URL="postgresql://fake:fake@fake:5432/fake"
ENV APP_SECRET="fake-secret-for-build-because-symfony-cannot-live-without-it-apparently"

RUN composer run-script post-install-cmd

RUN chown -R www-data:www-data var/ public/
