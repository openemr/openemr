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

// If to use utf-8 or not in my sql query
if (!$GLOBALS['disable_utf8_flag']) {
    if (!empty($GLOBALS["db_encoding"]) && ($GLOBALS["db_encoding"] == "utf8mb4")) {
        $tmp = "SET NAMES 'UTF8MB4', sql_mode = ''";
    } else {
        $tmp = "SET NAMES 'UTF8', sql_mode = ''";
    }
} else {
    $tmp = "SET sql_mode = ''";
}
$tmp .= ", time_zone = '" . (new DateTime())->format("P") . "'";

if ((!empty($GLOBALS["enable_database_connection_pooling"]) || !empty($_SESSION["enable_database_connection_pooling"])) && empty($GLOBALS['connection_pooling_off'])) {
    $utf8 = [PDO::MYSQL_ATTR_INIT_COMMAND => $tmp, PDO::ATTR_PERSISTENT => true];
} else {
    $utf8 = [PDO::MYSQL_ATTR_INIT_COMMAND => $tmp];
}

// Set mysql to use ssl, if applicable.
// Can support basic encryption by including just the mysql-ca pem (this is mandatory for ssl)
// Can also support client based certificate if also include mysql-cert and mysql-key (this is optional for ssl)
if (file_exists($GLOBALS['OE_SITE_DIR'] . "/documents/certificates/mysql-ca")) {
    $utf8[PDO::MYSQL_ATTR_SSL_CA ] = $GLOBALS['OE_SITE_DIR'] . "/documents/certificates/mysql-ca";
    if (
        file_exists($GLOBALS['OE_SITE_DIR'] . "/documents/certificates/mysql-key") &&
        file_exists($GLOBALS['OE_SITE_DIR'] . "/documents/certificates/mysql-cert")
    ) {
        $utf8[PDO::MYSQL_ATTR_SSL_KEY] = $GLOBALS['OE_SITE_DIR'] . "/documents/certificates/mysql-key";
        $utf8[PDO::MYSQL_ATTR_SSL_CERT] = $GLOBALS['OE_SITE_DIR'] . "/documents/certificates/mysql-cert";
    }
}

// Sets default factory using the default database
$factories = array(
    'Laminas\Db\Adapter\Adapter' => function ($containerInterface, $requestedName) {
        $adapterFactory = new Laminas\Db\Adapter\AdapterServiceFactory();
        $adapter = $adapterFactory($containerInterface, $requestedName);
        \Laminas\Db\TableGateway\Feature\GlobalAdapterFeature::setStaticAdapter($adapter);
        return $adapter;
    }
);

// This settings can be change in the global settings under security tab
$adapters = array();
if (!empty($GLOBALS['allow_multiple_databases'])) {
    // Open pdo connection
    $dbh = new PDO('mysql:dbname=' . $GLOBALS['dbase'] . ';host=' . $GLOBALS['host'], $GLOBALS['login'], $GLOBALS['pass']);
    $res = $dbh->prepare('SELECT * FROM multiple_db');
    if ($res->execute()) {
        foreach ($res->fetchAll() as $row) {
            // Create new adapters using data from database
            $cryptoGen = new CryptoGen();
            $adapters[$row['namespace']] = array(
                'driver' => 'Pdo',
                'dsn' => 'mysql:dbname=' . $row['dbname'] . ';host=' . $row['host'] . '',
                'driver_options' => $utf8,
                'port' => $row['port'],
                'username' => $row['username'],
                'password' => ($cryptoGen->cryptCheckStandard($row['password'])) ? $cryptoGen->decryptStandard($row['password']) : my_decrypt($row['password']),
            );

            // Create new factories using data from custom database
            $factories[$row['namespace']] = function ($serviceManager) use ($row) {
                $adapterAbstractServiceFactory = new Laminas\Db\Adapter\AdapterAbstractServiceFactory();
                $adapter = $adapterAbstractServiceFactory->createServiceWithName($serviceManager, '', $row['namespace']);
                return $adapter;
            };
        }
    }

    $dbh = null; // Close pdo connection
}

return array(
    'db' => array(
        'driver'         => 'Pdo',
        'dsn'            => 'mysql:dbname=' . ($GLOBALS['dbase'] ?? '') . ';host=' . ($GLOBALS['host'] ?? ''),
        'username'       => $GLOBALS['login'] ?? '',
        'password'       => $GLOBALS['pass'] ?? '',
        'port'           => $GLOBALS['port'] ?? '',
        'driver_options' => $utf8,
        'adapters' => $adapters

    ),
    'service_manager' => array(
        'factories' => $factories
    )
);



/**
 * DEPRECATED; just keeping this for backward compatibility.
 *
 * Decrypts the string
 * @param $value
 * @return bool|string
 *
 * DEPRECATED; just keeping this for backward compatibility.
 */

function my_decrypt($data)
{
    // Remove the base64 encoding from our key
    $encryption_key = base64_decode($GLOBALS['safe_key_database']);
    // To decrypt, split the encrypted data from our IV - our unique separator used was "::"
    list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
    return openssl_decrypt($encrypted_data, 'aes-256-cbc', $encryption_key, 0, $iv);
}
