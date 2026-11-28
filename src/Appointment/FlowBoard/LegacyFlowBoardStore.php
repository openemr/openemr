<?php

/**
 * LegacyFlowBoardStore
 *
 * FlowBoardStore over the patient tracker service and the calendar's encounter helper, as
 * the status popup uses them. The caller loads library/encounter_events.inc.php.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Appointment\FlowBoard;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\AppointmentService;
use OpenEMR\Services\PatientTrackerService;

final readonly class LegacyFlowBoardStore implements FlowBoardStore
{
    /**
     * @param ?\Closure $encounterCheck the calendar's todaysEncounterCheck()
     *        (library/encounter_events.inc.php), which the entry point loads; needed only to
     *        create the arrival encounter
     */
    public function __construct(
        private ?\Closure $encounterCheck = null,
        private PatientTrackerService $tracker = new PatientTrackerService(),
    ) {
    }

    public function appointment(int $eventId): ?FlowBoardAppointment
    {
        $row = QueryUtils::querySingleRow(
            'SELECT `pc_eid`, `pc_pid`, `pc_aid`, `pc_eventDate`, `pc_startTime`, `pc_recurrtype`, `pc_apptstatus`,'
            . ' `pc_room`, `pc_catid`, `pc_hometext`, `pc_facility`, `pc_billing_location`'
            . ' FROM `openemr_postcalendar_events` WHERE `pc_eid` = ?',
            [$eventId]
        );
        return is_array($row) ? FlowBoardAppointment::fromRow($row) : null;
    }

    public function activeOptions(string $listId): array
    {
        $rows = QueryUtils::fetchRecords(
            'SELECT `option_id`, `title` FROM `list_options` WHERE `list_id` = ? AND `activity` = 1 ORDER BY `seq`, `title`',
            [$listId]
        );
        $options = [];
        foreach ($rows as $row) {
            if (is_string($row['option_id'] ?? null) && is_string($row['title'] ?? null)) {
                $options[] = ['id' => $row['option_id'], 'title' => $row['title']];
            }
        }
        return $options;
    }

    public function isCheckIn(string $status): bool
    {
        return AppointmentService::isCheckInStatus($status);
    }

    public function trackerEncounter(FlowBoardAppointment $appointment): int
    {
        // As PatientTrackerService::is_tracker_encounter_exist(), which the status popup uses.
        $encounter = QueryUtils::fetchSingleValue(
            'SELECT `encounter` FROM `patient_tracker` WHERE `apptdate` = ? AND `encounter` > 0 AND `eid` = ? AND `pid` = ?',
            'encounter',
            [$appointment->date, $appointment->eventId, $appointment->patientId]
        );
        return is_numeric($encounter) ? (int) $encounter : 0;
    }

    public function createArrivalEncounter(FlowBoardAppointment $appointment): int
    {
        if ($this->encounterCheck === null) {
            throw new \LogicException('Creating the arrival encounter needs the calendar\'s encounter check');
        }
        $encounter = ($this->encounterCheck)(
            $appointment->patientId,
            $appointment->date,
            $appointment->reason,
            $appointment->facilityId,
            $appointment->billingFacilityId,
            $appointment->providerId,
            $appointment->categoryId,
            false
        );
        return is_numeric($encounter) ? (int) $encounter : 0;
    }

    public function atomically(callable $action): mixed
    {
        // The outer form: todaysEncounterCheck() and the tracker service may begin their own
        // transactions, which then join this one instead of committing it early.
        return QueryUtils::inOuterTransaction($action);
    }

    public function save(FlowBoardAppointment $appointment, string $user, string $status, string $room, int $encounter): bool
    {
        // It refuses, returning false, when the appointment repeats.
        return $this->tracker->manage_tracker_status(
            $appointment->date,
            $appointment->startTime,
            $appointment->eventId,
            $appointment->patientId,
            $user,
            $status,
            $room,
            $encounter === 0 ? '' : (string) $encounter
        ) !== false;
    }
}
