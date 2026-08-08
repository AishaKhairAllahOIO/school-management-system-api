FROM php:8.2-cli

RUN apt-get update \
    && apt-get install -y \
        git \
        unzip \
        libzip-dev \
        libicu-dev \
        libonig-dev \
        libxml2-dev \
    && docker-php-ext-install \
        zip \
        pdo_mysql \
        mbstring \
        bcmath \
        intl \
        xml \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

RUN composer install \
    --optimize-autoloader \
    --no-interaction \
    --no-dev

EXPOSE 8080

CMD php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
