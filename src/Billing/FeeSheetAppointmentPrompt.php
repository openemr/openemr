<?php

/**
 * The appointment the fee sheet asks about after Save, and the status it offers.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing;

final readonly class FeeSheetAppointmentPrompt
{
    /**
     * @param string $date YYYY-MM-DD
     * @param string $startTime HH:MM
     * @param string $statusCode the apptstat option_id to set
     * @param string $statusTitle its title, untranslated
     */
    public function __construct(
        public int $eid,
        public string $date,
        public string $startTime,
        public string $providerName,
        public string $statusCode,
        public string $statusTitle,
    ) {
    }

    /**
     * The question the fee sheet asks, translated.
     */
    public function question(): string
    {
        $date = oeFormatShortDate($this->date);

        return sprintf(
            xl('Set the %1$s appointment on %2$s with %3$s to %4$s?'),
            $this->startTime,
            is_string($date) ? $date : $this->date,
            $this->providerName,
            xl_list_label($this->statusTitle)
        );
    }

    /**
     * The calendar's day view on the appointment's date, from the webroot.
     */
    public function calendarPath(): string
    {
        return '/interface/main/calendar/index.php?module=PostCalendar&func=view&viewtype=day&Date='
            . urlencode(str_replace('-', '', $this->date));
    }
}
