<?php

/**
 * Guards the therapy group context switch made by GET links.
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
     * is open or the stored value is not numeric.
     */
    public static function getGroupId(): ?int
    {
        $raw = SessionWrapperFactory::getInstance()->getActiveSession()->get('therapy_group');
        return is_numeric($raw) ? (int) $raw : null;
    }

    /**
     * Whether a request for $requestedGroupId may make it the active group.
     * Opening a group closes the open patient, so reopening the current
     * group needs no token only while no patient is open.
     */
    public static function mayLinkSwitchGroup(int $requestedGroupId, mixed $csrfToken): bool
    {
        $sameGroup = $requestedGroupId === self::getGroupId() && PatientSessionUtil::getPid() === 0;
        if ($sameGroup) {
            return true;
        }
        return is_string($csrfToken)
            && CsrfUtils::verifyCsrfToken($csrfToken, SessionWrapperFactory::getInstance()->getActiveSession());
    }

    /**
     * End the request with a 403 when it would open another therapy group
     * without a valid `csrf_token_form` in the query or the posted form.
     */
    public static function requireCsrfForGroupSwitch(Request $request, int $requestedGroupId): void
    {
        // Read the raw values: InputBag::get() throws on an array.
        $token = $request->query->all()['csrf_token_form']
            ?? $request->request->all()['csrf_token_form']
            ?? null;
        if (!self::mayLinkSwitchGroup($requestedGroupId, $token)) {
            CsrfUtils::csrfNotVerified();
        }
    }
}
