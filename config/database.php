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
    Configuration,
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
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Database\QueryAuditing\{
    AuditSettingsInterface,
    BreakglassChecker,
    BreakglassCheckerInterface,
    CategoryResolver,
    GlobalsAuditSettings,
    Middleware\AuditingMiddleware,
    NullQueryContext,
    QueryAuditor,
    QueryAuditorInterface,
    QueryContext,
    QueryContextInterface,
    TableEventMap,
};
use Psr\Log\LoggerInterface;

return [
    // DBAL with auditing middleware
    Connection::class => function (TC $c) {
        $opts = $c->get(DatabaseConnectionOptions::class);
        $config = new Configuration();
        $config->setMiddlewares([
            $c->get(AuditingMiddleware::class),
        ]);
        return DriverManager::getConnection($opts->toDbalParams(), $config);
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

    // Query Auditing
    AuditingMiddleware::class => fn (TC $c) => new AuditingMiddleware(
        $c->get(QueryAuditorInterface::class),
    ),
    QueryAuditorInterface::class => fn (TC $c) => new QueryAuditor(
        $c->get(AuditSettingsInterface::class),
        $c->get(BreakglassCheckerInterface::class),
        $c->get(QueryContextInterface::class),
        new TableEventMap(),
        new CategoryResolver(),
        EventAuditLogger::getInstance(),
    ),
    AuditSettingsInterface::class => fn () => new GlobalsAuditSettings(
        OEGlobalsBag::getInstance(),
    ),
    BreakglassCheckerInterface::class => fn () => new BreakglassChecker(),
    QueryContextInterface::class => function () {
        if (php_sapi_name() === 'cli') {
            return new NullQueryContext();
        }
        return new QueryContext(
            SessionWrapperFactory::getInstance()->getWrapper(),
        );
    },

];
