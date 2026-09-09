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
    /** @param list<int|string> $allowedFacilityIds */
    public static function resolve(
        mixed $currentFacility,
        mixed $postFacility,
        mixed $getFacility,
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

            if ($postFacility !== null) {
                $facility = self::scalarFacility($postFacility) ?? $facility;
            }
            if ($getFacility !== null) {
                $facility = self::scalarFacility($getFacility) ?? $facility;
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
