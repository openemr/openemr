<?php

/**
 * PatientPortalLoginResult — the terminal directive returned by PatientPortalLoginController.
 *
 * Every login attempt ends in a redirect, whether successful, password-change-required, or
 * an error. The result tells the caller what URL to redirect to, whether to destroy the
 * portal session cookie first, and (if a final-state audit entry should be emitted) what
 * arguments to pass to ApplicationTable::portalLog().
 *
 * Side effects during the login attempt (session mutations, DB writes for password rehash
 * or password update, the mid-flow "password update" audit entry) happen inside the
 * controller — the result only describes what to do *after* the attempt completes.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Controllers\Portal;

final readonly class PatientPortalLoginResult
{
    /**
     * @param array<int, mixed>|null $portalLogArgs Positional args for ApplicationTable::portalLog,
     *   or null when no final audit entry should be emitted (e.g. the password-change-required path
     *   which redirects to the password-update form without logging the attempt as failure).
     * @param bool $sendNoCacheHeaders Emit the legacy Expires/Cache-Control/Pragma no-cache headers
     *   before redirecting. Only set on the default success path (home.php redirect); preserved from
     *   the original script for behavior equivalence.
     * @param bool $establishCsrf Caller should call CsrfUtils::setupCsrfKey($session) after applying
     *   the result. Set on every successful-login path; cleared on every error/redirect path.
     */
    public function __construct(
        public string $redirectUrl,
        public bool $destroySessionCookie,
        public ?array $portalLogArgs = null,
        public bool $sendNoCacheHeaders = false,
        public bool $establishCsrf = false,
    ) {
    }
}
