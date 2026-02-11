<?php

/**
 * Configuration for Doctrine Migrations
 *
 * Due to the way the bundled `vendor/bin/doctrine-migrations` script works,
 * this file MUST live at this exact path or in the repository root. A future
 * integration w/ CLI tooling may end up in a different location.
 *
 * @link https://www.doctrine-project.org/projects/doctrine-migrations/en/3.9/reference/configuration.html#advanced
 * @link https://www.doctrine-project.org/projects/doctrine-migrations/en/3.9/reference/custom-integration.html#custom-integration
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
