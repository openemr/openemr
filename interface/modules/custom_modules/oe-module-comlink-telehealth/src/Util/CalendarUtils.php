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

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\SystemLogger;

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

    public static function isTelehealthSessionInActiveTimeRange(array $session)
    {
        $dateTime = \DateTime::createFromFormat("Y-m-d H:i:s", $session['provider_last_update']);
        return CalendarUtils::isUserLastSeenTimeInActiveRange($dateTime);
    }

    public static function isUserLastSeenTimeInActiveRange(\DateTime $dateTime)
    {
        $currentDateTime = new \DateTime();
        (new SystemLogger())->debug("checking time ", ['user_last_update_time' => $currentDateTime->format("Y-m-d H:i:s"), 'now' => $currentDateTime->format("Y-m-d H:i:s")]);
        return $currentDateTime < $dateTime->add(new \DateInterval("PT15S"));
    }

    public static function getCalendarCategory($id)
    {
        $sql = "select pc_catid,pc_constant_id,pc_catname,pc_catcolor,pc_catdesc,pc_recurrtype,pc_enddate,pc_recurrspec"
            . " ,pc_recurrfreq,pc_duration,pc_end_date_flag,pc_end_date_type,pc_end_all_day,pc_dailylimit,pc_cattype"
            . " ,pc_active,pc_seq,aco_spec"
            . " from openemr_postcalendar_categories "
            . " WHERE pc_catid = ? ";
        $records = QueryUtils::fetchRecords($sql, [$id]);
        if (!empty($records)) {
            return $records[0];
        }
        return null;
    }
}
