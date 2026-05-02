<?php

/**
 * Best-effort polling client for ClaimRev's module-version-check endpoint.
 *
 * The endpoint is anonymous (no client ID, no auth) so it works on fresh
 * installs and on installs with bad credentials. It's called from the
 * actual work paths — claim send + eligibility check — rather than a cron
 * job, on a 24h throttle. The cached result is written to
 * mod_claimrev_version_check (one row, id=1) so the dashboard banner can
 * read it without going to the network.
 *
 * Failure mode: the public method returns null on every error path
 * (network unreachable, non-200 response, malformed JSON, missing fields).
 * Callers see null and proceed normally — there is intentionally no path
 * by which a ClaimRev outage can disable a working install. Only a
 * successful response with `disabled: true` will cause the cached record
 * to flip to disabled.
 *
 * Install identity: a UUIDv4 is generated on first check and persisted.
 * Sent alongside name+version so ClaimRev can count installs without
 * knowing who they are. There is no client ID, no patient data, no PHI
 * in any direction.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2026 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ClaimRevConnector;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Modules\ClaimRevConnector\Dto\ModuleVersionCheckResult;

class ModuleVersionCheckService
{
    /** Module name we identify as in the version-check API. */
    public const MODULE_NAME = 'openemr_client_connect';

    /** Public version-check endpoint — no auth, hardcoded to production. */
    private const ENDPOINT = 'https://api.claimrev.com/api/Module/v1/CheckVersion';

    /** Throttle window: don't hit the API more than once per this many seconds. */
    private const THROTTLE_SECONDS = 86400; // 24 hours

    /** Network call cap so a hanging request doesn't block claim send forever. */
    private const TIMEOUT_SECONDS = 5;

    /**
     * Attempt a version check. Returns the result on success (whether
     * fresh from the API or cached from a recent successful check),
     * null on any failure or before the first successful check.
     *
     * @param bool $force Bypass the 24h throttle. Reserved for an admin
     *                    "refresh now" button on the dashboard.
     */
    public static function check(bool $force = false): ?ModuleVersionCheckResult
    {
        // If a recent successful check exists, return its cached result.
        // The throttle protects ClaimRev's endpoint from N-installs * M-actions
        // per minute; the cached value is what the dashboard shows.
        $cached = self::loadCachedRow();
        if (!$force && $cached !== null && self::cacheIsFresh($cached)) {
            return self::resultFromRow($cached);
        }

        // Either no cache, stale cache, or force-refresh requested. Hit the API.
        $installId = self::getOrGenerateInstallId($cached);
        $version = Bootstrap::MODULE_VERSION;
        $payload = self::callApi($installId, $version);
        if ($payload === null) {
            // Network / parse failure — return whatever cached value we have
            // (could be a stale-but-valid prior response) without rewriting it.
            // Brand-new installs with no cache yet get null.
            return $cached !== null ? self::resultFromRow($cached) : null;
        }

        $result = ModuleVersionCheckResult::fromApi($payload);
        if ($result === null) {
            return $cached !== null ? self::resultFromRow($cached) : null;
        }

        self::persist($installId, $result);
        return $result;
    }

    /**
     * Read the last known result without making a network call. Used by
     * the dashboard banner so rendering the home page doesn't trigger
     * an outbound HTTP call. Returns null before the first check has run.
     */
    public static function getLastResult(): ?ModuleVersionCheckResult
    {
        $row = self::loadCachedRow();
        return $row !== null ? self::resultFromRow($row) : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function loadCachedRow(): ?array
    {
        $row = QueryUtils::querySingleRow(
            "SELECT install_id, last_checked_at, current_version, is_current, " .
            "is_supported, message, severity, download_url, disabled, disable_reason " .
            "FROM mod_claimrev_version_check WHERE id = 1"
        );
        if (!is_array($row) || $row === []) {
            return null;
        }
        /** @var array<string, mixed> $row */
        return $row;
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function cacheIsFresh(array $row): bool
    {
        $lastChecked = TypeCoerce::asString($row['last_checked_at'] ?? '');
        if ($lastChecked === '') {
            return false;
        }
        $ts = strtotime($lastChecked);
        if ($ts === false) {
            return false;
        }
        return (time() - $ts) < self::THROTTLE_SECONDS;
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function resultFromRow(array $row): ?ModuleVersionCheckResult
    {
        $currentVersion = TypeCoerce::asString($row['current_version'] ?? '');
        if ($currentVersion === '') {
            // Row exists but no successful response has populated it yet.
            return null;
        }
        return new ModuleVersionCheckResult(
            currentVersion: $currentVersion,
            isCurrent: TypeCoerce::asBool($row['is_current'] ?? false),
            isSupported: TypeCoerce::asBool($row['is_supported'] ?? true),
            message: TypeCoerce::asString($row['message'] ?? ''),
            severity: TypeCoerce::asString($row['severity'] ?? 'info'),
            downloadUrl: TypeCoerce::asString($row['download_url'] ?? ''),
            disabled: TypeCoerce::asBool($row['disabled'] ?? false),
            disableReason: TypeCoerce::asString($row['disable_reason'] ?? ''),
        );
    }

    /**
     * @param array<string, mixed>|null $cachedRow
     */
    private static function getOrGenerateInstallId(?array $cachedRow): string
    {
        if ($cachedRow !== null) {
            $existing = TypeCoerce::asString($cachedRow['install_id'] ?? '');
            if ($existing !== '') {
                return $existing;
            }
        }
        return self::uuidV4();
    }

    private static function uuidV4(): string
    {
        $b = random_bytes(16);
        $b[6] = chr((ord($b[6]) & 0x0f) | 0x40);
        $b[8] = chr((ord($b[8]) & 0x3f) | 0x80);
        $hex = bin2hex($b);
        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12),
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function callApi(string $installId, string $version): ?array
    {
        try {
            $client = new Client(['timeout' => self::TIMEOUT_SECONDS]);
            $response = $client->request('POST', self::ENDPOINT, [
                'json' => [
                    'moduleName' => self::MODULE_NAME,
                    'version' => $version,
                    'installId' => $installId,
                ],
                'headers' => [
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                ],
            ]);
        } catch (GuzzleException $e) {
            // Network / timeout / non-2xx — log at debug, do nothing.
            ServiceContainer::getLogger()->debug('ClaimRev version check unreachable', ['exception' => $e]);
            return null;
        }

        if ($response->getStatusCode() !== 200) {
            return null;
        }

        $body = (string) $response->getBody();
        $decoded = json_decode($body, true);
        if (!is_array($decoded)) {
            return null;
        }
        /** @var array<string, mixed> $decoded */
        return $decoded;
    }

    private static function persist(string $installId, ModuleVersionCheckResult $r): void
    {
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO mod_claimrev_version_check " .
            "(id, install_id, last_checked_at, current_version, is_current, is_supported, " .
            " message, severity, download_url, disabled, disable_reason) " .
            "VALUES (1, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?) " .
            "ON DUPLICATE KEY UPDATE " .
            " install_id = VALUES(install_id), " .
            " last_checked_at = VALUES(last_checked_at), " .
            " current_version = VALUES(current_version), " .
            " is_current = VALUES(is_current), " .
            " is_supported = VALUES(is_supported), " .
            " message = VALUES(message), " .
            " severity = VALUES(severity), " .
            " download_url = VALUES(download_url), " .
            " disabled = VALUES(disabled), " .
            " disable_reason = VALUES(disable_reason)",
            [
                $installId,
                $r->currentVersion,
                $r->isCurrent ? 1 : 0,
                $r->isSupported ? 1 : 0,
                $r->message,
                $r->severity,
                $r->downloadUrl,
                $r->disabled ? 1 : 0,
                $r->disableReason,
            ]
        );
    }
}
