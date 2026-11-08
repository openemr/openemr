<?php

/**
 * Recurrence spec computed from the add/edit event form's repeat fields.
 *
 * Mirrors the variables that interface/main/calendar/add_edit_event.php builds
 * before serialising into the pc_recurrspec / pc_recurrtype columns of
 * openemr_postcalendar_events. Produced by RecurrenceSpecBuilder so the
 * branching logic can be covered by isolated tests.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Calendar;

final readonly class RecurrenceSpec
{
    /**
     * @param int $recurrType   1 = REPEAT (every N days/weeks/months/years),
     *                          2 = REPEAT_ON (nth weekday of month, etc.).
     * @param int $repeatType   Original freq type when REPEAT; zero when REPEAT_ON.
     * @param int $repeatFreq   Original freq interval when REPEAT; zero when REPEAT_ON.
     * @param int $repeatOnDay  0..6 (Sunday..Saturday) for REPEAT_ON.
     * @param int $repeatOnNum  1..5 (5 = last occurrence in month) for REPEAT_ON.
     * @param int $repeatOnFreq Copy of the input freq when REPEAT_ON.
     */
    public function __construct(
        public int $recurrType,
        public int $repeatType,
        public int $repeatFreq,
        public int $repeatOnDay,
        public int $repeatOnNum,
        public int $repeatOnFreq,
    ) {
    }
}
