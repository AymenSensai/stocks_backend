# Use an official PHP image as a parent image
FROM php:8.2-fpm

# Set the working directory inside the container
WORKDIR /var/www

# Install system dependencies and PHP extensions required for Laravel
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql

# Install Composer (PHP dependency manager)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy the Laravel project files into the container
COPY . .

# Install PHP dependencies using Composer
RUN composer install --no-interaction --optimize-autoloader --prefer-dist

# Expose the port on which the app will run (usually 80 or 9000)
EXPOSE 9000

# Start the PHP FPM server
CMD ["php-fpm"]
