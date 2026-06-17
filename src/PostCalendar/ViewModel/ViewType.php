<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\PostCalendar\ViewModel;

/**
 * Which calendar view a render is targeting.
 *
 * The six values match the six Smarty templates being migrated:
 * three on-screen ajax templates and three print variants. The
 * CalendarViewModel branches its decoration logic on this enum so a
 * single service can drive every template.
 */
enum ViewType: string
{
    case Day = 'day';
    case Week = 'week';
    case Month = 'month';
    case DayPrint = 'day_print';
    case WeekPrint = 'week_print';
    case MonthPrint = 'month_print';

    /**
     * Print variants share a smaller data contract: no session data,
     * no provider picker, no facility filtering. The on-screen views
     * carry the full set.
     */
    public function isPrintView(): bool
    {
        return match ($this) {
            self::DayPrint, self::WeekPrint, self::MonthPrint => true,
            self::Day, self::Week, self::Month => false,
        };
    }

    /**
     * Day and week views constrain rendered events to clinic hours
     * [openhour, closehour]. Month + all print variants do not.
     */
    public function usesClinicHoursFilter(): bool
    {
        return match ($this) {
            self::Day, self::Week => true,
            self::Month, self::DayPrint, self::WeekPrint, self::MonthPrint => false,
        };
    }
}
