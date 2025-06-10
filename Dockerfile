# --- Stage 1: Node build for frontend assets ---
FROM node:18-alpine AS node-build
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY resources ./resources
COPY vite.config.js ./vite.config.js
COPY tailwind.config.js ./tailwind.config.js
COPY postcss.config.js ./postcss.config.js
RUN npm run build

# --- Stage 2: Composer build for PHP dependencies ---
FROM composer:2 AS composer-build
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --prefer-dist --no-interaction --optimize-autoloader

# --- Stage 3: Production image ---
FROM php:8.2-fpm-alpine

# Install system dependencies and PHP extensions
RUN apk add --no-cache \
    nginx \
    bash \
    libpng \
    libpng-dev \
    libjpeg-turbo \
    libjpeg-turbo-dev \
    freetype \
    freetype-dev \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    zlib-dev \
    libxml2-dev \
    curl \
    git \
    supervisor \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql zip gd mbstring exif pcntl bcmath intl xml

# Set working directory
WORKDIR /var/www/html

# Copy application code
COPY . .

# Remove all Laravel cache files except .gitignore to prevent dev-only provider errors
RUN find bootstrap/cache -type f ! -name '.gitignore' -delete

# Copy built assets from node-build
COPY --from=node-build /app/public/build ./public/build

# Copy vendor from composer-build
COPY --from=composer-build /app/vendor ./vendor

# Copy nginx and supervisord configs
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf

# Set permissions for Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Expose port 8080 for Cloud Run
EXPOSE 8080

# Start nginx and php-fpm via supervisord
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]

# Healthcheck (optional)
HEALTHCHECK --interval=30s --timeout=5s --start-period=5s CMD wget -q --spider http://localhost:8080/ || exit 1

# ---
# Note:
# - Provide your .env file at deploy time (do not bake into image)
# - You may need to create docker/nginx.conf and docker/supervisord.conf for this setup
