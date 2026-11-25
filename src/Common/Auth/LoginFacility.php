<?php

/**
 * The facility a user picks on the login page, when "Login into facility" is on.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth;

use OpenEMR\Services\FacilityService;

final readonly class LoginFacility
{
    /**
     * The login page's choice that stands for the facility on the user's own record.
     */
    public const USER_DEFAULT = 'user_default';

    public function __construct(private FacilityService $facilityService)
    {
    }

    /**
     * The facility ID to keep in the session for the chosen facility; '' when the user's
     * own record has no facility.
     */
    public function chosen(string $posted, int $userId): string
    {
        if ($posted !== self::USER_DEFAULT) {
            return $posted;
        }
        $facility = $this->facilityService->getFacilityForUser($userId);
        if (!is_array($facility)) {
            return '';
        }
        $id = $facility['id'] ?? null;
        return match (true) {
            is_int($id) => (string) $id,
            is_string($id) => $id,
            default => '',
        };
    }
}
