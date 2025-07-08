#!/bin/bash

echo "🚂 Railway QloApps Startup Script"

# Set default port if not provided (Railway will override this)
export PORT=${PORT:-80}
echo "🔌 Port configuration: $PORT"

# Check if settings.inc.php exists, if not, copy from Railway template
if [ ! -f "/var/www/html/config/settings.inc.php" ]; then
    echo "📋 Creating settings.inc.php from Railway template..."
    cp /var/www/html/config/settings.railway.inc.php /var/www/html/config/settings.inc.php
fi

# Set proper permissions
echo "🔒 Setting up permissions..."
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html
chmod -R 777 /var/www/html/cache
chmod -R 777 /var/www/html/log
chmod -R 777 /var/www/html/upload
chmod -R 777 /var/www/html/download
chmod -R 777 /var/www/html/img
chmod -R 755 /var/www/html/config

# Create necessary directories
echo "📁 Creating directories..."
mkdir -p /var/www/html/cache/smarty/cache
mkdir -p /var/www/html/cache/smarty/compile
mkdir -p /var/www/html/log
mkdir -p /var/www/html/upload
mkdir -p /var/www/html/download

# Create index.php files for security
echo "🛡️ Setting up security..."
echo "<?php header('HTTP/1.1 403 Forbidden'); exit; ?>" > /var/www/html/cache/index.php
echo "<?php header('HTTP/1.1 403 Forbidden'); exit; ?>" > /var/www/html/log/index.php

echo "✅ QloApps setup completed for Railway!"
echo "🌐 Starting Apache on port 80 (Railway will handle port mapping)"

# Start Apache in foreground
exec apache2-foreground
