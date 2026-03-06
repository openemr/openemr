<?php

/**
 * Configuration for Doctrine Migrations
 *
 * Due to the way the bundled `vendor/bin/doctrine-migrations` script works,
 * this file MUST live at this exact path or in the repository root. A future
 * integration w/ CLI tooling may end up in a different location.
 *
 * @package   openemr
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 * @link https://www.doctrine-project.org/projects/doctrine-migrations/en/3.9/reference/configuration.html#advanced
 * @link https://www.doctrine-project.org/projects/doctrine-migrations/en/3.9/reference/custom-integration.html#custom-integration
 */

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\DependencyFactory;
use OpenEMR\BC\DatabaseConnectionOptions;
use Firehed\Container\TypedContainerInterface as TC;

// This leaks in from parent `cli` scope... needs cleanup.
assert(isset($container) && $container instanceof TC);

$configLoader = new PhpFile('db/migration-config.php');

$connLoader = new ExistingConnection(
    connection: $container->get(Connection::class),
);

return DependencyFactory::fromConnection(
    configurationLoader: $configLoader,
    connectionLoader: $connLoader,
);
