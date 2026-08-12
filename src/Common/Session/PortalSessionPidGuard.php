<?php

/**
 * Portal session-PID guard.
 *
 * The portal binds a request to a single patient via `bootstrap_pid`, set once
 * at portal bootstrap from the authenticated session. Downstream code that
 * consumes any pid-shaped value from the HTTP request must confirm that value
 * agrees with `bootstrap_pid` before using it, otherwise an authenticated
 * portal user can supply another patient's id and read/write cross-chart data.
 *
 * Two entry points:
 *   - assertMatchesSession()          — surgical, checks a single request-supplied value
 *   - assertRequestKeysMatchSession() — paranoid, scans request arrays for any
 *                                       key matching /^p(id|atientid)(_|$)/i,
 *                                       catching Phreez ORM variant bypasses
 *                                       (Pid_Equals, PatientId_In, etc.)
 *
 * Decision logic lives in the pure predicates (`isMatchingRequestPid`,
 * `scanRequestSourceForMismatch`) — testable without session, DB, or exit.
 * The assert-wrappers compose the predicate with `AccessDeniedHelper::deny()`.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Session;

use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Core\OEGlobalsBag;

final class PortalSessionPidGuard
{
    /**
     * Matches any HTTP request key beginning with "pid" or "patientid"
     * (case-insensitive), optionally followed by an underscore-suffix variant
     * (Phreez ORM criteria magic: Pid_Equals, PatientId_In, pid_greaterthan, ...).
     */
    private const PID_FAMILY_REGEX = '/^p(?:id|atientid)(?:_|$)/i';

    /**
     * Pure predicate: does the request-supplied value equal the session pid?
     * Non-scalar / non-numeric / empty values return false without denying.
     */
    public static function isMatchingRequestPid(mixed $requestPid, int $sessionPid): bool
    {
        if ($sessionPid <= 0) {
            return false;
        }
        if ($requestPid === null || $requestPid === '' || !is_scalar($requestPid) || !is_numeric($requestPid)) {
            return false;
        }
        return (int) $requestPid === $sessionPid;
    }

    /**
     * Pure predicate: scan a request-source array for the first pid-family key
     * that fails the session check. Returns the offending key or null if clean.
     *
     * Rejects both empty values (empty-overwrite attack) and mismatched values.
     *
     * @param array<int|string, mixed> $source
     */
    public static function scanRequestSourceForMismatch(array $source, int $sessionPid): ?string
    {
        foreach ($source as $key => $value) {
            if (!is_string($key) || preg_match(self::PID_FAMILY_REGEX, $key) !== 1) {
                continue;
            }
            if ($value === '' || $value === null) {
                return $key;
            }
            if (!self::isMatchingRequestPid($value, $sessionPid)) {
                return $key;
            }
        }
        return null;
    }

    /**
     * Assert a single request-supplied value matches the session pid.
     * Deny (403 + audit) on mismatch. Session pid defaults to bootstrap_pid.
     */
    public static function assertMatchesSession(mixed $requestPid, ?int $sessionPid = null): void
    {
        $sessionPid ??= self::getBootstrapPid();
        if (!self::isMatchingRequestPid($requestPid, $sessionPid)) {
            AccessDeniedHelper::deny(sprintf(
                'Portal PID guard: request pid does not match session %d',
                $sessionPid,
            ));
        }
    }

    /**
     * Assert every pid-family key across the supplied request sources agrees
     * with the session pid. Deny (403 + audit) on first offending key.
     * Session pid defaults to bootstrap_pid.
     *
     * @param array<int|string, mixed> ...$sources
     */
    public static function assertRequestKeysMatchSession(array ...$sources): void
    {
        $sessionPid = self::getBootstrapPid();
        foreach ($sources as $source) {
            $badKey = self::scanRequestSourceForMismatch($source, $sessionPid);
            if ($badKey !== null) {
                AccessDeniedHelper::deny(sprintf(
                    "Portal PID guard: request key '%s' fails session %d check",
                    $badKey,
                    $sessionPid,
                ));
            }
        }
    }

    private static function getBootstrapPid(): int
    {
        $bootstrapPid = OEGlobalsBag::getInstance()->get('bootstrap_pid');
        if (!is_scalar($bootstrapPid) || !is_numeric($bootstrapPid) || (int) $bootstrapPid <= 0) {
            AccessDeniedHelper::deny('Portal PID guard: no valid bootstrap session pid');
        }
        return (int) $bootstrapPid;
    }
}
