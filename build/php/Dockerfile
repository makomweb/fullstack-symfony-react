# -----------------------------------
# Base-Stage (Development - Debian for XDebug)
# -----------------------------------
FROM php:8.4-fpm AS base

WORKDIR /var/www/project

RUN apt-get update && apt-get install -y --no-install-recommends \
    build-essential autoconf libicu-dev git libzip-dev zip librabbitmq-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl pdo opcache pdo_mysql zip \
    && pecl install opentelemetry redis amqp \
    && docker-php-ext-enable opentelemetry redis amqp \
    && apt-get remove --purge -y build-essential autoconf \
    && apt-get autoremove -y \
    && rm -rf /var/lib/apt/lists/*

COPY build/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# -----------------------------------
# Base-Alpine-Stage (Production - Alpine for smaller image)
# -----------------------------------
FROM php:8.4-fpm-alpine AS base-alpine

WORKDIR /var/www/project

RUN apk add --no-cache --virtual .build-deps \
    build-base autoconf icu-dev libzip-dev rabbitmq-c-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl pdo opcache pdo_mysql zip \
    && pecl install opentelemetry redis amqp \
    && docker-php-ext-enable opentelemetry redis amqp \
    && apk del .build-deps \
    && rm -rf /tmp/* /var/cache/apk/*

COPY build/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# -----------------------------------
# Frontend-Stage (Node + Vite build)
# -----------------------------------
FROM node:20-alpine AS frontend

WORKDIR /var/www/project/frontend

COPY frontend/package*.json ./
RUN npm ci

COPY frontend/ .

# Build React app with Vite
RUN npm run build

# -----------------------------------
# Development-Stage (with Composer + XDebug)
# -----------------------------------
FROM base AS dev

RUN echo "memory_limit=512M" > /usr/local/etc/php/conf.d/memory-limit.ini

COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

RUN apt-get update && apt-get install -y --no-install-recommends \
    build-essential autoconf git zip unzip \
    && pecl install xdebug && docker-php-ext-enable xdebug \
    && echo "xdebug.mode=debug,coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && apt-get remove --purge -y build-essential autoconf \
    && apt-get autoremove -y \
    && rm -rf /var/lib/apt/lists/*

# -----------------------------------
# Vendor-Stage (for Production)
# -----------------------------------
FROM base AS vendor

COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

COPY backend/composer.json .
COPY backend/composer.lock .
COPY backend/.env .env
COPY backend/bin ./bin
COPY backend/src ./src
COPY backend/public ./public

ENV COMPOSER_ALLOW_SUPERUSER=1

# https://getcomposer.org/doc/articles/autoloader-optimization.md#optimization-level-2-a-authoritative-class-maps
RUN apt-get update && apt-get install -y --no-install-recommends \
    libicu-dev git zip unzip \
    && composer install \
        --no-dev \
        --optimize-autoloader \
        --classmap-authoritative \
    && apt-get remove --purge -y git zip unzip \
    && apt-get autoremove -y \
    && rm -rf /var/lib/apt/lists/*

# -----------------------------------
# Production-Stage (Alpine - no Composer, no XDebug)
# -----------------------------------
FROM base-alpine AS prod

COPY --from=vendor /var/www/project/vendor ./vendor

COPY backend/.env .
COPY backend/bin ./bin
COPY backend/config ./config
COPY backend/migrations ./migrations
COPY backend/public ./public
COPY backend/src ./src
COPY backend/symfony.lock .
COPY backend/templates ./templates

# Copy built React frontend from frontend stage
COPY --from=frontend /var/www/project/frontend/dist ./public/dist
