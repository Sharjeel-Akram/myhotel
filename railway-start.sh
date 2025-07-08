#!/bin/bash

echo "🚂 Railway QloApps Startup Script"

# Set default port if not provided
export PORT=${PORT:-80}
echo "🔌 Using port: $PORT"

# Update Apache configuration with the dynamic port
sed -i "s/\${PORT}/$PORT/g" /etc/apache2/sites-available/railway.conf

# Configure Apache to listen on the dynamic port
echo "Listen $PORT" > /etc/apache2/ports.conf

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
