<?php

/**
 * Portal session-PID guard.
 *
 * The portal binds a request to a single patient; downstream code that
 * consumes any pid-shaped value from the HTTP request (or trusts a loaded
 * row's owner) must confirm the value agrees with the session pid.
 *
 * Entry points:
 *   - requireBootstrapPid()           — read + validate bootstrap_pid; caller
 *                                       uses the returned int for scoping.
 *   - assertMatchesSession()          — surgical: single request-supplied value
 *                                       must equal the caller-supplied session pid.
 *   - assertOwnedBySession()          — surgical: a loaded row's owner pid must
 *                                       equal the caller-supplied session pid.
 *   - assertRequestKeysMatchSession() — paranoid: scan request arrays for any
 *                                       key matching /^p(id|atientid)(_|$)/i
 *                                       (catches Phreez ORM variant bypasses
 *                                       like Pid_Equals). Uses bootstrap_pid.
 *
 * Decision logic lives in the pure predicates (`isMatchingPid`,
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
     * Pure predicate: do the two pid values equal, once both parse as
     * strict positive integers (rejects floats, decimal strings, and
     * scientific notation — no truncation-based coercion)?
     */
    public static function isMatchingPid(mixed $subjectPid, mixed $sessionPid): bool
    {
        $subject = self::parseStrictPositiveInt($subjectPid);
        $session = self::parseStrictPositiveInt($sessionPid);
        return $subject !== null && $session !== null && $subject === $session;
    }

    /**
     * Parse a value as a strict positive integer. Accepts int or exact
     * integer string; rejects floats, decimal strings ("5.5"), scientific
     * notation ("1e3"), booleans, null, arrays, and non-positive values.
     */
    private static function parseStrictPositiveInt(mixed $value): ?int
    {
        if (!is_int($value) && !is_string($value)) {
            return null;
        }
        $parsed = filter_var($value, FILTER_VALIDATE_INT);
        if ($parsed === false || $parsed <= 0) {
            return null;
        }
        return $parsed;
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
            if (!self::isMatchingPid($value, $sessionPid)) {
                return $key;
            }
        }
        return null;
    }

    /**
     * Read + validate the portal bootstrap pid. Denies on missing / invalid.
     */
    public static function requireBootstrapPid(): int
    {
        $parsed = self::parseStrictPositiveInt(OEGlobalsBag::getInstance()->get('bootstrap_pid'));
        if ($parsed === null) {
            AccessDeniedHelper::deny('Portal PID guard: no valid bootstrap session pid');
        }
        return $parsed;
    }

    /**
     * Assert a single request-supplied value matches the caller-supplied
     * session pid. Deny (403 + audit) on mismatch or invalid session pid.
     */
    public static function assertMatchesSession(mixed $requestPid, mixed $sessionPid): void
    {
        if (!self::isMatchingPid($requestPid, $sessionPid)) {
            AccessDeniedHelper::deny('Portal PID guard: request pid does not match session');
        }
    }

    /**
     * Assert a loaded record's owner pid matches the caller-supplied session pid.
     * Deny (403 + audit) on mismatch or invalid session pid.
     */
    public static function assertOwnedBySession(mixed $ownerPid, mixed $sessionPid): void
    {
        if (!self::isMatchingPid($ownerPid, $sessionPid)) {
            AccessDeniedHelper::deny('Portal PID guard: row not owned by session');
        }
    }

    /**
     * Assert every pid-family key across the supplied request sources agrees
     * with the bootstrap session pid. Deny (403 + audit) on first offending key.
     *
     * @param array<int|string, mixed> ...$sources
     */
    public static function assertRequestKeysMatchSession(array ...$sources): void
    {
        $sessionPid = self::requireBootstrapPid();
        foreach ($sources as $source) {
            $badKey = self::scanRequestSourceForMismatch($source, $sessionPid);
            if ($badKey !== null) {
                AccessDeniedHelper::deny('Portal PID guard: request key fails session pid check');
            }
        }
    }
}
