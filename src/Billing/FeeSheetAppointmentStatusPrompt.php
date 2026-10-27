<?php

/**
 * Decides whether saving the fee sheet asks to set the visit's appointment status.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing;

use OpenEMR\Common\Database\QueryUtils;

/**
 * With the global fee_sheet_save_appt_status set to an appointment status code (for
 * example "$", Coding done), Save on the fee sheet asks whether to set the patient's
 * appointment on the visit date to that status. The appointment is the one the fee
 * sheet's own status update uses: the patient's latest non-recurring appointment that
 * day. There is no question when the setting is blank or not a status on the site's
 * list, when there is no such appointment, or when it already has the status or was
 * cancelled or missed.
 */
final readonly class FeeSheetAppointmentStatusPrompt
{
    public const GLOBAL_NAME = 'fee_sheet_save_appt_status';

    /** Cancelled, cancelled < 24h, no show: coding them done would be wrong. */
    private const SKIPPED_STATUSES = ['x', '%', '?'];

    public function __construct(private string $statusCode)
    {
    }

    public function appointmentFor(int $pid, string $visitDate): ?FeeSheetAppointmentPrompt
    {
        $code = trim($this->statusCode);
        if ($code === '') {
            return null;
        }
        $statusTitle = QueryUtils::fetchSingleValue(
            "SELECT title FROM list_options WHERE list_id = 'apptstat' AND option_id = ? AND activity = 1",
            'title',
            [$code]
        );
        if (!is_string($statusTitle)) {
            return null;
        }

        $appointment = QueryUtils::querySingleRow(
            'SELECT e.pc_eid, e.pc_eventDate, e.pc_startTime, e.pc_apptstatus, u.fname, u.lname'
            . ' FROM openemr_postcalendar_events AS e LEFT JOIN users AS u ON u.id = e.pc_aid'
            . ' WHERE e.pc_pid = ? AND e.pc_recurrtype = 0 AND e.pc_eventDate = ?'
            . ' ORDER BY e.pc_startTime DESC, e.pc_eid DESC LIMIT 1',
            [$pid, $visitDate]
        );
        if (!is_array($appointment) || !is_numeric($appointment['pc_eid'] ?? null)) {
            return null;
        }
        $current = is_string($appointment['pc_apptstatus'] ?? null) ? $appointment['pc_apptstatus'] : '';
        if ($current === $code || in_array($current, self::SKIPPED_STATUSES, true)) {
            return null;
        }

        return new FeeSheetAppointmentPrompt(
            (int) $appointment['pc_eid'],
            self::text($appointment['pc_eventDate'] ?? null),
            substr(self::text($appointment['pc_startTime'] ?? null), 0, 5),
            trim(self::text($appointment['fname'] ?? null) . ' ' . self::text($appointment['lname'] ?? null)),
            $code,
            $statusTitle,
        );
    }

    private static function text(mixed $value): string
    {
        return is_string($value) ? $value : '';
    }
}
