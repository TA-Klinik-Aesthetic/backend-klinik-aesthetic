FROM php:8.1-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy the entire project
COPY . /var/www

# Create startup script
RUN echo '#!/bin/bash\n\
php artisan migrate --force\n\
php artisan db:seed --force\n\
php-fpm\n\
' > /usr/local/bin/startup.sh

RUN chmod +x /usr/local/bin/startup.sh

# Set permissions for Laravel
RUN chmod -R 777 /var/www/storage /var/www/bootstrap/cache

EXPOSE 9000
CMD ["/usr/local/bin/startup.sh"]
