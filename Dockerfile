# Stage 1: Use an official Node.js image to access npm and Node.js
FROM node:20-slim AS node

# Use the official PHP 8.2 FPM image as the base image
FROM php:8.2-fpm

# Copy the Node.js and npm executables from the Node.js image
COPY --from=node /usr/local/bin/node /usr/local/bin/node
COPY --from=node /usr/local/bin/npm /usr/local/bin/npm

# Copy the Node.js libraries
COPY --from=node /usr/local/lib/node_modules /usr/local/lib/node_modules

# Optionally, set the PATH environment variable to include npm binaries
ENV PATH="/usr/local/lib/node_modules/npm/bin:$PATH"

# Set environment variables
ENV PHP_OPCACHE_ENABLE=1
ENV PHP_OPCACHE_ENABLE_CLI=0
ENV PHP_OPCACHE_VALIDATE_TIMESTAMPS=0
ENV PHP_OPCACHE_REVALIDATE_FREQ=0

# Set environment variables for non-interactive installations
ENV DEBIAN_FRONTEND=noninteractive

# Update and install all required system dependencies and PHP extensions in a single step.
RUN apt-get update && apt-get install -y \
    nodejs \
    npm \
    unzip \
    curl \
    libpq-dev \
    libcurl4-gnutls-dev \
    libonig-dev \
    libzip-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libpng-dev \
    libmagickwand-dev \
    libssl-dev \
    bash \
    tzdata \
    supervisor \
    default-mysql-client \
    libssh2-1-dev \
    && pecl install redis \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip mysqli pdo pdo_mysql bcmath curl opcache mbstring pcntl ftp \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Copy composer executable.
COPY --from=composer:2.4.2 /usr/bin/composer /usr/bin/composer

# Copy configuration files.
COPY ./docker/php/php.ini /usr/local/etc/php/php.ini
COPY ./docker/php/php-fpm.conf /usr/local/etc/php-fpm.d/zz-laravel.conf

# Adjust user permission & group and create appuser with necessary groups and shell
RUN usermod --uid 1000 www-data && \
    groupmod --gid 1000 www-data && \
    useradd -u 1001 -m -s /bin/bash -G www-data appuser && \
    groupadd -g 1001 appuser || true && \
    usermod -aG appuser appuser

# Set the working directory
WORKDIR /var/www

# Install Nginx
RUN apt-get update && apt-get install -y \
   nginx \
   && apt-get clean \
   && rm -rf /var/lib/apt/lists/*

# Copy your Nginx configuration file (if any)
COPY ./docker/nginx/nginx.conf /etc/nginx/nginx.conf

# Set the working directory
WORKDIR /var/www/app

# Ensure /var/www is empty and set ownership for www-data
RUN chown -R www-data:www-data /var/www

# Switch to the non-root user
USER www-data

# Install Laravel 8 into the existing directory
RUN composer create-project --prefer-dist laravel/laravel:^8.0 .

# Switch back to root for permission settings and cleanup
USER root

RUN chown -R www-data:www-data ./storage ./bootstrap/cache \
    && chmod -R 775 ./storage ./bootstrap/cache

# Copy files from current folder to container current folder (set in workdir).
COPY --chown=www-data:www-data ../public ./public/test
COPY --chown=www-data:www-data ../public /var/web-test
RUN cp -r . /var/app

# Switch to the non-root user
USER www-data

# Set the working directory
WORKDIR /var/www/app/public/test

# Switch back to root for permission settings and cleanup
USER root

# Set the working directory
WORKDIR /var/www/app

# Check alive
RUN echo "I am alive! $(date)" > ./public/check-alive.txt

# Copy supervisor files.
COPY ./docker/supervisord/nginx.conf /etc/supervisor/conf.d/nginx.conf
COPY ./docker/supervisord/php-fpm.conf /etc/supervisor/conf.d/php-fpm.conf
RUN chmod -R +x /etc/supervisor/conf.d

# Remove the directory if it exists
RUN rm -rf ./public

# Copy files from current folder to container current folder (set in workdir).
COPY --chown=www-data:www-data ./ .

# Copy the original web-app directory to a backup location
RUN if [ ! -e ./.env ]; then \
        cp -r /var/web-app ./; \
    fi

RUN chown -R www-data:www-data ./storage ./bootstrap/cache \
    && chmod -R 775 ./storage ./bootstrap/cache

# Copy the files from /var/webtest to ./public/test
# RUN mkdir -p ./public/test && cp -r /var/web-test/* ./public/test/

# Check alive
# Generate a random string
RUN RANDOM_STRING=$(openssl rand -hex 8) && \
    echo -n "I am alive! | $(date) | $RANDOM_STRING" >> ./public/check-alive.txt

# Copy laravel web-start-up.sh
RUN chmod +x ./*.sh

# Set the CMD to run the shell script
CMD ["/var/www/app/web-start-up.sh"]
