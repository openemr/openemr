<?php

/**
 * FlowBoardChange
 *
 * Changes an appointment's status or room from the Flow Board, the way the status popup
 * (interface/patient_tracker/patient_tracker_status.php) does: through the patient tracker,
 * which keeps the time columns and the appointment in step, creating the arrival encounter
 * when the new status is a check-in today and the tracker has no encounter yet.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Appointment\FlowBoard;

use DateTimeZone;
use Lcobucci\Clock\SystemClock;
use Psr\Clock\ClockInterface;

final readonly class FlowBoardChange
{
    public const STATUS_LIST = 'apptstat';
    public const ROOM_LIST = 'patient_flow_board_rooms';

    /**
     * @param bool $autoCreateEncounters the auto_create_new_encounters setting is on
     */
    public function __construct(
        private FlowBoardStore $store,
        private bool $autoCreateEncounters,
        private ClockInterface $clock = new SystemClock(new DateTimeZone('UTC')),
    ) {
    }

    /**
     * @param ?string $status the new status, or null to keep it
     * @param ?string $room   the new room ('' for none), or null to keep it
     */
    public function apply(int $eventId, ?string $status, ?string $room, string $user): FlowBoardChangeResult
    {
        $appointment = $this->store->appointment($eventId);
        if ($appointment === null) {
            return FlowBoardChangeResult::NotFound;
        }
        if ($appointment->repeating) {
            return FlowBoardChangeResult::Repeating;
        }

        // An unchanged value is kept even when its list entry is no longer active.
        $status ??= $appointment->status;
        if ($status !== $appointment->status && !$this->isActive(self::STATUS_LIST, $status)) {
            return FlowBoardChangeResult::UnknownStatus;
        }
        $room ??= $appointment->room;
        if ($room !== '' && $room !== $appointment->room && !$this->isActive(self::ROOM_LIST, $room)) {
            return FlowBoardChangeResult::UnknownRoom;
        }

        $encounter = $this->store->trackerEncounter($appointment);
        if (
            $encounter === 0
            && $this->autoCreateEncounters
            && $appointment->date === $this->today()
            && $this->store->isCheckIn($status)
        ) {
            $encounter = $this->store->createArrivalEncounter($appointment);
        }

        // The save is refused when the appointment was made repeating after it was read.
        return $this->store->save($appointment, $user, $status, $room, $encounter)
            ? FlowBoardChangeResult::Saved
            : FlowBoardChangeResult::Repeating;
    }

    private function isActive(string $listId, string $optionId): bool
    {
        return in_array($optionId, array_column($this->store->activeOptions($listId), 'id'), true);
    }

    private function today(): string
    {
        return $this->clock->now()->setTimezone(new DateTimeZone(date_default_timezone_get()))->format('Y-m-d');
    }
}
