<?php

/**
 * Contains Helper methods for working with the calendar
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Comlink
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Comlink\OpenEMR\Modules\TeleHealthModule\Util;

class CalendarUtils
{
    /**
     * Checks if the given date is within the two hour safe range for a TeleHealth appointment
     * @param \DateTime $dateTime
     * @return bool
     * @throws \Exception
     */
    public static function isAppointmentDateTimeInSafeRange(\DateTime $dateTime)
    {
        $beforeTime = (new \DateTime())->sub(new \DateInterval("PT2H"));
        $afterTime = (new \DateTime())->add(new \DateInterval("PT2H"));
        return $dateTime >= $beforeTime && $dateTime <= $afterTime;
    }
}
