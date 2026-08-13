<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\PostCalendar;

final class CalendarFacilityResolver
{
    /**
     * @param array<string, mixed> $post
     * @param array<string, mixed> $get
     * @param list<int|string>     $allowedFacilityIds
     */
    public static function resolve(
        mixed $currentFacility,
        array $post,
        array $get,
        bool $loginIntoFacility,
        mixed $loginFacility,
        bool $facilityCookieEnabled,
        mixed $cookieFacility,
        bool $restrictUserFacility,
        array $allowedFacilityIds
    ): int|string|null {
        if ($loginIntoFacility) {
            $facility = self::scalarFacility($loginFacility);
        } else {
            $facility = self::scalarFacility($currentFacility);
            if ($facility === null && $facilityCookieEnabled) {
                $facility = self::scalarFacility($cookieFacility);
            }
            $facility ??= 0;

            if (isset($post['pc_facility'])) {
                $facility = self::scalarFacility($post['pc_facility']) ?? $facility;
            }
            if (isset($get['pc_facility'])) {
                $facility = self::scalarFacility($get['pc_facility']) ?? $facility;
            }
        }

        if (!$restrictUserFacility) {
            return $facility;
        }

        foreach ($allowedFacilityIds as $allowedFacilityId) {
            if ($allowedFacilityId == $facility) {
                return $facility;
            }
        }

        return $allowedFacilityIds[0] ?? null;
    }

    private static function scalarFacility(mixed $facility): int|string|null
    {
        if (!is_int($facility) && !is_string($facility)) {
            return null;
        }

        return $facility === '' ? 0 : $facility;
    }
}
