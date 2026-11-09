<?php

/**
 * CSRF guards for the therapy groups module: opening a group and its writes.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Session;

use OpenEMR\Common\Csrf\CsrfUtils;
use Symfony\Component\HttpFoundation\Request;

final class TherapyGroupSessionUtil
{
    /**
     * Read the active therapy group ID from the session; null when no group
     * is open or the stored value is not a whole number.
     */
    public static function getGroupId(): ?int
    {
        $raw = SessionWrapperFactory::getInstance()->getActiveSession()->get('therapy_group');
        if (is_int($raw)) {
            return $raw;
        }
        return is_string($raw) && ctype_digit($raw) ? (int) $raw : null;
    }

    /**
     * The `csrf_token_form` sent in the query or the posted form; null when
     * neither carries a string.
     */
    public static function csrfTokenFrom(Request $request): ?string
    {
        // Read the raw values: InputBag::get() throws on an array.
        $token = $request->query->all()['csrf_token_form']
            ?? $request->request->all()['csrf_token_form']
            ?? null;
        return is_string($token) ? $token : null;
    }

    /**
     * End the request with a 403 unless it carries the session's CSRF token.
     */
    public static function requireCsrfToken(Request $request): void
    {
        if (!self::hasValidToken(self::csrfTokenFrom($request))) {
            CsrfUtils::csrfNotVerified();
        }
    }

    /**
     * Whether a request for $requestedGroupId may make it the active group.
     * Opening a group closes the open patient, so reopening the current
     * group needs no token only while no patient is open.
     */
    public static function mayLinkSwitchGroup(int $requestedGroupId, ?string $csrfToken): bool
    {
        $sameGroup = $requestedGroupId === self::getGroupId() && PatientSessionUtil::getPid() === 0;
        return $sameGroup || self::hasValidToken($csrfToken);
    }

    /**
     * End the request with a 403 when it would open another therapy group
     * without a valid `csrf_token_form` in the query or the posted form.
     */
    public static function requireCsrfForGroupSwitch(Request $request, int $requestedGroupId): void
    {
        if (!self::mayLinkSwitchGroup($requestedGroupId, self::csrfTokenFrom($request))) {
            CsrfUtils::csrfNotVerified();
        }
    }

    private static function hasValidToken(?string $csrfToken): bool
    {
        return $csrfToken !== null
            && CsrfUtils::verifyCsrfToken($csrfToken, SessionWrapperFactory::getInstance()->getActiveSession());
    }
}
