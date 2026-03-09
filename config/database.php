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
use Psr\Log\LoggerInterface;

return [
    // DBAL
    Connection::class => function (TC $c) {
        $opts = $c->get(DatabaseConnectionOptions::class);
        return DriverManager::getConnection($opts->toDbalParams());
    },

    // DB connection config
    DatabaseConnectionOptions::class => function (TC $c) {
        $site = $c->get('OPENEMR_SITE');
        assert(is_string($site));
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
