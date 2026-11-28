<?php

/**
 * FlowBoardStore
 *
 * What a Flow Board status or room change reads and writes: the appointment, the status and
 * room lists, the patient tracker and the arrival encounter.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Appointment\FlowBoard;

interface FlowBoardStore
{
    public function appointment(int $eventId): ?FlowBoardAppointment;

    /**
     * The active options of a list (apptstat, patient_flow_board_rooms) in list order, with
     * their untranslated titles.
     *
     * @return list<array{id: string, title: string}>
     */
    public function activeOptions(string $listId): array;

    public function isCheckIn(string $status): bool;

    /**
     * The encounter on the appointment's tracker, or 0 when it has no tracker or no encounter.
     */
    public function trackerEncounter(FlowBoardAppointment $appointment): int;

    /**
     * Creates the arrival encounter, as the status popup does; 0 when none was created.
     */
    public function createArrivalEncounter(FlowBoardAppointment $appointment): int;

    /**
     * Records the status and room on the tracker (creating it when needed) and on the
     * appointment, with the encounter when it is not 0. False when the save was refused
     * because the appointment repeats.
     */
    public function save(FlowBoardAppointment $appointment, string $user, string $status, string $room, int $encounter): bool;
}
