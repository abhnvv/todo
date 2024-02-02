# Use an official PHP runtime as a parent image
FROM php:8.2-fpm

# Set the working directory in the container
WORKDIR /var/www/html

# Install dependencies
RUN apt-get update && \
    apt-get install -y git zip unzip && \
    docker-php-ext-install pdo pdo_mysql && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy the composer.json and composer.lock files to install dependencies
COPY composer.json composer.lock ./

# Install Symfony application dependencies
RUN composer install --no-scripts --no-autoloader

# Copy the rest of the application code
COPY . .

# Run Composer scripts
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative

# Expose port 9000 to communicate with PHP-FPM
EXPOSE 9000

# Start PHP-FPM server
CMD ["php-fpm"]

# # Use an official PHP runtime as a parent image
# FROM php:8.2-apache

# # Set the working directory in the container
# WORKDIR /app

# # Copy the application files into the container
# # COPY . .
# COPY . /app

# # Install Composer
# RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# # Install any dependencies needed by your application
# RUN apt-get update && apt-get install -y \
#     libpq-dev \
#     && docker-php-ext-install pdo pdo_mysql

# # Copy composer files and install dependencies
# COPY composer.json ./
# RUN composer install --no-scripts --no-autoloader

# # Expose port 80 to allow incoming connections
# EXPOSE 8000

# # Start the Apache web server when the container starts
# CMD ["apache2-foreground"]
# # CMD php bin/console server:run 0.0.0.0:8000
# # CMD symfony server:start

