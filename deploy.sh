#!/bin/bash

echo "🚀 Starting QloApps deployment setup..."

# Create required directories with proper permissions
echo "📁 Creating required directories..."
mkdir -p cache/smarty/cache
mkdir -p cache/smarty/compile
mkdir -p log
mkdir -p upload
mkdir -p download
mkdir -p img/tmp

# Set permissions
echo "🔒 Setting permissions..."
chmod -R 777 cache/
chmod -R 777 log/
chmod -R 777 upload/
chmod -R 777 download/
chmod -R 777 img/
chmod -R 777 config/

# Create empty index.php files for security
echo "🛡️ Creating security files..."
echo "<?php header('HTTP/1.1 403 Forbidden'); exit; ?>" > cache/index.php
echo "<?php header('HTTP/1.1 403 Forbidden'); exit; ?>" > log/index.php
echo "<?php header('HTTP/1.1 403 Forbidden'); exit; ?>" > upload/index.php
echo "<?php header('HTTP/1.1 403 Forbidden'); exit; ?>" > download/index.php

echo "✅ Deployment setup completed!"
echo "📋 Next steps:"
echo "1. Set up your database connection in config/settings.inc.php"
echo "2. Configure your environment variables"
echo "3. Run the QloApps installer"
