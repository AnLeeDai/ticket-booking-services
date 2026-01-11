# Laravel 12 (PHP 8.3) production image for Render (Nginx + PHP-FPM in one container)
FROM php:8.3-fpm-bookworm

ENV DEBIAN_FRONTEND=noninteractive \
    COMPOSER_ALLOW_SUPERUSER=1 \
    PHP_OPCACHE_VALIDATE_TIMESTAMPS=0

# System deps + Nginx + Supervisor
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates \
        curl \
        gettext-base \
        git \
        unzip \
        nginx \
        supervisor \
        libicu-dev \
        libonig-dev \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions commonly needed by Laravel
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        bcmath \
        gd \
        intl \
        mbstring \
        opcache \
        pdo_mysql \
        zip

# Recommended OPcache defaults for production
RUN { \
      echo "opcache.enable=1"; \
      echo "opcache.enable_cli=0"; \
      echo "opcache.memory_consumption=128"; \
      echo "opcache.interned_strings_buffer=16"; \
      echo "opcache.max_accelerated_files=20000"; \
      echo "opcache.revalidate_freq=0"; \
      echo "opcache.validate_timestamps=${PHP_OPCACHE_VALIDATE_TIMESTAMPS}"; \
    } > /usr/local/etc/php/conf.d/opcache-recommended.ini

# Install Composer (latest stable)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

# Copy only composer files first for better layer caching
COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --no-scripts \
    --optimize-autoloader

# Copy application source
COPY . .

# Put docker assets in a stable path (outside app root is fine too)
RUN mkdir -p /var/www/docker \
    && cp -r ./docker/* /var/www/docker/ \
    && rm -rf ./docker

# Laravel writable dirs + correct perms
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Supervisor config
RUN mkdir -p /etc/supervisor/conf.d \
    && cp /var/www/docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Expose default; Render provides PORT at runtime
EXPOSE 8080

# Start script renders Nginx config using $PORT and starts supervisor
RUN chmod +x /var/www/docker/start.sh

CMD ["/var/www/docker/start.sh"]
