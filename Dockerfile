# Base image
FROM php:8.2-apache

# Install dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libzip-dev \
    zip \
    unzip \
    curl \
    default-mysql-client \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-enable pdo_mysql \
    && a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY ./app /var/www/html

# Install additional libraries for CSS/JS handling
RUN apt-get install -y wget && \
    wget https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.css -P /var/www/html/css/ && \
    wget https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css -P /var/www/html/css/ && \
    wget https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js -P /var/www/html/js/ && \
    wget https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js -P /var/www/html/js/ && \
    wget https://code.jquery.com/jquery-3.4.1.min.js -O /var/www/html/js/jquery-3.4.1.slim.min.js && \
    wget https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js -O /var/www/html/js/popper.min.js && \
    wget https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js -O /var/www/html/js/bootstrap.min.js

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html

# Expose port 80
EXPOSE 80
