<?php

/**
 * Database and Doctrine configuration
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 Eric Stern
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

use Doctrine\DBAL\{
    Connection,
    DriverManager,
};
use Doctrine\Migrations\Configuration\{
    Connection\ConnectionLoader,
    Connection\ExistingConnection,
    Migration\ConfigurationLoader,
    Migration\PhpFile,
};
use Doctrine\Migrations\DependencyFactory;
use Firehed\Container\TypedContainerInterface as TC;
use OpenEMR\BC\DatabaseConnectionOptions;
use OpenEMR\Common\Database\ConnectionManager;
use OpenEMR\Common\Database\ConnectionType;
use Psr\Log\LoggerInterface;

return [
    // Connection Manager - manages named connections with different middleware
    ConnectionManager::class => function (TC $c) {
        $manager = new ConnectionManager();
        $opts = $c->get(DatabaseConnectionOptions::class);

        // Main connection: middleware will be added here
        $manager->register(ConnectionType::Main, fn () =>
            DriverManager::getConnection($opts->toDbalParams()));

        // Audit connection: no middleware, used by EventAuditLogger and some
        // application bootstrapping
        $manager->register(ConnectionType::NonAudited, fn () =>
            DriverManager::getConnection($opts->toDbalParams()));

        return $manager;
    },

    // DBAL - delegates to ConnectionManager
    Connection::class => fn (TC $c) =>
        $c->get(ConnectionManager::class)->get(ConnectionType::Main),

    // DB connection config
    DatabaseConnectionOptions::class => function (TC $c) {
        $site = $c->getString('OPENEMR_SITE');
        return DatabaseConnectionOptions::forSite("sites/$site");
    },

    // Doctrine Migrations
    ConfigurationLoader::class => fn () => new PhpFile('db/migration-config.php'),
    ConnectionLoader::class => fn (TC $c) => new ExistingConnection($c->get(Connection::class)),
    DependencyFactory::class => fn (TC $c) => DependencyFactory::fromConnection(
        $c->get(ConfigurationLoader::class),
        $c->get(ConnectionLoader::class),
        $c->get(LoggerInterface::class),
    ),

];
