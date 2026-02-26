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

use OpenEMR\BC\DatabaseConnectionFactory;
use OpenEMR\Common\Database\DbUtils;

$tmp = "SET NAMES 'UTF8MB4', sql_mode = '', time_zone = '" . (new DateTime())->format("P") . "'";

$utf8 = [PDO::MYSQL_ATTR_INIT_COMMAND => $tmp];
if (DatabaseConnectionFactory::detectConnectionPersistenceFromGlobalState()) {
    $utf8[PDO::ATTR_PERSISTENT] = true;
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
$factories = [
    \Laminas\Db\Adapter\Adapter::class => function ($containerInterface, $requestedName) {
        $adapterFactory = new Laminas\Db\Adapter\AdapterServiceFactory();
        $adapter = $adapterFactory($containerInterface, $requestedName);
        \Laminas\Db\TableGateway\Feature\GlobalAdapterFeature::setStaticAdapter($adapter);
        return $adapter;
    }
];

// sites/<site_id>/sqlconf.php stores the database connection settings into a global sqlconf variable
// we will use that instead of the individual globals set previously.
$sqlConf = $GLOBALS['sqlconf'] ?? ['dbase' => '', 'host' => '', 'login' => '', 'pass' => '', 'port' => ''];

return [
    'db' => [
        'driver'         => 'Pdo',
        'dsn'            => DbUtils::buildMysqlDsn($sqlConf['dbase'] ?? '', $sqlConf['host'] ?? '', $sqlConf['port'] ?? ''),
        'username'       => $sqlConf['login'] ?? '',
        'password'       => $sqlConf['pass'] ?? '',
        'driver_options' => $utf8,
        'adapters'       => [],
    ],
    'service_manager' => [
        'factories' => $factories
    ]
];
