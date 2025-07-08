#!/bin/bash

echo "🚂 Railway QloApps Startup Script"

# Check if settings.inc.php exists, if not, copy from Railway template
if [ ! -f "/var/www/html/config/settings.inc.php" ]; then
    echo "📋 Creating settings.inc.php from Railway template..."
    cp /var/www/html/config/settings.railway.inc.php /var/www/html/config/settings.inc.php
fi

# Set proper permissions
chmod -R 777 /var/www/html/cache
chmod -R 777 /var/www/html/log
chmod -R 777 /var/www/html/upload
chmod -R 777 /var/www/html/download
chmod -R 777 /var/www/html/img
chmod -R 755 /var/www/html/config

# Create necessary directories
mkdir -p /var/www/html/cache/smarty/cache
mkdir -p /var/www/html/cache/smarty/compile
mkdir -p /var/www/html/log
mkdir -p /var/www/html/upload
mkdir -p /var/www/html/download

echo "✅ QloApps setup completed for Railway!"

# Start Apache
exec apache2-foreground
