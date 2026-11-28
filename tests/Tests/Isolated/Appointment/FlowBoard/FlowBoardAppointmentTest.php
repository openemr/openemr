<?php

/**
 * Isolated FlowBoardAppointment Test
 *
 * Reading an openemr_postcalendar_events row for a Flow Board change.
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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FlowBoardAppointmentTest extends TestCase
{
    /**
     * @return array<string, mixed>
     */
    private static function row(): array
    {
        return [
            'pc_eid' => '768', 'pc_pid' => '15', 'pc_aid' => '7', 'pc_eventDate' => '2026-11-30',
            'pc_startTime' => '08:00:00', 'pc_recurrtype' => '0', 'pc_apptstatus' => '-', 'pc_room' => '',
            'pc_catid' => '5', 'pc_hometext' => 'Follow-up', 'pc_facility' => '3', 'pc_billing_location' => '3',
        ];
    }

    public function testARow(): void
    {
        $this->assertEquals(
            new FlowBoardAppointment(768, 15, 7, '2026-11-30', '08:00:00', false, '-', '', 5, 'Follow-up', 3, 3),
            FlowBoardAppointment::fromRow(self::row())
        );
    }

    public function testARepeatingRowAndMissingOptionalValues(): void
    {
        $row = ['pc_recurrtype' => '1', 'pc_aid' => null, 'pc_hometext' => null, 'pc_room' => null] + self::row();

        $this->assertEquals(
            new FlowBoardAppointment(768, 15, 0, '2026-11-30', '08:00:00', true, '-', '', 5, '', 3, 3),
            FlowBoardAppointment::fromRow($row)
        );
    }

    /**
     * @return array<string, array{array<string, mixed>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function notAnAppointmentProvider(): array
    {
        return [
            'no patient' => [['pc_pid' => '0']],
            'no event' => [['pc_eid' => null]],
            'bad date' => [['pc_eventDate' => '2026-13-45x']],
            'bad time' => [['pc_startTime' => '']],
            'no such date' => [['pc_eventDate' => '2026-02-31']],
            'no such time' => [['pc_startTime' => '25:00:00']],
        ];
    }

    /**
     * @param array<string, mixed> $changes
     */
    #[DataProvider('notAnAppointmentProvider')]
    public function testRowsThatAreNotAPatientAppointment(array $changes): void
    {
        $this->assertNull(FlowBoardAppointment::fromRow($changes + self::row()));
    }
}
