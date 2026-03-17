<?php

/**
 * Generalized service configuration
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 Eric Stern
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

use Firehed\Container\TypedContainerInterface as TC;
use Lcobucci\Clock\SystemClock;
use Monolog\Level;
use OpenEMR\Common\Http\Psr17Factory;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Logging\BreakglassChecker;
use OpenEMR\Common\Logging\Audit;
use OpenEMR\Common\Database\{
    ConnectionManager,
    ConnectionType,
};

return [
    Level::class => fn (TC $c) => Level::fromName($c->get('LOG_LEVEL')),

    Psr17Factory::class,

    SystemClock::class => fn () => SystemClock::fromSystemTimezone(),

    SystemLogger::class => fn (TC $c) => new SystemLogger($c->get(Level::class)),

    EventAuditLogger::class => function (TC $c) {
        // Minor: the things that take connections could instead take
        // connection managers and then be fully autowired.

        $auditConn = $c->get(ConnectionManager::class)->get(ConnectionType::Audit);
        $sinks = [
            new Audit\LogTablesSink($auditConn),
        ];
        // ATNA.... tbd

        return new EventAuditLogger(
            sinks: $sinks,
            // cryptoGen: CG
            // shouldEncrypt: config,
            // session: oh my,
            // config: build above from config,
            breakglassChecker: new BreakglassChecker($auditConn),
        );
    },
];
