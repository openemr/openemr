<?php

/**
 * Thin delegator kept for the existing call sites of library/appointment_status.inc.php.
 * The body lives in AppointmentStatusService; see the migration tracker, openemr/openemr#11674.
 *
 * patient_tracker.inc.php stays required: the service calls its manage_tracker_status() and the
 * todaysEncounterCheck() of library/encounter_events.inc.php, which it loads.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @copyright Copyright (c) 2011, 2016 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2026 Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Services\AppointmentStatusService;

require_once(__DIR__ . '/patient_tracker.inc.php');

/**
 * Moves the patient's appointment on the encounter date to the new status when the
 * auto-update switch is on; see AppointmentStatusService::updateAppointmentStatus().
 * Arguments of any other type than the service takes change nothing.
 */
function updateAppointmentStatus($pid, $encdate, $newstatus): void
{
    if (!is_int($pid) && !is_string($pid)) {
        return;
    }
    if (!is_string($encdate)) {
        return;
    }
    if (!is_string($newstatus)) {
        return;
    }
    AppointmentStatusService::updateAppointmentStatus($pid, $encdate, $newstatus);
}
