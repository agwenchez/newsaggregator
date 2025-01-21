# Use official PHP 8.4 image with FPM
FROM php:8.4-fpm

# Install system dependencies and clear cache
RUN apt-get update && apt-get install -y \
    curl \
    git \
    libonig-dev \
    libpq-dev \
    libpng-dev \
    libxml2-dev \
    unzip \
    && docker-php-ext-install \
    pdo \
    pdo_pgsql \
    mbstring \
    bcmath \
    gd \
    curl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer (use official Composer image to get the latest version)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files into the container
COPY . .

# Set permissions for Laravel folders and install packages with composer
RUN chown -R 775 www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && composer install
