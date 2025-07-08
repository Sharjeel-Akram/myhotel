FROM php:8.1-apache

# Install required PHP extensions and tools
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    default-mysql-client \
    unzip \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install pdo pdo_mysql mysqli curl zip xml soap simplexml \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && mkdir -p /var/www/html/cache /var/www/html/log /var/www/html/download /var/www/html/upload /var/www/html/img/tmp \
    && chmod -R 777 /var/www/html/cache \
    && chmod -R 777 /var/www/html/log \
    && chmod -R 777 /var/www/html/download \
    && chmod -R 777 /var/www/html/upload \
    && chmod -R 777 /var/www/html/img \
    && chmod -R 755 /var/www/html/config

# Create .htaccess for URL rewriting
RUN echo 'RewriteEngine On\nRewriteCond %{REQUEST_FILENAME} !-f\nRewriteCond %{REQUEST_FILENAME} !-d\nRewriteRule ^(.*)$ index.php [QSA,L]' > /var/www/html/.htaccess

# Configure Apache for Railway
RUN echo 'ServerName localhost' >> /etc/apache2/apache2.conf \
    && echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html\n\
    ServerName localhost\n\
    <Directory /var/www/html>\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Copy and setup startup script
COPY railway-start.sh /usr/local/bin/railway-start.sh
RUN chmod +x /usr/local/bin/railway-start.sh

# Expose port 80 (Railway will map to its port)
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=60s --retries=3 \
    CMD curl -f http://localhost:80/ || exit 1

# Start with our custom script
CMD ["/usr/local/bin/railway-start.sh"]
