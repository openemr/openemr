<?php
/**
 * Local PHP Configuration for OpenEMR Docker Integration
 * Seamless connection between local PHP and Docker services
 * Author: Dang Tran <tqvdang@msn.com>
 */

// Database connection for local PHP to Docker MariaDB
$GLOBALS['OE_SITE_DIR'] = '/Users/dang/dev/openemr/sites/default';

// Database configuration
$GLOBALS['openemr_docker_env'] = [
    'host' => '127.0.0.1',
    'port' => '3306',
    'database' => 'openemr',
    'username' => 'openemr',
    'password' => 'openemr',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_vietnamese_ci',
    'dsn_options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];

// Redis configuration for sessions
$GLOBALS['redis_config'] = [
    'host' => '127.0.0.1',
    'port' => 6380,
    'password' => 'openemr_redis',
    'database' => 0,
    'timeout' => 30,
    'read_timeout' => 30,
    'persistent' => true,
    'prefix' => 'openemr_session:'
];

// Email configuration (MailHog)
$GLOBALS['email_config'] = [
    'smtp_host' => '127.0.0.1',
    'smtp_port' => 1025,
    'smtp_auth' => false,
    'smtp_secure' => false,
    'from_email' => 'noreply@openemr-dev.local',
    'from_name' => 'OpenEMR Development'
];

// Vietnamese locale settings
$GLOBALS['vietnamese_locale'] = [
    'language' => 'vi_VN',
    'charset' => 'UTF-8',
    'currency' => 'VND',
    'currency_symbol' => '₫',
    'date_format' => 'd/m/Y',
    'time_format' => 'H:i',
    'datetime_format' => 'd/m/Y H:i',
    'decimal_separator' => ',',
    'thousands_separator' => '.',
    'first_day_of_week' => 1, // Monday
    'timezone' => 'Asia/Ho_Chi_Minh'
];

// Development settings
$GLOBALS['development_config'] = [
    'debug' => true,
    'log_level' => 'debug',
    'error_reporting' => E_ALL,
    'display_errors' => true,
    'log_errors' => true,
    'error_log' => '/tmp/openemr_php_errors.log',
    'session_save_handler' => 'redis',
    'session_save_path' => 'tcp://127.0.0.1:6380?auth=openemr_redis&database=0'
];

// Network configuration helpers
$GLOBALS['docker_network'] = [
    'subnet' => '172.25.0.0/16',
    'services' => [
        'mariadb' => '172.25.0.10',
        'phpmyadmin' => '172.25.0.11', 
        'redis' => '172.25.0.12',
        'mailhog' => '172.25.0.13',
        'adminer' => '172.25.0.14'
    ]
];

// Service URLs for development
$GLOBALS['service_urls'] = [
    'phpmyadmin' => 'http://127.0.0.1:8083',
    'adminer' => 'http://127.0.0.1:8084',
    'mailhog' => 'http://127.0.0.1:8025',
    'database' => 'mysql://openemr:openemr@127.0.0.1:3306/openemr',
    'redis' => 'redis://openemr_redis@127.0.0.1:6380/0'
];

/**
 * Helper function to get database connection
 */
function getDockerDatabaseConnection() {
    $config = $GLOBALS['openemr_docker_env'];
    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
        $pdo = new PDO($dsn, $config['username'], $config['password'], $config['dsn_options']);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        throw new Exception("Unable to connect to Docker MariaDB: " . $e->getMessage());
    }
}

/**
 * Helper function to get Redis connection
 */
function getDockerRedisConnection() {
    $config = $GLOBALS['redis_config'];
    try {
        $redis = new Redis();
        $redis->connect($config['host'], $config['port'], $config['timeout']);
        if (!empty($config['password'])) {
            $redis->auth($config['password']);
        }
        $redis->select($config['database']);
        if ($config['persistent']) {
            $redis->setOption(Redis::OPT_READ_TIMEOUT, $config['read_timeout']);
        }
        return $redis;
    } catch (Exception $e) {
        error_log("Redis connection failed: " . $e->getMessage());
        throw new Exception("Unable to connect to Docker Redis: " . $e->getMessage());
    }
}

/**
 * Vietnamese-specific database helper
 */
function executeVietnameseQuery($query, $params = []) {
    $pdo = getDockerDatabaseConnection();
    
    // Set Vietnamese collation for this connection
    $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_vietnamese_ci");
    $pdo->exec("SET CHARACTER SET utf8mb4");
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    
    return $stmt;
}

// Initialize session with Redis if enabled
if ($GLOBALS['development_config']['session_save_handler'] === 'redis') {
    ini_set('session.save_handler', 'redis');
    ini_set('session.save_path', $GLOBALS['development_config']['session_save_path']);
}

// Set Vietnamese locale
if (function_exists('setlocale')) {
    setlocale(LC_ALL, $GLOBALS['vietnamese_locale']['language'] . '.' . $GLOBALS['vietnamese_locale']['charset']);
}

// Set timezone
if (function_exists('date_default_timezone_set')) {
    date_default_timezone_set($GLOBALS['vietnamese_locale']['timezone']);
}

// Development error reporting
if ($GLOBALS['development_config']['debug']) {
    error_reporting($GLOBALS['development_config']['error_reporting']);
    ini_set('display_errors', $GLOBALS['development_config']['display_errors']);
    ini_set('log_errors', $GLOBALS['development_config']['log_errors']);
    ini_set('error_log', $GLOBALS['development_config']['error_log']);
}