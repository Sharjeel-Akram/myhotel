<?php
// Simple health check endpoint
header('Content-Type: application/json');

$health = [
    'status' => 'ok',
    'timestamp' => date('c'),
    'php_version' => PHP_VERSION,
    'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown'
];

// Check database connection if settings exist
if (file_exists(__DIR__ . '/config/settings.inc.php')) {
    try {
        require_once(__DIR__ . '/config/settings.inc.php');
        
        if (defined('_DB_SERVER_') && defined('_DB_USER_') && defined('_DB_PASSWD_') && defined('_DB_NAME_')) {
            $pdo = new PDO(
                "mysql:host=" . _DB_SERVER_ . ";port=" . (_DB_PORT_ ?? 3306) . ";dbname=" . _DB_NAME_,
                _DB_USER_,
                _DB_PASSWD_,
                [PDO::ATTR_TIMEOUT => 5]
            );
            $health['database'] = 'connected';
        } else {
            $health['database'] = 'not_configured';
        }
    } catch (Exception $e) {
        $health['database'] = 'error: ' . $e->getMessage();
    }
} else {
    $health['database'] = 'settings_missing';
}

http_response_code(200);
echo json_encode($health, JSON_PRETTY_PRINT);
?>
