<?php

/**
 * AppointmentStatusService: moves a patient's appointment on an encounter date to a new status
 * through the patient tracker (moved here from library/appointment_status.inc.php).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @copyright Copyright (c) 2011, 2016 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2026 Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;

class AppointmentStatusService extends BaseService
{
    public const TABLE_NAME = 'openemr_postcalendar_events';

    /**
     * Binds the service to the calendar events table; the update itself is static.
     */
    public function __construct()
    {
        parent::__construct(self::TABLE_NAME);
    }

    /**
     * Moves the patient's latest non-recurring appointment on $encdate to $newstatus, through the
     * patient tracker, when the gbl_auto_update_appt_status switch is on. A '$' appointment is
     * never changed, and a '>' one is not moved back to '<'.
     *
     * todaysEncounterCheck() and manage_tracker_status() are still the global functions of
     * library/encounter_events.inc.php and library/patient_tracker.inc.php, which
     * library/appointment_status.inc.php loads. The PatientTrackerService method behind
     * manage_tracker_status() documents $room and $enc_id as strings, and the encounter number
     * here is an int, so the untyped function keeps the call as it was.
     *
     * Moved from library/appointment_status.inc.php (updateAppointmentStatus).
     */
    public static function updateAppointmentStatus(int|string $pid, string $encdate, string $newstatus): void
    {
        // The values empty() treats as empty, as the legacy check did. The switch has no entry in
        // library/globals.inc.php, so a site that uses it writes the globals row by hand.
        $autoUpdate = OEGlobalsBag::getInstance()->get('gbl_auto_update_appt_status');
        if (in_array($autoUpdate, [null, false, 0, 0.0, '', '0', []], true)) {
            return;
        }

        $appointment = QueryUtils::querySingleRow(
            "SELECT pc_eid, pc_aid, pc_catid, pc_apptstatus, pc_eventDate, pc_startTime, " .
            "pc_hometext, pc_facility, pc_billing_location, pc_room " .
            "FROM openemr_postcalendar_events WHERE " .
            "pc_pid = ? AND pc_recurrtype = 0 AND pc_eventDate = ? " .
            "ORDER BY pc_startTime DESC, pc_eid DESC LIMIT 1",
            [$pid, $encdate]
        );
        // pc_eid is the auto-increment key, so every row found has a non-empty one.
        if ($appointment === false) {
            return;
        }

        // Some tests for illogical changes.
        $currentStatus = $appointment['pc_apptstatus'];
        if ($currentStatus === '$') {
            return;
        }
        if ($newstatus === '<' && $currentStatus === '>') {
            return;
        }

        $session = SessionWrapperFactory::getInstance()->getActiveSession();

        $encounter = todaysEncounterCheck(
            $pid,
            $appointment['pc_eventDate'],
            $appointment['pc_hometext'],
            $appointment['pc_facility'],
            $appointment['pc_billing_location'],
            $appointment['pc_aid'],
            $appointment['pc_catid'],
            false
        );
        manage_tracker_status(
            $appointment['pc_eventDate'],
            $appointment['pc_startTime'],
            $appointment['pc_eid'],
            $pid,
            $session->get('authUser'),
            $newstatus,
            $appointment['pc_room'],
            $encounter
        );
    }
}
