<?php

/**
 * DB-backed tests for the phone-reminder notification_log writer.
 *
 * Calendar rows carry a NULL pc_endDate for "no end date", but
 * notification_log.pc_endDate is DATE NOT NULL, and MySQL rejects a NULL bind
 * there even with sql_mode = ''. The insert runs after the call is placed and
 * before the appointment is marked notified, so a failed insert made the next
 * run call again. The row's type must also survive as 'Phone'.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Notification;

use OpenEMR\Common\Command\PhoneNotificationCommand;
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PhoneNotificationLogTest extends TestCase
{
    /**
     * Sentinel IDs unlikely to collide with real rows; notification_log has
     * no foreign keys, so no patient or event needs to exist.
     */
    private const TEST_PID = 987654321;
    private const TEST_EID = 987654321;
    private const EVENT_DATE = '2026-11-05';

    protected function setUp(): void
    {
        $this->deleteTestRows();
    }

    protected function tearDown(): void
    {
        $this->deleteTestRows();
    }

    #[Test]
    public function logsPhoneReminderForEventWithNoEndDate(): void
    {
        $command = new class extends PhoneNotificationCommand {
            /** @param array<string, mixed> $row */
            public function writeLogEntry(array $row): void
            {
                $this->insertLogEntry($row, 'reminder', 'https://phone.invalid/');
            }
        };

        $command->writeLogEntry([
            'pid' => self::TEST_PID,
            'pc_eid' => self::TEST_EID,
            'title' => '',
            'fname' => 'Test',
            'mname' => '',
            'lname' => 'Patient',
            'phone_home' => '',
            'pc_eventDate' => self::EVENT_DATE,
            'pc_endDate' => null,
            'pc_startTime' => '09:00:00',
            'pc_endTime' => '09:15:00',
        ]);

        $rows = QueryUtils::fetchRecords(
            'SELECT `type`, pc_eventDate FROM notification_log WHERE pid = ? AND pc_eid = ?',
            [self::TEST_PID, self::TEST_EID],
        );
        $this->assertCount(1, $rows);
        $this->assertSame('Phone', $rows[0]['type'] ?? null);
        $this->assertSame(self::EVENT_DATE, $rows[0]['pc_eventDate'] ?? null);
    }

    private function deleteTestRows(): void
    {
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM notification_log WHERE pid = ? AND pc_eid = ?',
            [self::TEST_PID, self::TEST_EID],
        );
    }
}
