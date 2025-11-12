FROM php:8.3-apache

# --- Install required dependencies and PHP extensions ---
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libicu-dev \
    unzip \
    git \
    && docker-php-ext-install intl pdo pdo_pgsql pgsql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# --- Enable Apache modules ---
RUN a2enmod rewrite headers

# --- Set document root to CodeIgniter's /public ---
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# --- Optional: Configure Apache to handle forwarded requests correctly ---
RUN echo "\n\
    <Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
    </Directory>\n\
    \n\
    SetEnvIf X-Forwarded-Proto https HTTPS=on\n\
    " >> /etc/apache2/apache2.conf

# --- Copy application code ---
WORKDIR /var/www/html
COPY . /var/www/html

# --- Fix permissions for writable/ directory ---
RUN chown -R www-data:www-data /var/www/html/writable \
    && chmod -R 775 /var/www/html/writable

# --- Configure ServerName to suppress warnings ---
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# --- Expose Apache port ---
EXPOSE 80

# --- Start Apache ---
CMD ["apache2-foreground"]
