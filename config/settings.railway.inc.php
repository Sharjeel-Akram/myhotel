<?php
/**
 * QloApps Database Configuration Template
 * This file will be created after Railway deployment with actual database credentials
 */

// Database configuration - these will be populated from Railway environment variables
define('_DB_SERVER_', getenv('MYSQL_HOST') ?: 'localhost');
define('_DB_PORT_', getenv('MYSQL_PORT') ?: '3306');
define('_DB_NAME_', getenv('MYSQL_DATABASE') ?: 'qloapps');
define('_DB_USER_', getenv('MYSQL_USER') ?: 'root');
define('_DB_PASSWD_', getenv('MYSQL_PASSWORD') ?: '');
define('_MYSQL_ENGINE_', 'InnoDB');
define('_PS_CACHING_SYSTEM_', 'CacheMemcache');
define('_PS_CACHE_ENABLED_', '0');
define('_MEDIA_SERVER_1_', '');
define('_MEDIA_SERVER_2_', '');
define('_MEDIA_SERVER_3_', '');
define('_COOKIE_KEY_', getenv('PS_COOKIE_KEY') ?: 'your_random_cookie_key_here');
define('_COOKIE_IV_', getenv('PS_COOKIE_IV') ?: 'your_random_iv_here');
define('_PS_CREATION_DATE_', '2025-07-09');

// Get domain from Railway environment or default
$domain = getenv('RAILWAY_STATIC_URL') ?: getenv('RAILWAY_PUBLIC_DOMAIN') ?: 'localhost:8000';
define('_PS_DOMAIN_', $domain);
define('_PS_DOMAIN_SSL_', $domain);

define('_PS_SHOP_DOMAIN_', $domain);
define('_PS_SHOP_DOMAIN_SSL_', $domain);

define('_PS_ENABLE_SSL_', false);
define('_PS_COOKIE_DOMAIN_', '.'.$domain);

define('_PS_ADMIN_DIR_', 'admin955dxqibz');
define('_PS_ADMIN_PROFILE_', '1');

// Development settings for Railway
define('_PS_MODE_DEV_', false);
define('_PS_DEBUG_SQL_', false);
define('_PS_DEBUG_PROFILING_', false);

// Cache settings
define('_PS_SMARTY_CACHE_', '1');
define('_PS_SMARTY_CACHING_TYPE_', 'filesystem');
define('_PS_SMARTY_CONSOLE_', '0');
define('_PS_SMARTY_CONSOLE_KEY_', 'SMARTY_DEBUG');

// Security
define('_RIJNDAEL_KEY_', getenv('PS_RIJNDAEL_KEY') ?: 'your_rijndael_key_here');
define('_RIJNDAEL_IV_', getenv('PS_RIJNDAEL_IV') ?: 'your_rijndael_iv_here');

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
?>
