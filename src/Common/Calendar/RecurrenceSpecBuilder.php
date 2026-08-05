<?php

/**
 * Build a RecurrenceSpec from the "Repeats every N freq" form fields.
 *
 * Postcalendar freq types split into two persistence modes:
 *
 *   Type 0..4 (daily, weekly, monthly, yearly, work-week) -> pc_recurrtype = 1
 *             "REPEAT" -- stored as an interval + freq type, iterated by
 *             adding N days/weeks/months/years.
 *
 *   Type 5..9 (nth weekday of month, last weekday, etc.) -> pc_recurrtype = 2
 *             "REPEAT_ON" -- stored as (weekday, nth occurrence) so the
 *             iterator can pick e.g. the 3rd Thursday of each month.
 *
 * Prior to openemr/openemr#11407 the threshold here was `> 6`, which stranded
 * freq type 5 in the REPEAT branch; __increment() had no handler for that
 * freq type and looped forever. The threshold is now `> 4`, matching the
 * postcalendar iterator's expectations for every type 5..9.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Calendar;

final class RecurrenceSpecBuilder
{
    /**
     * Translate the submitted repeat fields into a RecurrenceSpec.
     *
     * @param string $eventDate  Start date of the event (anything strtotime() accepts).
     * @param int    $repeatType Raw freq_type from the form (0..9).
     * @param int    $repeatFreq Raw freq interval from the form; must be >= 1.
     */
    public static function fromRepeatForm(
        string $eventDate,
        int $repeatType,
        int $repeatFreq,
    ): RecurrenceSpec {
        // Bounds-check the inputs. A $repeatFreq of 0 or negative makes
        // __increment() fail to advance the date, wedging the calendar
        // expansion loops in an infinite iteration (CWE-835). A $repeatType
        // outside 0..9 has no handler anywhere downstream.
        if ($repeatType < 0 || $repeatType > 9) {
            throw new \InvalidArgumentException(sprintf(
                'Repeat type must be in 0..9, got %d.',
                $repeatType,
            ));
        }
        if ($repeatFreq < 1) {
            throw new \InvalidArgumentException(sprintf(
                'Repeat frequency must be >= 1, got %d.',
                $repeatFreq,
            ));
        }

        // Types 0..4 are REPEAT (every N days / weeks / months / years).
        if ($repeatType <= 4) {
            return new RecurrenceSpec(
                recurrType: 1,
                repeatType: $repeatType,
                repeatFreq: $repeatFreq,
                repeatOnDay: 0,
                repeatOnNum: 1,
                repeatOnFreq: 0,
            );
        }

        // Types 5..9 are REPEAT_ON (nth weekday of month, last weekday, etc.).
        // The REPEAT_ON weekday/nth computation requires a real date; bail out
        // explicitly rather than persist a REPEAT row with an unsupported
        // freq type, which would make __increment() loop forever downstream.
        // Do not embed $eventDate in the exception message: it originates
        // from user-controlled POST input and would pass control characters
        // straight into the log stream (CWE-117) or into any error page that
        // renders the message unescaped (CWE-79).
        $timestamp = strtotime($eventDate);
        if ($timestamp === false) {
            throw new \InvalidArgumentException(
                'Cannot build REPEAT_ON recurrence spec: event date is unparsable.',
            );
        }

        $repeatOnDay = (int) date('w', $timestamp);

        // Types 5, 7, 8, 9 land on a specific nth occurrence within the month.
        // Type 6 is the "last occurrence" variant, encoded as nth = 5 by
        // downstream consumers.
        $repeatOnNum = match ($repeatType) {
            5, 7, 8, 9 => intdiv((int) date('j', $timestamp) - 1, 7) + 1,
            default => 5,
        };

        return new RecurrenceSpec(
            recurrType: 2,
            repeatType: 0,
            repeatFreq: 0,
            repeatOnDay: $repeatOnDay,
            repeatOnNum: $repeatOnNum,
            repeatOnFreq: $repeatFreq,
        );
    }
}
