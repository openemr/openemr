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
use Doctrine\ORM\{
    Configuration,
    EntityManager,
    EntityManagerInterface,
    Events,
    Mapping\DefaultTypedFieldMapper,
    Mapping\NamingStrategy,
    Mapping\TypedFieldMapper,
    Mapping\UnderscoreNamingStrategy,
    ORMSetup,
};
use Firehed\Container\TypedContainerInterface as TC;
use OpenEMR\BC\DatabaseConnectionOptions;
use OpenEMR\Common\Database\ConnectionManager;
use OpenEMR\Common\Database\ConnectionType;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

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

    // ORM
    Configuration::class => function (TC $c) {
        $paths = [
            'src/Entities',
            // Future: load additional paths from modules?
            // this may need a ClassLoader instead.
        ];
        // FIXME: before this gets integrated, isDevMode needs to be derived
        // and an appropriate cache adapter provided (probably apcu for web and
        // still array for cli)
        $isDevMode = true;
        $config = ORMSetup::createAttributeMetadataConfig(
            paths: $paths,
            isDevMode: $isDevMode,
            cache: new ArrayAdapter(),
        );

        // Automatically translates classes and properties from UpperCamelCase
        // in PHP to snake_case in the database.
        $config->setNamingStrategy(new UnderscoreNamingStrategy(case: CASE_LOWER));
        // Future: TypedFieldMapper for custom types.

        // These are only used in PHP <= 8.3 where native lazy objects aren't
        // supported or enabled.
        $config->enableNativeLazyObjects(version_compare(PHP_VERSION, '8.4.0', '>='));
        $config->setProxyDir(sys_get_temp_dir());
        $config->setProxyNamespace('DoctrineProxies');
        $config->setAutoGenerateProxyClasses($isDevMode);
        return $config;
    },

    EntityManager::class,
    EntityManagerInterface::class => EntityManager::class,
];
