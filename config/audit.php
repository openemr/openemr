<?php

/**
 * Auditing-related services and configuration
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

use Firehed\Container\TypedContainerInterface as TC;
use OpenEMR\Common\Database\{
    ConnectionManager,
    ConnectionType,
};
use OpenEMR\Common\Logging\{
    Audit,
    AuditConfig,
    BreakglassChecker,
    BreakglassCheckerInterface,
    EventAuditLogger,
};
use Psr\Clock\ClockInterface;

use function Firehed\Container\env;

return [
    BreakglassCheckerInterface::class => BreakglassChecker::class,
    // See notes in BreakglassChecker's constructor: it must use the
    // non-audited connection in order to avoid an infinite loop w/ SQL logging
    BreakglassChecker::class => fn (TC $c) => new BreakglassChecker(
        $c->get(ConnectionManager::class)->get(ConnectionType::NonAudited),
    ),

    EventAuditLogger::class,

    AuditConfig::class => fn (TC $c) => new AuditConfig(
        enabled: $c->getBool('AUDIT_ENABLE'),
        forceBreakglass: $c->getBool('AUDIT_BREAKGLASS_ACTIVITY'),
        queryEvents: $c->getBool('AUDIT_QUERIES'),
        httpRequestEvents: $c->getBool('AUDIT_HTTP_REQUESTS'),
        // Remap the input string to the set of enabled events
        eventTypeFlags: array_fill_keys(explode(',', $c->getString('AUDIT_EVENT_TYPES')), true),
    ),
    Audit\SinkInterface::class => Audit\MultiSink::class,
    Audit\MultiSink::class => function (TC $c) {
        $sinks = [];
        $auditConn = $c->get(ConnectionManager::class)
            ->get(ConnectionType::NonAudited);
        // Future: make this configurable
        $sinks[] = new Audit\LogTablesSink(conn: $auditConn);
        if ($c->getBool('ATNA_ENABLED')) {
            $sinks[] = $c->get(Audit\AtnaSink::class);
        }
        return new Audit\MultiSink($sinks);
    },


    // ATNA logging config
    Audit\Atna\TcpWriter::class => fn (TC $c) => new Audit\Atna\TcpWriter(
        host: $c->getString('ATNA_AUDIT_HOST'),
        port: $c->getInt('ATNA_AUDIT_PORT'),
        localCert: $c->getString('ATNA_AUDIT_LOCALCERT'),
        caCert: $c->getString('ATNA_AUDIT_CACERT'),
    ),
    Audit\AtnaSink::class => fn (TC $c) => new Audit\AtnaSink(
        clock: $c->get(ClockInterface::class),
        writer: $c->get(Audit\Atna\TcpWriter::class),
        host: $c->getString('ATNA_AUDIT_HOST'),
        serverName: '', // SERVER[SERVER_NAME]
        serverAddress: '', // SERVER[SERVER_ADDRESS]
    ),
    'AUDIT_ENABLE' => env('AUDIT_EVENTS', 'true')->asBool(),
    'AUDIT_QUERIES' => env('AUDIT_QUERIES', 'true')->asBool(),
    'AUDIT_HTTP_REQUESTS' => env('AUDIT_HTTP_REQUESTS', 'true')->asBool(),
    'AUDIT_EVENT_TYPES' => env('AUDIT_EVENT_TYPES', 'patient-record,scheduling,order,lab-order,lab-results,security-administrator,other'),
    'AUDIT_BREAKGLASS_ACTIVITY' => env('AUDIT_BREAKGLASS_ACTIVITY', 'true')->asBool(),


    'ATNA_ENABLED' => env('ATNA_ENABLED', 'false')->asBool(),
    'ATNA_AUDIT_HOST' => env('ATNA_AUDIT_HOST'),
    'ATNA_AUDIT_PORT' => env('ATNA_AUDIT_PORT', '6514')->asInt(),
    'ATNA_AUDIT_LOCALCERT' => env('ATNA_AUDIT_LOCALCERT', ''),
    'ATNA_AUDIT_CACERT' => env('ATNA_AUDIT_CACERT', ''),
];
