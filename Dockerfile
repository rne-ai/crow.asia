# Stage 1: Build Frontend Assets (React 19.3 + Tailwind CSS v4.3 + Remotion v4.0)
FROM node:24-alpine AS frontend-builder
WORKDIR /app
COPY package.json package-lock.json* ./
RUN npm ci
COPY resources/ ./resources/
COPY public/ ./public/
COPY vite.config.ts tsconfig.json ./
RUN npm run build

# Stage 2: Production Runtime (FrankenPHP 1.13 + PHP 8.5 Worker Mode)
FROM dunglas/frankenphp:1.13-php8.5-alpine

RUN install-php-extensions \
    pdo_pgsql \
    pgsql \
    redis \
    intl \
    zip \
    opcache \
    pcntl \
    bcmath

COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-autoloader --no-scripts --prefer-dist

COPY . .
COPY --from=frontend-builder /app/public/build ./public/build

RUN composer dump-autoload --optimize --classmap-authoritative \
    && chown -R www-data:www-data /app/storage /app/bootstrap/cache

ENV SERVER_NAME=":8000"
ENV OCTANE_SERVER="frankenphp"
EXPOSE 8000

CMD ["php", "artisan", "octane:frankenphp", "--host=0.0.0.0", "--port=8000", "--workers=4", "--max-requests=1000"]
