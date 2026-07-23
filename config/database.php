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

use Doctrine\Common\EventManager;
use Doctrine\DBAL\{
    Configuration as DbalConfiguration,
    Connection,
    DriverManager,
};
use Firehed\DbalLogger\{
    ChainLogger,
    Middleware as LoggingMiddleware,
};
use OpenEMR\Common\Database\Middleware\QueryAuditor;
use Doctrine\Migrations\Configuration\{
    Connection\ConnectionLoader,
    Connection\ExistingConnection,
    Migration\ConfigurationArray,
    Migration\ConfigurationLoader,
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
use OpenEMR\Core\Database\Types\CustomTypes;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

return [
    // Connection Manager - manages named connections with different middleware
    ConnectionManager::class => function (TC $c) {
        CustomTypes::register();
        $manager = new ConnectionManager();
        $opts = $c->get(DatabaseConnectionOptions::class);

        // Main connection: with audit logging middleware
        $manager->register(ConnectionType::Main, function () use ($c, $opts) {
            $config = new DbalConfiguration();
            $config->setMiddlewares([
                new LoggingMiddleware(new ChainLogger([
                    $c->get(QueryAuditor::class),
                ])),
            ]);
            return DriverManager::getConnection($opts->toDbalParams(), $config);
        });

        // Audit connection: no middleware, used by EventAuditLogger and some
        // application bootstrapping
        $manager->register(ConnectionType::NonAudited, fn () =>
            DriverManager::getConnection($opts->toDbalParams()));

        return $manager;
    },

    // DBAL - delegates to ConnectionManager
    Connection::class => fn (TC $c) =>
        $c->get(ConnectionManager::class)->get(ConnectionType::Main),

    QueryAuditor::class,

    // DB connection config
    DatabaseConnectionOptions::class => function (TC $c) {
        $site = $c->getString('OPENEMR_SITE');
        return DatabaseConnectionOptions::forSite("sites/$site");
    },

    // Doctrine Migrations
    ConfigurationLoader::class => fn () => new ConfigurationArray([
        'custom_template' => 'db/migration-template.php.tpl',
        'migrations_paths' => [
            // A future version of this will integrate w/ the modules system and
            // pull in any vended migrations from installed/active modules.
            'OpenEMR\\Core\\Migrations' => 'db/Migrations',
        ],
        'table_storage' => [
            'table_name' => 'migrations',
            'execution_time_column_name' => 'execution_duration_ms',
        ],
    ]),
    ConnectionLoader::class => fn (TC $c) => new ExistingConnection(
        $c->get(ConnectionManager::class)
            ->get(ConnectionType::NonAudited)
    ),
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

    // Note: Doctrine EntityManager types EventManager not
    // EventManagerInterface, so we use it as the key so it gets wired.
    EventManager::class => function (TC $c): EventManager {
        $manager = new EventManager();
        // Future: add ORM lifecycle hooks in here
        //
        // Hookable events are defined in:
        // - Doctrine\ORM\Events
        // - Doctrine\ORM\Tools\ToolEvents
        //
        // Doctrine\Migrations\Events are NOT available, since Migrations is
        // (yet again) not connected to EntityManager. See #12810. tl;dr:
        // migrations needs the non-audited connection (or it can't create
        // tables on a fresh install, it tries to write logs before the table
        // exists), and that takes priority over event hooks.
        //
        // Listeners are convention-based: they must have a public method of the
        // event's name which will be called upon event dispatching. E.g. an
        // event named `fooEvent` means the listener must have `public function
        // fooEvent(): void`. Some events emit additional data as an argument,
        // see their definitions for more detail.
        //
        // This should NOT be used for application events; stick with the
        // Symfony EventDispatcher in the kernel.
        return $manager;
    },
];
