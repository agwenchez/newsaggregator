# Use official PHP 8.4 image with FPM
FROM php:8.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpq-dev \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    zlib1g-dev \
    libxslt-dev \
    nginx \
    && docker-php-ext-install \
    pdo \
    pdo_pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    curl \
    xsl

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer (use official Composer image to get the latest version)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files into the container
COPY . .


# Set permissions for Laravel folders
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# # Expose port 9000 for PHP-FPM
# EXPOSE 9000
