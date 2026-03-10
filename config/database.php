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
    Types\Type,
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
use OpenEMR\Entities\EventSubscriber\TimestampSubscriber;
use OpenEMR\BC\DatabaseConnectionOptions;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\{
    Doctrine\UuidBinaryType,
    UuidInterface,
};

return [
    // DBAL
    Connection::class => function (TC $c) {
        $opts = $c->get(DatabaseConnectionOptions::class);
        return DriverManager::getConnection($opts->toDbalParams());
    },

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
    Configuration::class => function (TC $c): Configuration {
        $paths = [
            'src/Entities',
            // TODO: integrate module-vended entity paths.
        ];
        $isDevMode = $c->get('OPENEMR__ENVIRONMENT') === 'dev';
        $config = ORMSetup::createAttributeMetadataConfiguration($paths, $isDevMode);
        $config->setNamingStrategy($c->get(NamingStrategy::class));
        $config->setTypedFieldMapper($c->get(TypedFieldMapper::class));

        return $config;
    },
    EntityManagerInterface::class => function (TC $c): EntityManagerInterface {
        $em = new EntityManager(
            conn: $c->get(Connection::class),
            config: $c->get(Configuration::class),
        );
        // In theory, this EM could be fully autowired and have EventManager in
        // the DI defs with this setup, but whatever.
        $em->getEventManager()->addEventListener(
            [Events::prePersist, Events::preUpdate],
            $c->get(TimestampSubscriber::class),
        );
        return $em;
    },
    NamingStrategy::class => fn () => new UnderscoreNamingStrategy(case: CASE_LOWER),
    TypedFieldMapper::class => function (): TypedFieldMapper {
        Type::addType(UuidBinaryType::NAME, UuidBinaryType::class);
        return new DefaultTypedFieldMapper([
            UuidInterface::class => UuidBinaryType::NAME,
        ]);
    },

];
