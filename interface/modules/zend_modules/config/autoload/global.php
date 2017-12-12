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
 */

// If to use utf-8 or not in my sql query
$tmp = $GLOBALS['disable_utf8_flag'] ? "SET sql_mode = ''" : "SET NAMES 'UTF8', sql_mode = ''";
$utf8 = array(PDO::MYSQL_ATTR_INIT_COMMAND => $tmp);
// Set mysql to use ssl, if applicable.
// Can support basic encryption by including just the mysql-ca pem (this is mandatory for ssl)
// Can also support client based certificate if also include mysql-cert and mysql-key (this is optional for ssl)
if (file_exists($GLOBALS['OE_SITE_DIR'] . "/documents/certificates/mysql-ca")) {
    $utf8[PDO::MYSQL_ATTR_SSL_CA ] = $GLOBALS['OE_SITE_DIR'] . "/documents/certificates/mysql-ca";
    $utf8[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    if (file_exists($GLOBALS['OE_SITE_DIR'] . "/documents/certificates/mysql-key") &&
        file_exists($GLOBALS['OE_SITE_DIR'] . "/documents/certificates/mysql-cert")) {
        $utf8[PDO::MYSQL_ATTR_SSL_KEY] = $GLOBALS['OE_SITE_DIR'] . "/documents/certificates/mysql-key";
        $utf8[PDO::MYSQL_ATTR_SSL_CERT] = $GLOBALS['OE_SITE_DIR'] . "/documents/certificates/mysql-cert";
    }
}

  $db = array(
        'driver'         => 'Pdo',
        'dsn'            => 'mysql:dbname='.$GLOBALS['dbase'].';host='.$GLOBALS['host'],
        'username'       => $GLOBALS['login'],
        'password'       => $GLOBALS['pass'],
        'port'           => $GLOBALS['port'],
        'driver_options' => $utf8
        );

return array(
    'db' => $db,
    'service_manager' => array(
        'factories' => array(
            'Zend\Db\Adapter\Adapter' => function ($serviceManager) {
				$adapterFactory = new Zend\Db\Adapter\AdapterServiceFactory();
				$adapter = $adapterFactory->createService($serviceManager);
               \Zend\Db\TableGateway\Feature\GlobalAdapterFeature::setStaticAdapter($adapter);
               return $adapter;
         	}
        ),
    ),
);
