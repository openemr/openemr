<?php

/**
 * A FlowBoardStore in memory, recording what is saved and which encounters are created.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Appointment\FlowBoard;

use OpenEMR\Appointment\FlowBoard\FlowBoardAppointment;
use OpenEMR\Appointment\FlowBoard\FlowBoardStore;

final class FakeFlowBoardStore implements FlowBoardStore
{
    /** @var list<array{eventId: int, user: string, status: string, room: string, encounter: int}> */
    public array $saved = [];

    /** @var list<int> event IDs an arrival encounter was created for */
    public array $createdEncounters = [];

    /**
     * @param array<string, list<array{id: string, title: string}>> $lists
     */
    public function __construct(
        private readonly ?FlowBoardAppointment $appointment,
        private readonly array $lists = [
            'apptstat' => [['id' => '-', 'title' => 'None'], ['id' => '@', 'title' => 'Arrived'], ['id' => '>', 'title' => 'Checked out']],
            'patient_flow_board_rooms' => [['id' => '1', 'title' => 'Room 1'], ['id' => '2', 'title' => 'Room 2']],
        ],
        private readonly int $trackerEncounter = 0,
        private readonly int $newEncounter = 900,
        private readonly bool $refuseSave = false,
    ) {
    }

    public function appointment(int $eventId): ?FlowBoardAppointment
    {
        return $this->appointment?->eventId === $eventId ? $this->appointment : null;
    }

    public function activeOptions(string $listId): array
    {
        return $this->lists[$listId] ?? [];
    }

    public function isCheckIn(string $status): bool
    {
        return $status === '@';
    }

    public function trackerEncounter(FlowBoardAppointment $appointment): int
    {
        return $this->trackerEncounter;
    }

    public function createArrivalEncounter(FlowBoardAppointment $appointment): int
    {
        $this->createdEncounters[] = $appointment->eventId;
        return $this->newEncounter;
    }

    public function save(FlowBoardAppointment $appointment, string $user, string $status, string $room, int $encounter): bool
    {
        if ($this->refuseSave) {
            return false;
        }
        $this->saved[] = [
            'eventId' => $appointment->eventId,
            'user' => $user,
            'status' => $status,
            'room' => $room,
            'encounter' => $encounter,
        ];
        return true;
    }

    /**
     * Patient 15's appointment 768 on the given date, in status '*' (inactive in the default
     * lists here) and room 1.
     */
    public static function appointmentOn(string $date, bool $repeating = false): FlowBoardAppointment
    {
        return new FlowBoardAppointment(768, 15, 7, $date, '10:00:00', $repeating, '*', '1', 5, 'Follow-up', 3, 3);
    }
}
