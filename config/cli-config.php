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
 *
 * @phpstan-import-type SqlConf from Database
 * (note: this has no effect in the current version of PHPStan; I want to
 * refine this format furtuer which will eliminate the problem. For now it's
 * baselined)
 */

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\Migrations\Configuration\Migration\ConfigurationArray;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use OpenEMR\BC\DatabaseConnectionOptions;

$loader = new PhpFile('db/migration-config.php');

$site = 'default'; // fixme: env or something


$getConnectionFromSqlconf = function(string $site): Connection {
    $siteDir = __DIR__ . "/../sites/$site";
    $connOpts = DatabaseConnectionOptions::forSite($siteDir);
    return DriverManager::getConnection($connOpts->toDbalParams());
};

$conn = $getConnectionFromSqlconf($site);
$connLoader = new ExistingConnection(
    connection: $conn,
);

$df = DependencyFactory::fromConnection(
    configurationLoader: $loader,
    connectionLoader: $connLoader,
);

return $df;
