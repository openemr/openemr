<?php

/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 *
 * @Author: Oshri R <oshri.rozmarin@gmail.com>
 *
 */

use OpenEMR\Common\Crypto\CryptoGen;

// Set SQL mode and character set based on utf-8 settings
if (!$GLOBALS['disable_utf8_flag']) {
    $charset = !empty($GLOBALS["db_encoding"]) && ($GLOBALS["db_encoding"] == "utf8mb4") ? 'UTF8MB4' : 'UTF8';
    $tmp = "SET NAMES '$charset', sql_mode = ''";
} else {
    $tmp = "SET sql_mode = ''";
}
$tmp .= ", time_zone = '" . (new DateTime())->format("P") . "'";

// Determine PDO options based on connection pooling settings
if ((!empty($GLOBALS["enable_database_connection_pooling"]) || !empty($_SESSION["enable_database_connection_pooling"])) && empty($GLOBALS['connection_pooling_off'])) {
    $utf8 = [PDO::MYSQL_ATTR_INIT_COMMAND => $tmp, PDO::ATTR_PERSISTENT => true];
} else {
    $utf8 = [PDO::MYSQL_ATTR_INIT_COMMAND => $tmp];
}

// Configure SSL for MySQL if certificates are available
if (file_exists($GLOBALS['OE_SITE_DIR'] . "/documents/certificates/mysql-ca")) {
    $utf8[PDO::MYSQL_ATTR_SSL_CA] = $GLOBALS['OE_SITE_DIR'] . "/documents/certificates/mysql-ca";
    if (file_exists($GLOBALS['OE_SITE_DIR'] . "/documents/certificates/mysql-key") && file_exists($GLOBALS['OE_SITE_DIR'] . "/documents/certificates/mysql-cert")) {
        $utf8[PDO::MYSQL_ATTR_SSL_KEY] = $GLOBALS['OE_SITE_DIR'] . "/documents/certificates/mysql-key";
        $utf8[PDO::MYSQL_ATTR_SSL_CERT] = $GLOBALS['OE_SITE_DIR'] . "/documents/certificates/mysql-cert";
    }
}

// Set up default database adapter factory
$factories = [
    'Laminas\Db\Adapter\Adapter' => function ($containerInterface, $requestedName) {
        $adapterFactory = new Laminas\Db\Adapter\AdapterServiceFactory();
        $adapter = $adapterFactory($containerInterface, $requestedName);
        Laminas\Db\TableGateway\Feature\GlobalAdapterFeature::setStaticAdapter($adapter);
        return $adapter;
    }
];

// Set up additional adapters if multiple databases are supported
$adapters = [];
if (!empty($GLOBALS['allow_multiple_databases'])) {
    // Open PDO connection
    $dbh = new PDO("mysql:dbname={$GLOBALS['dbase']};host={$GLOBALS['host']};port={$GLOBALS['port']}", $GLOBALS['login'], $GLOBALS['pass']);
    $res = $dbh->prepare('SELECT * FROM multiple_db');
    if ($res->execute()) {
        foreach ($res->fetchAll() as $row) {
            $cryptoGen = new CryptoGen();
            $adapters[$row['namespace']] = [
                'driver' => 'Pdo',
                'dsn' => "mysql:dbname={$row['dbname']};host={$row['host']};port={$row['port']}",
                'driver_options' => $utf8,
                'username' => $row['username'],
                'password' => $cryptoGen->cryptCheckStandard($row['password']) ? $cryptoGen->decryptStandard($row['password']) : my_decrypt($row['password']),
            ];

            $factories[$row['namespace']] = function ($serviceManager) use ($row) {
                $adapterAbstractServiceFactory = new Laminas\Db\Adapter\AdapterAbstractServiceFactory();
                return $adapterAbstractServiceFactory->createServiceWithName($serviceManager, '', $row['namespace']);
            };
        }
    }
    $dbh = null; // Close PDO connection
}

return [
    'db' => [
        'driver'         => 'Pdo',
        'dsn'            => "mysql:dbname={$GLOBALS['dbase']};host={$GLOBALS['host']};port={$GLOBALS['port']}",
        'username'       => $GLOBALS['login'],
        'password'       => $GLOBALS['pass'],
        'driver_options' => $utf8,
        'adapters'       => $adapters
    ],
    'service_manager' => [
        'factories' => $factories
    ]
];

/**
 * DEPRECATED; just keeping this for backward compatibility.
 *
 * Decrypts the string
 * @param $value
 * @return bool|string
 */

function my_decrypt($data) {
    $encryption_key = base64_decode($GLOBALS['safe_key_database']);
    list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
    return openssl_decrypt($encrypted_data, 'aes-256-cbc', $encryption_key, 0, $iv);
}
