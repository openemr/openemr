<?php

/**
 * For tracking session in database
 *  At this time only used for lastupdate tracking. Using for this case since this is used on essentially
 *   every script and avoiding use of functions in SessionUtil that prevent session locking since may
 *   cause session concurrency issues.
 *  Note these are maintained automatically and cleared out after 7 days of inactivity.
 *  Note that all time collection/derivation is from the mysql/mariadb server (in order to ensure things do not
 *   break in case the time set on the php server and mysql/mariadb server are different).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Session;

use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Core\OEGlobalsBag;
use Symfony\Component\HttpFoundation\Request;

class SessionTracker
{
    public static function setupSessionDatabaseTracker(): void
    {
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        // create the session uuid which will use as primary key in database session_tracker table
        $session->set('session_database_uuid', (new UuidRegistry(['disable_tracker' => true, 'table_name' => 'session_tracker']))->createUuid());

        // maintenance, remove entries that have not been updated for more than 7 days
        $expiredDateTime = date("Y-m-d H:i:s", strtotime('-7 day'));
        sqlStatementNoLog("DELETE FROM `session_tracker` WHERE `last_updated` < ?", [$expiredDateTime]);

        // insert new entry into database
        sqlStatementNoLog("INSERT INTO `session_tracker` (`uuid`, `created`, `last_updated`) VALUES (? , NOW(), NOW())", [$session->get('session_database_uuid')]);
    }

    public static function isSessionExpired(): bool
    {
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        if (empty($session->get('session_database_uuid'))) {
            error_log("OpenEMR Error: session_database_uuid session variable is missing");
            return true;
        }
        $sessionTracker = sqlQueryNoLog("SELECT `last_updated`, NOW() as `current_time` FROM `session_tracker` WHERE `uuid` = ?", [$session->get('session_database_uuid')]);
        if (empty($sessionTracker) || empty($sessionTracker['last_updated']) || empty($sessionTracker['current_time'])) {
            error_log("OpenEMR Error: session entry in session_tracker table is missing or invalid");
            return true;
        }
        $last_updated = strtotime((string) $sessionTracker['last_updated']);
        $current_time = strtotime((string) $sessionTracker['current_time']);
        if ($last_updated > $current_time) {
            error_log("OpenEMR Error: isSessionExpired error (last_updated time is ahead of current time which should be impossible)");
            return true;
        }
        if (($current_time - $last_updated) > OEGlobalsBag::getInstance()->getInt('timeout')) {
            return true;
        }

        // session is not expired
        return false;
    }

    public static function updateSessionExpiration(): void
    {
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        sqlStatementNoLog("UPDATE `session_tracker` SET `last_updated` = NOW() WHERE `uuid` = ?", [$session->get('session_database_uuid')]);
    }

    /**
     * Whether this request should leave session idle tracking alone.
     *
     * Background polls may opt out with skip_timeout_reset=1. Known polling
     * scripts are also excluded by path so a missing client flag cannot keep
     * the session alive indefinitely.
     *
     * Expiration is still enforced; only the last_updated refresh is skipped.
     *
     * @param  array<string, mixed>|null  $requestParams Optional override (tests).
     *                                                    When null, reads from CurrentRequest.
     */
    public static function shouldSkipTimeoutReset(
        ?array $requestParams = null,
        ?string $scriptPath = null,
        ?string $requestMethod = null,
        ?string $requestUri = null
    ): bool {
        if (self::hasTruthySkipTimeoutResetFlag(self::resolveSkipTimeoutResetFlag($requestParams))) {
            return true;
        }

        [$resolvedScriptPath, $resolvedMethod, $resolvedUri] = self::resolveRequestIdentity(
            $scriptPath,
            $requestMethod,
            $requestUri
        );

        return self::isBackgroundPollingRequest($resolvedScriptPath, $resolvedMethod, $resolvedUri);
    }

    /**
     * True when the active script/URI is a known automatic poller.
     */
    public static function isBackgroundPollingRequest(
        string $scriptPath,
        string $requestMethod = 'GET',
        string $requestUri = ''
    ): bool {
        $normalized = self::normalizeScriptPath($scriptPath);
        $uri = self::normalizeScriptPath($requestUri);
        $method = strtoupper($requestMethod);

        // Local API background runner (main UI every ~60s via APICSRFTOKEN).
        // Comment in main.php incorrectly assumed REST never touches SessionTracker;
        // LocalApi bridges the core session and auth.inc.php would reset idle time.
        if (
            str_contains($uri, '/api/background_service/')
            || str_contains($uri, '/api/background_service/%24')
        ) {
            return true;
        }

        if ($normalized === '') {
            return false;
        }

        // GET-only presence/status polls (mutating POSTs still count as activity).
        $getOnlySuffixes = [
            '/interface/modules/custom_modules/oe-module-telehealth-jse/public/api/invite.php',
            '/interface/modules/custom_modules/oe-module-telehealth-jse/public/api/invite_status_batch.php',
            '/interface/modules/custom_modules/oe-module-telehealth-jse/public/api/patient_status.php',
        ];
        foreach ($getOnlySuffixes as $suffix) {
            if (str_ends_with($normalized, $suffix)) {
                return $method === 'GET';
            }
        }

        // Dated reminders page: AJAX refresh is POST; a plain GET page load is user activity.
        if (str_ends_with($normalized, '/interface/main/dated_reminders/dated_reminders.php')) {
            return $method !== 'GET';
        }

        // Dedicated counter endpoint is always a background poll.
        if (str_ends_with($normalized, '/library/ajax/dated_reminders_counter.php')) {
            return true;
        }

        // Only skip dispatch.php when the route is the background runner.
        if (str_ends_with($normalized, '/apis/dispatch.php')) {
            return str_contains($uri, '/api/background_service/');
        }

        return false;
    }

    /**
     * @param  array<string, mixed>|null  $requestParams
     */
    private static function resolveSkipTimeoutResetFlag(?array $requestParams): mixed
    {
        if ($requestParams !== null) {
            return $requestParams['skip_timeout_reset'] ?? null;
        }

        $httpRequest = CurrentRequest::get();
        $fromBody = $httpRequest->request->get('skip_timeout_reset');
        if ($fromBody !== null) {
            return $fromBody;
        }

        return $httpRequest->query->get('skip_timeout_reset');
    }

    private static function hasTruthySkipTimeoutResetFlag(mixed $value): bool
    {
        if ($value === null || $value === false || $value === '' || $value === 0 || $value === '0') {
            return false;
        }

        return true;
    }

    /**
     * @return array{0: string, 1: string, 2: string}
     */
    private static function resolveRequestIdentity(
        ?string $scriptPath,
        ?string $requestMethod,
        ?string $requestUri
    ): array {
        if ($scriptPath !== null && $requestMethod !== null && $requestUri !== null) {
            return [$scriptPath, $requestMethod, $requestUri];
        }

        $httpRequest = CurrentRequest::get();
        $scriptPath ??= self::scriptPathFromHttpRequest($httpRequest);
        $requestMethod ??= $httpRequest->getMethod();
        $requestUri ??= $httpRequest->getRequestUri();

        return [$scriptPath, $requestMethod, $requestUri];
    }

    private static function scriptPathFromHttpRequest(Request $httpRequest): string
    {
        foreach (['SCRIPT_NAME', 'PHP_SELF', 'SCRIPT_FILENAME'] as $key) {
            $candidate = $httpRequest->server->get($key);
            if (!is_string($candidate) || $candidate === '') {
                continue;
            }
            $normalized = self::normalizeScriptPath($candidate);
            if ($normalized !== '') {
                return $normalized;
            }
        }

        return '';
    }

    private static function normalizeScriptPath(string $path): string
    {
        // Normalize Windows separators to forward slashes.
        $path = str_replace(chr(92), '/', $path);
        $path = explode('?', $path, 2)[0];

        return rtrim($path, '/');
    }

    // Function to update the throttle down function (ie. counting scripts)
    //  Only basically used for the online demos to prevent abuse of demo farm
    public static function updateSessionThrottleDown(): void
    {
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        sqlStatementNoLog("UPDATE `session_tracker` SET `number_scripts` = `number_scripts` + 1 WHERE `uuid` = ?", [$session->get('session_database_uuid')]);
    }

    // Function to throttle down requests when using the online demos to prevent abuse of the demo farm
    public static function processSessionThrottleDown($throttleDownWaitMilliseconds): void
    {
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        // calculate $timeThrottle['time_throttle'], which will be average time (in milliseconds) per script call
        $timeThrottle = sqlQueryNoLog("SELECT `number_scripts`, `created`, NOW() as `current_timestamp` FROM `session_tracker` WHERE `uuid` = ?", [$session->get('session_database_uuid')]);
        $timeThrottle['time_throttle'] = ((new \DateTime($timeThrottle['created']))->format('Uv') + ((int)$throttleDownWaitMilliseconds * $timeThrottle['number_scripts'])) - (new \DateTime($timeThrottle['current_timestamp']))->format('Uv');

        // ensure scripts on average do not go faster than the THROTTLE_DOWN_WAIT_MIllISECONDS' environment setting
        if (($timeThrottle['time_throttle'] ?? 0) > 0) {
            $dieMilliseconds = getenv('THROTTLE_DOWN_DIE_MILLISECONDS', true) ?? 0;
            if ($dieMilliseconds > 0 && ($timeThrottle['time_throttle'] ?? 0) > $dieMilliseconds) {
                // throttle down and die since the 'THROTTLE_DOWN_DIE_MILLISECONDS' environment setting has been exceeded
                error_log("DEBUG: die for script number " . $timeThrottle['number_scripts'] . " for " . $timeThrottle['time_throttle'] . " milliseconds");
                usleep($timeThrottle['time_throttle'] * 1000);
                die(xlt("These demos are not meant for headless server testing. Please do this on your own servers."));
            }
            // throttle down since the 'THROTTLE_DOWN_WAIT_MILLISECONDS' environment setting has been exceeded
            error_log("DEBUG: throttling down for script number " . $timeThrottle['number_scripts'] . " for " . $timeThrottle['time_throttle'] . " milliseconds");
            usleep($timeThrottle['time_throttle'] * 1000);
        }
    }
}
