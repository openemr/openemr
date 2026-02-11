<?php

/**
 * Configuration for Doctrine Migrations
 */

use Doctrine\DBAL\DriverManager;
use Doctrine\Migrations\Configuration\Migration\ConfigurationArray;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use OpenEMR\BC\Database;

$loader = new PhpFile('db/migration-config.php');

$site = 'default'; // fixme: env or something


$fml = function(string $site) {
    require __DIR__ . "/../sites/$site/sqlconf.php";
    assert(isset($sqlconf) && is_array($sqlconf));
    $params = Database::translateLegacySqlconf($sqlconf, $site);
    return DriverManager::getConnection($params);
    var_dump(get_defined_vars());
    exit;
};
$conn = $fml($site);

// $conn = Database::instance()->getDbalConnection();
$connL = new ExistingConnection(
    connection: $conn,
);
$df = DependencyFactory::fromConnection(
    configurationLoader: $loader,
    connectionLoader: $connL,
);

var_dump("READ CLI CONFIG");
return $df;
