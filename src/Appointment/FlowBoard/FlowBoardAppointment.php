<?php

/**
 * FlowBoardAppointment
 *
 * The parts of a patient appointment that a status or room change on the Flow Board reads.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Appointment\FlowBoard;

use DateTimeImmutable;

final readonly class FlowBoardAppointment
{
    /**
     * @param int    $eventId   pc_eid
     * @param int    $patientId pc_pid
     * @param string $date      pc_eventDate, Y-m-d
     * @param string $startTime pc_startTime, H:i:s
     * @param bool   $repeating pc_recurrtype is not 0: its dates share the one event
     * @param string $status    pc_apptstatus
     * @param string $room      pc_room, '' for none
     */
    public function __construct(
        public int $eventId,
        public int $patientId,
        public int $providerId,
        public string $date,
        public string $startTime,
        public bool $repeating,
        public string $status,
        public string $room,
        public int $categoryId,
        public string $reason,
        public int $facilityId,
        public int $billingFacilityId,
    ) {
    }

    /**
     * An openemr_postcalendar_events row, or null when it is not a patient appointment with a
     * readable date and time.
     *
     * @param array<mixed> $row
     */
    public static function fromRow(array $row): ?self
    {
        $eventId = self::int($row['pc_eid'] ?? null);
        $patientId = self::int($row['pc_pid'] ?? null);
        $date = self::string($row['pc_eventDate'] ?? null);
        $startTime = self::string($row['pc_startTime'] ?? null);
        if (
            $eventId <= 0
            || $patientId <= 0
            || DateTimeImmutable::createFromFormat('!Y-m-d', $date) === false
            || DateTimeImmutable::createFromFormat('!H:i:s', $startTime) === false
        ) {
            return null;
        }

        return new self(
            $eventId,
            $patientId,
            self::int($row['pc_aid'] ?? null),
            $date,
            $startTime,
            self::int($row['pc_recurrtype'] ?? null) !== 0,
            self::string($row['pc_apptstatus'] ?? null),
            self::string($row['pc_room'] ?? null),
            self::int($row['pc_catid'] ?? null),
            self::string($row['pc_hometext'] ?? null),
            self::int($row['pc_facility'] ?? null),
            self::int($row['pc_billing_location'] ?? null),
        );
    }

    private static function int(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }
        return is_string($value) && ctype_digit($value) ? (int) $value : 0;
    }

    private static function string(mixed $value): string
    {
        return is_string($value) ? $value : '';
    }
}
