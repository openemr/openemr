<?php

/**
 * CSRF check for the patient portal's Phreeze API (portal/patient/index.php).
 *
 * Every request that can change data must carry the session's CSRF token in the
 * X-CSRF-Token header. The portal pages print the token, and scripts/portal-api.js sends it
 * on every write.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Csrf;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class PortalApiCsrfGuard
{
    public const HEADER = 'X-CSRF-Token';

    private const SAFE_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    /**
     * Whether a request with this HTTP method must carry a CSRF token.
     */
    public static function requiresToken(string $requestMethod): bool
    {
        return !in_array(strtoupper($requestMethod), self::SAFE_METHODS, true);
    }

    /**
     * @throws CsrfInvalidException if the request can change data and the token is missing or wrong
     */
    public static function assertValid(string $requestMethod, ?string $token, SessionInterface $session): void
    {
        if (!self::requiresToken($requestMethod)) {
            return;
        }

        if ($token === null || !CsrfUtils::verifyCsrfToken($token, $session)) {
            throw new CsrfInvalidException('Portal API CSRF token validation failed');
        }
    }
}
